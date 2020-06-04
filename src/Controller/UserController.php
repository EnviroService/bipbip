<?php

namespace App\Controller;

use App\Entity\Collects;
use App\Entity\Estimations;
use App\Entity\Reporting;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserEditType;
use App\Repository\CollectsRepository;
use App\Repository\UserRepository;
use App\Repository\OrganismsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{

    /**
     * @Route("/user/add/{estimation}", name="user_add")
     * @param Request $request
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     * @throws Exception
     * @throws \Exception
     */
    public function newUser(
        Request $request,
        Estimations $estimation,
        EntityManagerInterface $em
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setSignupDate(new DateTime('now'));
            $user->setSigninDate(new DateTime('now'));
            $user->addEstimation($estimation);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Compte créé, félicitations à toi, rendez vous à la collecte !!');

            return $this->redirectToRoute('home');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
// Les différentes collectes classées par date chronologique
    /**
     * @Route("/showCollects", name="show_collect")
     * @param CollectsRepository $collectsRepository
     * @param OrganismsRepository $organismsRepository
     * @return Response
     * @throws Exception
     */
    public function searchCollect(CollectsRepository $collectsRepository, OrganismsRepository $organismsRepository)
    {
        if (!empty($this->getUser())) {
            $organism = $this->getUser()->getOrganism();
        } else {
            $organism = null;
        }
        if ($organism !== null) {
            $privateCollects = $collectsRepository->findBy(
                ['collector' => $organism->getId()],
                ["collector" => "ASC"]
            );

            $publicOrganisms = $organismsRepository->findBy(['organismStatus' => 'Collecteur public']);
            $publicOrganismsId = [];
            foreach ($publicOrganisms as $publicOrganism) {
                $publicOrganismsId [] = $publicOrganism->getId();
            }
            $publicCollects = $collectsRepository->findBy(
                ['collector' => $publicOrganismsId],
                ["collector" => "ASC"]
            );

            $repo = [];
            foreach ($privateCollects as $privateCollect) {
                $repo[] = $privateCollect;
            }

            foreach ($publicCollects as $publicCollect) {
                $repo[] = $publicCollect;
            }
        } else {
            $repo = $collectsRepository->findBy([], ["dateCollect" => "ASC"]);
        }

        $now = new DateTime('now');

        $sizeRepo = count($repo);

        for ($i=0; $i < $sizeRepo; $i++) {
            if ($repo[$i]->getDateCollect() < $now) {
                unset($repo[$i]);
            }
        }
        return $this->render('user/showCollect.html.twig', [
            'collects' => $repo,
            'collector' => $organism
        ]);
    }
// le user choisie sa collecte et un mail de confirmation lui est envoyé
    /**
     * @Route("/choice/{collect}", name="choice")
     * @ParamConverter("collect" , class="App\Entity\Collects", options={"id"="collect"})
     * @param EntityManagerInterface $em
     * @param CollectsRepository $repository
     * @param Collects $collect
     * @param MailerInterface $mailer
     * @return RedirectResponse
     * @throws TransportExceptionInterface
     */
    public function choiceCollect(
        EntityManagerInterface $em,
        CollectsRepository $repository,
        Collects $collect,
        MailerInterface $mailer
    ) {
        $user = $this->getUser();
        $collect = $repository->findOneBy(['id' => $collect]);
        if (!empty($collect)) {
            $organism = $collect->getCollector();
            $user->setCollect($collect);
            $em->persist($user);
            $em->flush();

            // mail for user
            if (!empty($collect->getDateCollect())) {
                $day = $collect->getDateCollect()->format("d/m/y");
                $hour = $collect->getDateCollect()->format("H:i");
                $emailExp = (new Email())
                    ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                    ->to(new Address($user->getEmail(), $user
                            ->getFirstname() . ' ' . $user->getLastname()))
                    ->replyTo('github-test@bipbip-mobile.fr')
                    ->subject('Tu es inscrit à une collecte !')
                    ->html($this->renderView(
                        'contact/confirmCollect.html.twig',
                        [
                            'day' => $day,
                            'hour' => $hour,
                            'user' => $user,
                            'organism' => $organism,
                        ]
                    ));

                $mailer->send($emailExp);
            }
        }

        return $this->redirectToRoute("collect_confirm");
    }
// Confirmation inscription collecte
    /**
     * @Route("/confirm/collect/", name="collect_confirm")
     * @return Response
     */
    public function collectConfirm()
    {
        $user = $this->getUser();

        return $this->render('user/confirmCollect.html.twig', [
            'user' => $user,
        ]);
    }
// Permet a l'admin de lister ces collecteurs
    /**
     * @Route("/admin/collectors", name="collectors_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function indexCollectors(UserRepository $userRepository): Response
    {
        $collectors = $userRepository->findCollectors('ROLE_COLLECTOR');

        return $this->render('user/indexCollectors.html.twig', [
            'collectors' => $collectors
        ]);
    }

    /**
     * @Route("/admin/collectors/{id}", name="collectors_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param User $user
     * @return Response
     */
    public function showCollector(User $user): Response
    {
        return $this->render('user/showCollector.html.twig', [
            'user' => $user,
        ]);
    }
// Permet a l'admin d'editer un collecteur et de le modifier
    /**
     * @Route("/admin/collectors/{id}/edit", name="collectors_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function editCollector(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('collectors_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            ]);
    }
// Permet a l'admin de supprimer les collecteurs
    /**
     * @Route("/admin/collectors/{id}", name="collectors_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('collectors_index');
    }
// Profil du user
    /**
     * @Route("/", name="user_show")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function showUser(): Response
    {

        return $this->render('user/showUser.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
// Modifie les informations du profil
    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user_show');
        }
        return $this->render('user/editUser.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
