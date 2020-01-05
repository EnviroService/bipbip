<?php

namespace App\Controller;

use App\Repository\FAQRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{

    /**
     *
     * @param FAQRepository $faqRepo
     * @return Response
     * @Route("/faq", name="show_faq")
     */

    public function showFaq(FAQRepository $faqRepo): Response
    {
        $faqContent = $faqRepo->findAll();
        return $this->render('faq/index.html.twig', [
            'faqContent' => $faqContent]);
    }
}
