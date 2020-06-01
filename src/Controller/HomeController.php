<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\CollectsRepository;
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
     * @Route("vends/", name="vends")
     * @return Response
     */
    public function vends()
    {
        return $this->render('home/vends.html.twig');
    }

    /**
     * @Route("infos/team", name="team")
     */
    public function team()
    {
        return $this->render('infos/team.html.twig');
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
     * @Route("histoire", name="histoire")
     */
    public function histoire()
    {
        return $this->render('infos/histoire.html.twig');
    }

    /**
     * @Route("recrute", name="recrute")
     */
    public function recrute()
    {
        return $this->render('infos/recrute.html.twig');
    }

    /**
     * @Route("livraisons", name="livraisons")
     */
    public function livraisons()
    {
        return $this->render('infos/livraisons.html.twig');
    }

    /**
     * @Route("presse", name="presse")
     */
    public function presse()
    {
        return $this->render('infos/presse.html.twig');
    }

    /**
     * @Route("bonsplans", name="bonsplans")
     */
    public function bonsplans()
    {
        return $this->render('bonsplans/index.html.twig');
    }

    /**
     * @Route("bipers", name="bipers")
     * @return Response
     */
    public function collectorBipers(CollectsRepository $collectsRepo)
    {
        $form = $this->createForm(ContactType::class);

        return $this->render('home/collectorBipers.html.twig', [
            'form' => $form->createView(),
            'collects' => $collectsRepo->findByDateValid()
        ]);
    }
}
