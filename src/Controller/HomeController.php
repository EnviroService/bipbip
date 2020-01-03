<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("infos/bipbip", name="qui-sommes-nous")
     */
    public function who()
    {
        return $this->render('infos/bipbip.html.twig');
    }

    /**
     * @Route("infos/recrute", name="recrute")
     */
    public function recrute()
    {
        return $this->render('infos/recrute.html.twig');
    }

    /**
     * @Route("infos/cgu", name="cgu")
     */
    public function cgu()
    {
        return $this->render('infos/cgu.html.twig');
    }
}
