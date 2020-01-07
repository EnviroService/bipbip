<?php

namespace App\Controller;

use App\Entity\Estimations;
use App\Repository\EstimationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("/bdc")
 */
class BdcController extends AbstractController
{
    /**
     * @Route("/", name="bdc_index")
     */
    public function index()
    {
        // list all pdf in public/uploads/BDC/
        $files = scandir('uploads/BDC/');
        unset($files[array_search('.', $files)]);
        unset($files[array_search('..', $files)]);
        return $this->render('bdc/index.html.twig', [
            'files' => $files
        ]);
    }

    /**
     * @Route("/show/{id}", name="bdc_show")
     * @param Estimations $estimation
     * @return Response
     */
    // route to show an estimation
    public function show(Estimations $estimation)
    {
        return $this->render('bdc/bdc.html.twig', [
            'IMEI' => "355 402 092 374 478",
            'estimation' => $estimation,
        ]);
    }

    /**
     * @Route("/pdf/{id}", name="bdc_pdf")
     * @param Estimations $estimation
     */
    // route to generate a PDF from estimation
    public function showPDF(Estimations $estimation)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bdc/bdc.html.twig', [
            'IMEI' => "355 402 092 374 478",
            'estimation' => $estimation
        ]);

        // Create Filename
        $clientId = 1;
        $estimationId = $estimation->getId();
        $filename = "C" . $clientId . "E" . $estimationId . ".pdf";

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Store PDF Binary Data
        $output = $dompdf->output();

        // we want to write the file in the public directory
        $publicDirectory = 'uploads/BDC';
        $pdfFilepath =  $publicDirectory . '/' . $filename;

        // Write file to the desired path
        file_put_contents($pdfFilepath, $output);

        // TODO Send some text response flash message
        // return new Response("The PDF file has been succesfully generated !");

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream($filename, [
        "Attachment" => false
        ]);
    }
}
