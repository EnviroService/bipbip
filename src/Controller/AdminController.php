<?php

namespace App\Controller;

use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/administrateur", name="admin")
     */
    public function show(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
   //  * @Route("/administrateur", name="adminSearchBar")
     //* @param $usersRepository
     //* @return Response
     */
    //public function showSearchBar($usersRepository): Response
  //  {
    //    $form = $this->createForm(UserType::class);
     //   if ($form->isSubmitted() && $form->isValid()) {
       //     $this->getDoctrine()->getManager()->flush();

         //   return $this->redirectToRoute('adminSearchBar');
        //}
    //}
}
