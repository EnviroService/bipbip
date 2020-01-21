<?php

namespace App\Controller;

use App\Entity\Search;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\SearchType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 * @Route("/admin", name="admin")
 */

class AdminController extends AbstractController
{
    /**
     * @Route("/home", name="home_admin", methods={"GET"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     */
    public function index(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            $data = $form->getData()->getnameSearch();
            $result = $userRepository->findSearch($data);
            $json =[];
            foreach ($result as $name) {
                $lastname = $name->getLastname();
                $json[]= ['lastname'=>$lastname];
            }
            $json =json_encode($json);
            //envoi des données JSON en front
            return new JsonResponse($json, 200, [], true);
        }
            return $this->render('admin/index.html.twig', [
                "form" => $form->createView()
            ]);
    }
    /**
     * @Route("/collector/register", name="register_collector")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @return Response|null
     * @throws Exception
     */
    public function registerCollector(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator
    ) {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_COLLECTOR']);
            $user->setSignupDate(new DateTime('now'));
            $user->setSigninDate(new DateTime('now'));
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('adminhome_admin');
        }

        return $this->render('admin/register_collector.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
