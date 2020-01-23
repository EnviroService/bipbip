<?php

namespace App\Controller;

use App\Entity\Estimations;
use Dompdf\Dompdf;
use Dompdf\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/bdc")
 */

class BdcController extends AbstractController
{
    /**
     * @Route("/", name="bdc_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index()
    {
        // list all pdf in public/uploads/BDC/
        $files = scandir('uploads/BDC/');
        if (is_array($files)) {
            // If several files in dir
            $files = array_slice($files, 2);
            // search date and estimation from file name
            $dates = [];
            $estimationIds = [];
            foreach ($files as $file) {
                $year = substr($file, 0, 4);
                $month = substr($file, 4, 2);
                $day = substr($file, 6, 2);
                $date = "$day/$month/$year";
                array_push($dates, $date);
                $file = ' ' . $file;
                $ini = strpos($file, "P");
                if ($ini == 0) {
                    return '';
                }
                $ini += strlen("P");
                $len = strpos($file, ".", $ini) - $ini;
                $estimationId = substr($file, $ini, $len);
                array_push($estimationIds, $estimationId);
            }
        } elseif (is_bool($files)) {
            // if no file in dir, files is false
            $dates = "Aucun fichier";
            $estimationIds = "";
        } else {
            // if only one file in dir, search date and estimationId
            $year = substr($files, 0, 4);
            $month = substr($files, 4, 2);
            $day = substr($files, 6, 2);
            $dates = "$day/$month/$year";
            $ini = strpos($files, "P");
            if ($ini == 0) {
                return '';
            }
            $ini += strlen("P");
            $len = strpos($files, ".", $ini) - $ini;
            $estimationIds = substr($files, $ini, $len);
        }

        return $this->render('bdc/index.html.twig', [
            'files' => $files,
            'dates' => $dates,
            'estimationIds' => $estimationIds,
        ]);
    }

    /**
     * @Route("/pdf/{id}", name="bdc_pdf")
     * @param Estimations $estimation
     * @return RedirectResponse
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
        $clientId = $this->getUser()->getId();
        $estimationId = $estimation->getId();
        $filename = date("Ymd") . "C" . $clientId . "P" . $estimationId . ".pdf";

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

        // Prepare flash message
        $message = "Le bon de cession a été généré";
        $this->addFlash('success', $message);

        // Output the generated PDF to Browser (inline view)
        //$dompdf->stream($filename, [
        //"Attachment" => false
        //]);

        return $this->redirectToRoute('bdc_pay', [
            'id' => $estimation->getId(),
        ]);
    }
}
