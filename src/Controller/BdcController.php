<?php

namespace App\Controller;

use App\Entity\Estimations;
use App\Entity\Organisms;
use Dompdf\Dompdf;
use Dompdf\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/bdc")
 */

class BdcController extends AbstractController
{

   // Permet de faire signer le user et d'enregistrer sa signature

    /**
     * @Route("/signature/{id}", name="generate_signature")
     * @IsGranted("ROLE_COLLECTOR")
     * @param Estimations $estimation
     * @return Response
     */
    public function signature(Estimations $estimation)
    {
        if (isset($_POST['imgBase64'])) {
            define('UPLOAD_DIR', 'uploads/signatures/');
            $img = $_POST['imgBase64'];
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            if ((is_string($img))) {
                $data = base64_decode($img);
                $file = UPLOAD_DIR . 'user'. $estimation->getUser()->getId(). '.png';
                file_put_contents($file, $data);
            }

            return $this->redirectToRoute('generate_signature', [
                'id' => $estimation->getId(),
            ]);
        } else {
            return $this->render('bdc/signature.html.twig', [
                'id' => $estimation->getId(),
                'estimation' => $estimation,
            ]);
        }
    }
// Permet a l'admin de stocker l'ensemble des bons de cessions

    /**
     * @Route("/", name="bdc_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index()
    {
        // list all pdf in public/uploads/BDC/
        $dates = [];
        $estimationIds = [];
        $end = '.';
        $start = 'P';
        $estimations = [];
        $files = scandir('uploads/BDC/');
        if (is_array($files)) {
            $files = array_slice($files, 3);
            foreach ($files as $file) {
                $year = substr($file, 0, 4);
                $month = substr($file, 4, 2);
                $day = substr($file, 6, 2);
                $date = "$day-$month-$year";

                // recupérer uniquement le nom du fichier sans l'extension
                $fileNew = strtok($file, '.');
                // Extraire le numero id, juste apres la lettre P du $file
                $id = str_replace('P', '', strrchr($fileNew, "P"));
                // Envoie de la date et de l'id sous forme de tableau
                $estimations[$fileNew] = [$date,$id,$file];

                /*
                array_push($dates, $date);
                $file = ' ' . $file;
                $ini = strpos($file, $start);
                if ($ini == 0) {
                    $estimationId = '';
                }
                $ini += strlen($start);
                $len = strpos($file, $end, $ini) - $ini;
                $estimationId = substr($file, $ini, $len);
                array_push($estimationIds, $estimationId);
                */
            }
            krsort($estimations);
        }

        return $this->render('bdc/index.html.twig', [
            'estimations' => $estimations
        ]);
    }
// Permet la génération d'un pdf

    /**
     * @Route("/pdf/{id}", name="bdc_pdf")
     * @IsGranted("ROLE_COLLECTOR")
     * @param Estimations $estimation
     * @return RedirectResponse
     * @throws TransportExceptionInterface
     */
    // route to generate a PDF from estimation
    public function showPDF(Estimations $estimation, MailerInterface $mailer)
    {
        // if collector, verify that estimation belongs to collector's organism
        // unless redirect him to estimations_index
        $userRoles = $this->getUser()->getRoles();
        foreach ($userRoles as $role) {
            if ($role == "ROLE_COLLECTOR") {
                $organismUser = $this->getUser()->getOrganism();
                if ($estimation->getUser() !== null) {
                    $organismCollector = $estimation->getUser()->getOrganism();
                } else {
                    $organismCollector = new Organisms();
                }
                if ($organismUser != $organismCollector or $organismUser == null) {
                    // Prepare flash message and redirect
                    $message = "Ce bon de cession n'appartient pas à votre organisme";
                    $this->addFlash('danger', $message);
                    return $this->redirectToRoute('estimations_index');
                }
            }
        }
        // create barcode image
        $imei = $estimation->getImei();
        $text = "*" . $imei . "*";
        header("Content-Type: image/png");
        $imgPath = "uploads/barcodes/";
        $barcode = @imagecreate(240, 40);
        if ($barcode) {
            imagecolorallocate($barcode, 255, 255, 255);
            $font = 'fonts/code128.ttf';
            $black = imagecolorallocate($barcode, 0, 0, 0);
            imagettftext($barcode, 30, 0, 2, 38, $black, $font, $text);
            imagetruecolortopalette($barcode, true, 255);
            imagepng($barcode, "$imei.png");
            move_uploaded_file("$imei.png", $imgPath);
            imagedestroy($barcode);
        }

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bdc/bdc.html.twig', [
            'estimation' => $estimation,
        ]);

        // Create Filename
        $clientId = $estimation->getUser()->getId();
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

        $user = $estimation->getUser();
        $emailExp = (new Email())
            ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
            ->to(new Address($user->getEmail(), $user
                    ->getFirstname() . ' ' . $user->getLastname()))
            ->replyTo('github-test@bipbip-mobile.fr')
            ->subject('Voici ton bon de cession')
            ->attachFromPath($pdfFilepath)
            ->html($this->renderView(
                'contact/envoiBDC.html.twig',
                [
                    'user' => $user,
                    'estimation' => $estimation
                ]
            ));
        $mailer->send($emailExp);


        return $this->redirectToRoute('bdc_pay', [
            'id' => $estimation->getId(),
        ]);
    }
}
