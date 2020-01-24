<?php


namespace App\Controller;

use App\Entity\Collects;
use App\Entity\Estimations;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\CollectsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
use DateTime;

class UserController extends AbstractController
{
    /**
     * @Route("/user/add/{estimation}", name="user_add")
     * @param Request $request
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     * @throws Exception
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

    /**
     * @Route("/showCollects", name="show_collect")
     * @param CollectsRepository $collectsRepository
     * @return Response
     */
    public function searchCollect(CollectsRepository $collectsRepository)
    {
        $organism = $this->getUser()->getOrganism();
        if ($organism !== null) {
            $repo = $collectsRepository->findBy(['collector' => $organism->getId()]);
        } else {
            $repo = $collectsRepository->findAll();
        }

        return $this->render('user/showCollect.html.twig', [
            'collects' => $repo,
        ]);
    }

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
        $organism = $collect->getCollector();
        $user->setCollect($collect);
        $em->persist($user);
        $em->flush();

        // mail for user
        $day = $collect->getDateCollect()->format("d/m/y");
        $hour = $collect->getDateCollect()->format("h:i");
        $emailExp = (new Email())
            ->from(new Address('contact@bipbipmobile.com', 'BipBip Mobile'))
            ->to(new Address($user->getEmail(), $user
                    ->getFirstname() . ' ' . $user->getLastname()))
            ->replyTo('contact@bipbipmobile.com')
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

        return $this->redirectToRoute("collect_confirm");
    }

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
}
