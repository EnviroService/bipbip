<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="admin")
 */

class AdminController extends AbstractController
{
    /**
     * @Route("/home", name="homeAdmin")
     */
    public function show(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/admin", name="searchBar")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     * @return Response
     */
    public function searchBar(Request $request, EntityManagerInterface $em, userRepository $userRepository): Response
    {
            $users= $em->getRepository(User::class)->findAll();
            $result = [];
        foreach ($users as $user) {
            array_push($result, $user->getLastname());
        }

        if ($request->isXmlHttpRequest()) {
            $result = $request->request->all();
            dd($result);
            return new JsonResponse($result);
        }
            return $this-> render(
                'admin/index.html.twig',
                ["lastname"=>$result]
            );

        //1return new JsonResponse(['resultSearch'=>$resultSearch]);
        /*2$response = new Response(json_encode(['resultSearch' => $resultSearch]));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
*/
       /*3 if ($request->isXmlHttpRequest()) {
            $user = $request->query->get('user');

            return $this-> render(
                'admin/index.html.twig',
                ['resultSearch' => $resultSearch,
                'user'=>$user]
            );
        }*/
    }
}
