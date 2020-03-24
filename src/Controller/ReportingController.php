<?php

namespace App\Controller;

use App\Repository\ReportingRepository;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportingController extends AbstractController
{
    /**
     * @Route("/admin/reporting", name="reporting_index")
     * @IsGranted("ROLE_ADMIN")
     * @param ReportingRepository $reporting
     * @return Response
     * @throws Exception
     */
    public function index(ReportingRepository $reporting): Response
    {
        // search reportings 24h before
        $toBeCollected = $reporting->findByEstimatedYesterday();
        $collected = $reporting->findByCollectedYesterday();
        return $this->render('reporting/index.html.twig', [
            'toBeCollected' => $toBeCollected,
            'collected' => $collected,
        ]);
    }
}
