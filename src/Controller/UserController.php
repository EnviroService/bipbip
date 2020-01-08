<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\EstimationsRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class UserController extends AbstractController
{
    /**
     * @Route("/user/add", name="user_add")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function newUser(Request $request): Response
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setSignupDate(new DateTime('now'));
            $user->setSigninDate(new DateTime('now'));
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Compte créé, félicitations à toi, rendez vous à la collecte !!');

            return $this->redirectToRoute('home');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
