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
     * @param OrganismsRepository $organismsRepository
     * @return Response
     */
    public function index(OrganismsRepository $organismsRepository)
    {
        $organisms = $organismsRepository->findAll();


        $randomIDOrganisms = array_rand($organisms, 3);
        if (is_array($randomIDOrganisms)) {
            $randomOrganisms = [];
            $randIDOrgLength = \count($randomIDOrganisms);
            for ($i = 0; $i < $randIDOrgLength; $i++) {
                $randomOrganisms[] = $organisms[$randomIDOrganisms[$i]];
            }
        } else {
            $randomOrganisms = $organisms;
        }

        return $this->render('home/index.html.twig', [
            'organisms' => $randomOrganisms
        ]);
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
     * @Route("infos/protection", name="protection")
     */
    public function protection()
    {
        return $this->render('infos/protection.html.twig');
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
