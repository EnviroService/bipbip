<?php

namespace App\Controller;

use App\Entity\Collects;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\CollectsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/choice/{collect}", name="choice")
     * @ParamConverter("collect" , class="App\Entity\Collects", options={"id"="collect"})
     * @param EntityManagerInterface $em
     * @param CollectsRepository $repository
     * @param Collects $collect
     * @return RedirectResponse
     */
    public function choiceCollect(EntityManagerInterface $em, CollectsRepository $repository, Collects $collect)
    {
        $user = $this->getUser();
        $collect = $repository->findOneBy(['id' => $collect]);
        $user->setCollect($collect);
        $em->persist($user);
        $em->flush();
        $this->addFlash("success", "Tu as bien été enregistré sur cette collecte.");

        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/admin/collectors", name="collectors_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function indexCollectors(UserRepository $userRepository): Response
    {
        $collectors = $userRepository->findCollectors('ROLE_COLLECTOR');

        return $this->render('user/index.html.twig', [
            'collectors' => $collectors
        ]);
    }

    /**
     * @Route("/admin/collectors/{id}", name="collectors_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/admin/collectors/{id}/edit", name="collectors_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('collectors_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

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
}
