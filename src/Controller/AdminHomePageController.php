<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminHomePageController extends AbstractController
{
    /**
     * @Route("/administrateur", name="homeAdmin")
     */
    public function show(): Response
    {
        return $this->render('admin/index.html.twig');
    }
}
