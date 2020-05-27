<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BoutiqueController extends AbstractController
{
    /**
     * @Route("boutique", name="boutique")
     */
    public function boutique()
    {
        return $this->render('boutique/boutique.html.twig');
    }
}
