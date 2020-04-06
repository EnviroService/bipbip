<?php

namespace App\Controller;

use App\Repository\OrganismsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @return Response
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
     * @Route("admin/", name="adminIndex")
     */
    public function adminIndex()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("infos/cgu", name="cgu")
     */
    public function cgu()
    {
        return $this->render('infos/cgu.html.twig');
    }

    /**
     * @Route("infos/cgv", name="cgv")
     */
    public function cgv()
    {
        return $this->render('infos/cgv.html.twig');
    }

    /**
     * @Route("infos/protection", name="protection")
     */
    public function protection()
    {
        return $this->render('infos/protection.html.twig');
    }

    /**
     * @Route("infos/cookies", name="cookies")
     */
    public function cookies()
    {
        return $this->render('infos/cookies.html.twig');
    }

    /**
     * @Route("infos/mentions", name="mentions")
     */
    public function mentions()
    {
        return $this->render('infos/mentions.html.twig');
    }

    /**
     * @Route("autres", name="autres")
     */
    public function autres()
    {
        return $this->render('estimation/autres.html.twig');
    }

    /**
     * @Route("boutique", name="boutique")
     */
    public function boutique()
    {
        return $this->render('infos/boutique.html.twig');
    }
}
