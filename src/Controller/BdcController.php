<?php

namespace App\Controller;

use App\Entity\Estimations;
use App\Entity\User;
use App\Form\CollectEstimationType;
use App\Form\CollectUserType;
use App\Repository\PhonesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

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
        return $this->render('bdc/index.html.twig', [
            'files' => $files
        ]);
    }

    /**
     * @Route("/verify/{id}", name="verifyEstim", methods={"GET","POST"})
     * @param Request $request
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @param PhonesRepository $phonesRepository
     * @return Response
     */
    public function verifyEstim(
        Request $request,
        Estimations $estimation,
        EntityManagerInterface $em,
        PhonesRepository $phonesRepository
    ): Response {
        $form = $this->createForm(CollectEstimationType::class, $estimation);
        $form->handleRequest($request);
        $brand = $estimation->getBrand();
        $model = $estimation->getModel();
        $capacity = $estimation->getCapacity();

        $phone = $phonesRepository->findOneBy([
            'brand' => $brand,
            'model' => $model,
            'capacity' => $capacity
        ]);
        $maxPrice = $phone->getMaxPrice();
        $liquidDamage = $phone->getPriceLiquidDamage();
        $screenCracks = $phone->getPriceScreenCracks();
        $casingCracks = $phone->getPriceCasingCracks();
        $bateryPrice = $phone->getPriceBattery();
        $buttonPrice = $phone->getPriceButtons();

        if ($form->isSubmitted() && $form->isValid()) {
            $estimated = $maxPrice;

            if ($form['liquidDamage']->getData() === "1") {
                $estimation->setLiquidDamage($liquidDamage);
                $estimated -= $liquidDamage;
            } else {
                $estimation->setLiquidDamage(0);
            }

            if ($form['screenCracks']->getData() === "1") {
                $estimation->setScreenCracks($screenCracks);
                $estimated -= $screenCracks;
            } else {
                $estimation->setScreenCracks(0);
            }

            if ($form['casingCracks']->getData() === "1") {
                $estimation->setCasingCracks($casingCracks);
                $estimated -= $casingCracks;
            } else {
                $estimation->setCasingCracks(0);
            }

            if ($form['batteryCracks']->getData() === "1") {
                $estimation->setBatteryCracks($bateryPrice);
                $estimated -= $bateryPrice;
            } else {
                $estimation->setBatteryCracks(0);
            }

            if ($form['buttonCracks']->getData() === "1") {
                $estimation->setButtonCracks($buttonPrice);
                $estimated -= $buttonPrice;
            } else {
                $estimation->setButtonCracks(0);
            }
            if ($estimated < 1) {
                $estimated = 1;
            }
            $estimation->setEstimatedPrice($estimated);
            $em->persist($estimation);
            $em->flush();
        }

        return $this->render('estimations/editEstim.html.twig', [
            'estimation' => $estimation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}", name="verifyUser", methods={"GET","POST"})
     * @param Request $request
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function verifyUser(
        Request $request,
        Estimations $estimation,
        EntityManagerInterface $em
    ): Response {
        $user = $estimation->getUser();
        $form = $this->createForm(CollectUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $user) {
            $data = $form-> getData();
            $user->setLastname($data['lastname'])
                ->setFirstname($data['firstname'])
                ->setEmail($data['email'])
                ->setPhoneNumber($data['phoneNumber'])
                ->setAddress($data['address'])
                ->setPostCode($data['postCode'])
                ->setCity($data['city']);

            $em->persist($user);
            $em->flush();
        }

        return $this->render('bdc/editUser.html.twig', [
            'estimation' => $estimation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/capture/{id}", name="takePhoto")
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     */
    // route to take a photo of the Identity Card
    public function takePhoto(Estimations $estimation, EntityManagerInterface $em)
    {
        if (isset($_POST['submit'])) {
            if (!$estimation->getUser()) {
                $message = "Cette estimation n'est pas liée à un utilisateur";
                $this->addFlash('danger', $message);
                return $this->redirectToRoute('home');
            }
            // is the file exist ?
            $tmpFilePath = $_FILES['upload']['tmp_name'];
            if (!is_uploaded_file($tmpFilePath)) {
                $error = 'Le fichier est introuvable';
                $this->addFlash('danger', $error);
                return $this->render('bdc/takePhoto.html.twig', [
                    'estimation' => $estimation,
                    ]);
            }
            //save the url and the file
            if (!empty($estimation->getUser())) {
                $lastname = $estimation->getUser()->getLastname();
                $firstname = $estimation->getUser()->getFirstname();
            } else {
                $lastname = "anonyme";
                $firstname = "anonyme";
            }
            $extension = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);
            $filename = 'E' . $estimation->getId() . '-' . $lastname . '-' . $firstname . '.' . $extension;
            $filePath = "uploads/CI/$filename";

            if (move_uploaded_file($tmpFilePath, $filePath)) {
                $message = 'Merci, la photo a été enregistrée';
                $this->addFlash('success', $message);
            } else {
                $error = 'Merci de créer un dossier uploads/CI/';
                $this->addFlash('danger', $error);
            }
            // Validation of isValidatedCi in DB
            $estimation->setIsValidatedCi(true);
            $em->persist($estimation);
            $em->flush();
            return $this->redirectToRoute('bdc_show', [
                'estimation' => $estimation,
                'id' => $estimation->getId(),
                ]);
        }
        return $this->render('bdc/takePhoto.html.twig', [
            'estimation' => $estimation,
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
        if ($estimation->getUser() !== null && $estimation->getUser()->getOrganism() !== null) {
            if (($this->getUser()->getRoles()[0] == "ROLE_ADMIN")
                || ($estimation->getUser()->getOrganism() === $this->getUser()->getOrganism())) {
                return $this->render('bdc/show.html.twig', [
                    'IMEI' => "355 402 092 374 478",
                    'estimation' => $estimation,
                ]);
            }
        }
        $message = "Ce Bon de Cession n'est pas lié à un utilisateur";
        $this->addFlash('danger', $message);
        return $this->redirectToRoute('adminIndex');
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

    /**
     * @Route("/pay/{id}", name="bdc_pay")
     * @param Estimations $estimation
     * @return Response
     */
    // route to go to payment
    public function pay(Estimations $estimation)
    {
        return $this->render('bdc/pay.html.twig', [
            'estimation' => $estimation,
        ]);
    }

    /**
     * @Route("/confirm/{id}", name="confirm_photo")
     * @param Estimations $estimation
     * @return Response
     */
    // route to confirm picture of identity Card
    public function confirm(Estimations $estimation)
    {
        return $this->render('bdc/confirm.html.twig', [
            'estimation' => $estimation,
        ]);
    }

    /**
     * @Route("/end/{id}", name="bdc_end")
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     */
    // route to confirm picture of identity Card
    public function end(Estimations $estimation, EntityManagerInterface $em)
    {
        // Validation of estimation and payment
        $estimation->setIsValidatedPayment(true)->setIsCollected(true);
        $em->persist($estimation);
        $em->flush();
        return $this->render('bdc/end.html.twig', [
            'estimation' => $estimation,
        ]);
    }
}
