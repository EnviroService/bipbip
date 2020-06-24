<?php

namespace App\Controller;

use App\Entity\Collects;
use App\Entity\Estimations;
use App\Entity\User;
use App\Form\ImeiType;
use App\Repository\EstimationsRepository;
use App\Repository\OrganismsRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use SoapClient;
use SoapFault;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    //Permet au user de trouver un point de collecte ou d'envoyer en Chrono
    /**
     * @Route("/mode-envoi/{id}", name="mode_envoi")
     * @param Estimations $estimation
     * @return RedirectResponse|Response
     */
    public function choiceTransport(Estimations $estimation)
    {
        if ($this->getUser()->getRoles()[0] === "ROLE_ADMIN" || $this->getUser()->getRoles()[0] === "ROLE_COLLECTOR") {
            return $this->redirectToRoute('estimations_index');
        } else {
            $user = $this->getUser();


            if ($user == null) {
                $this->addFlash(
                    "error",
                    "Tu as été déconnecté pour le bien de la planéte... Connecte-toi à nouveau ..."
                );
                return $this->redirectToRoute('app_login');
            } else {
                return $this->render("user/choiceEnvoi.html.twig", [
                    'user' => $user,
                    'estimation' => $estimation
                ]);
            }
        }
    }
// Le user choisi d'envoyer par chronopost

    /**
     * @Route("/envoi-chronopost", name="envoi_chronopost")
     * @param EstimationsRepository $estimationsRepo
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function envoiChronopost(
        EstimationsRepository $estimationsRepo,
        Request $request,
        EntityManagerInterface $em
    ) {
        $user = $this->getUser();
        $estimation = $_GET['id'];
        $estimation = $estimationsRepo->find($estimation);

        $form = $this->createForm(ImeiType::class, $estimation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $imei = $data->getImei();
            $estimation->setImei(intval($imei));
            $em->persist($estimation);
            $em->flush();

            return $this->render("user/envoi_chronopost.html.twig", [
                'form' => $form->createView(),
                'user' => $user,
                'estimation' => $estimation
            ]);
        }

        if ($user == null) {
            $this->addFlash("error", "Tu as été déconnecté pour le bien de la planéte... Connecte-toi 
            à nouveau ...");
            $this->redirectToRoute('app_login');
        }

        return $this->render("user/envoi_chronopost.html.twig", [
            'form' => $form->createView(),
            'user' => $user,
            'estimation' => $estimation
        ]);
    }
// Lien API chronopost avec envoi

    /**
     * @Route("/chronopost/{id}", name="api_chronopost_ae")
     * @param User $user
     * @param EstimationsRepository $repository
     * @param EntityManagerInterface $em
     * @param OrganismsRepository $organismsRepository
     * @param MailerInterface $mailer
     * @return Response
     * @throws SoapFault
     * @throws TransportExceptionInterface
     */
    public function apiChronopostAe(
        User $user,
        EstimationsRepository $repository,
        EntityManagerInterface $em,
        OrganismsRepository $organismsRepository,
        MailerInterface $mailer
    ) {

        if ($this->getUser()->getId() == $user->getId()) {
            $wsdl = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl";
            $clientCh = new SoapClient($wsdl);

            $firstname = $user->getFirstname();
            $name = $user->getLastname();

            // Création d'un nom de fichier pour la sauvegarde.
            $idUser = $user->getId();
            $estimationId = $_GET['estimation'];
            $repertory = "uploads/etiquettes/";
            $filenameSave = $repertory . "id" . $idUser . "_E" . $estimationId . ".pdf";
            $filename = "id" . $idUser . "_E" . $estimationId . ".pdf";

            // statut estimation "2" correspond à une génération d'étiquette Chronopost.
            if ($_GET['status'] == 2) {
                $estimation = $repository->find($estimationId);
                $estimation->setStatus(2)->setUser($user);

                $organism = $organismsRepository->findOneBy([
                    'organismName' => 'Bip-Bip'
                ]);

                $collect = new Collects();
                $collect->setCollector($organism)->addClient($user)->setDateCollect(new DateTime('now'));
                $estimation->setCollect($collect);

                $em->persist($estimation);
                $em->persist($collect);
                $em->flush();
            }

            $params = [
                //STRUCTURE HEADER VALUE
                'headerValue' => [
                    'accountNumber' => $_ENV['account_chrono'],
                    'idEmit' => 'CHRFR',
                    'identWebPro' => '',
                    'subAccount' => '',
                ],
                //STRUCTURE SHIPPERVALUE (expediteur)
                'shipperValue' => [
                    'shipperAdress1' => $user->getAddress(),
                    'shipperAdress2' => '',
                    'shipperCity' => $user->getCity(),
                    'shipperCivility' => 'M',
                    'shipperContactName' => "$name $firstname",
                    'shipperCountry' => 'FR',
                    'shipperCountryName' => 'FRANCE',
                    'shipperEmail' => $user->getEmail(),
                    'shipperMobilePhone' => '',
                    'shipperName' => $firstname,
                    'shipperName2' => $name,
                    'shipperPhone' => '0' . $user->getPhoneNumber(),
                    'shipperPreAlert' => 0,
                    'shipperZipCode' => $user->getPostCode(),
                ],
                //STRUCTURE CUSTOMERVALUE (client)
                'customerValue' => [
                    'customerAdress1' => $organism->getOrganismName(),
                    'customerAdress2' => '',
                    'customerCity' => $organism->getOrganismCity(),
                    'customerCivility' => 'M',
                    'customerContactName' => 'Natacha',
                    'customerCountry' => 'FR',
                    'customerCountryName' => 'FRANCE',
                    'customerEmail' => $_ENV['mail_Natacha'],
                    'customerMobilePhone' => '',
                    'customerName' => $organism->getOrganismName(),
                    'customerName2' => '',
                    'customerPhone' => $_ENV['mobile_Natacha'],
                    'customerPreAlert' => 0,
                    'customerZipCode' => $organism->getOrganismPostcode(),
                    'printAsSender' => 'N',
                ],
                //STRUCTURE RECIPIENTVALUE (destinataire)
                'recipientValue' => [
                    'recipientAdress1' => $organism->getOrganismAddress(),
                    'recipientAdress2' => '',
                    'recipientCity' => $organism->getOrganismCity(),
                    'recipientContactName' => 'Natacha',
                    'recipientCountry' => 'FR',
                    'recipientCountryName' => 'FRANCE',
                    'recipientEmail' => $_ENV['mail_Natacha'],
                    'recipientMobilePhone' => '',
                    'recipientName' => 'Enviro Services',
                    'recipientName2' => $organism->getOrganismName(),
                    'recipientPhone' => $_ENV['mobile_Natacha'],
                    'recipientPreAlert' => 0,
                    'recipientZipCode' => $organism->getOrganismPostcode(),
                    'recipientCivility' => 'M',
                ],
                //STRUCTURE REFVALUE
                'refValue' => [
                    'customerSkybillNumber' => '',
                    'PCardTransactionNumber' => '',
                    'recipientRef' => 'E-' . $estimationId . '/' . date_format(new DateTime("now"), "d_m_Y"),
                    'shipperRef' => 'U-' . $idUser,
                ],
                //STRUCTURE SKYBILLVALUE
                'skybillValue' => [
                    'bulkNumber' => 1,
                    'codCurrency' => 'EUR',
                    'codValue' => 0,
                    'customsCurrency' => 'EUR',
                    'customsValue' => 0,
                    'evtCode' => 'DC',
                    'insuredCurrency' => 'EUR',
                    'insuredValue' => 0,
                    'masterSkybillNumber' => '?',
                    'objectType' => 'MAR',
                    'portCurrency' => 'EUR',
                    'portValue' => 0,
                    'productCode' => '01',
                    'service' => '0',
                    'shipDate' => date('c'),
                    'shipHour' => date('G'),
                    'skybillRank' => 1,
                    'weight' => 0.150,
                    'weightUnit' => 'KGM',
                    'height' => '5',
                    'length' => '25',
                    'width' => '10',
                ],

                //STRUCTURE SKYBILLPARAMSVALUE
                'skybillParamsValue' => [
                    'mode' => 'PPR',
                    'withReservation' => 0,
                ],
                //OTHERS
                'password' => $_ENV['mdp'],
                'modeRetour' => 1,
                'numberOfParcel' => 1,
                'version' => '2.0',
                'multiparcel' => 'N'
            ];

            // YOU CAN FIND PARAMETERS YOU NEED IN HERE
            //var_dump($client_ch->__getFunctions());
            //var_dump($client_ch->__getTypes());

            try {
                //Objet StdClass

                // demande la réponse de la méthode shippingMultiParcelV2
                $results = $clientCh->shippingMultiParcelV2($params);

                //récupération de l'étiquette en base64
                $pdf = $results->return->resultMultiParcelValue->pdfEtiquette;



                $openDir = scandir($repertory);

                if (!empty($openDir)) {
                    foreach ($openDir as $value) {
                        if ($filename === $value) {
                            $this->addFlash('danger', 'Votre étiquette a déjà été enregistrée, 
                            elle est disponible sur votre profil');
                            return $this->redirectToRoute('user_show');
                        }
                    }
                }

                $fichier = fopen($filenameSave, "w");
                fwrite($fichier, $pdf);
                fclose($fichier);

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
                $filename = date("Ymd") . "C" . $idUser . "P" . $estimationId . ".pdf";

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

                //$user = $estimation->getUser();
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

                return new Response($pdf, 200, [
                    'Content-Disposition' => "attachment; filename=$filename"
                ]);
            } catch (SoapFault $soapFault) {
                //var_dump($soapFault);
                echo "Request :<br>", htmlentities($clientCh->__getLastRequest()), "<br>";
                echo "Response :<br>", htmlentities($clientCh->__getLastResponse()), "<br>";
            }
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas éditer cette étiquette. Seules 
            les étiquettes vous appartenant sont disponibles');

            return $this->redirectToRoute("user_show");
        }

        return $this->redirectToRoute("user_show");
    }
// Lien avec l'API chronopost Sans envoi : génération du code

    /**
     * @Route("/chronopost/se/{id}", name="api_chronopost_se")
     * @param User $user
     * @param EstimationsRepository $repository
     * @param EntityManagerInterface $em
     * @param OrganismsRepository $organismsRepository
     * @return Response
     * @throws SoapFault
     */
    public function apiChronopostSe(
        User $user,
        EstimationsRepository $repository,
        EntityManagerInterface $em,
        OrganismsRepository $organismsRepository,
        MailerInterface $mailer
    ) {

        $wsdl = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl";
        $clientCh = new SoapClient($wsdl);

        $firstname = $user->getFirstname();
        $name = $user->getLastname();
        $estimation = $repository->find($_GET['estimation']);

        // YOU CAN FIND PARAMETERS YOU NEED IN HERE
        //var_dump($client_ch->__getFunctions());
        //var_dump($client_ch->__getTypes());

        // statut estimation "4" correspond à une génération de code envoyé au client
        if ($_GET['status'] == 4) {
            $estimation->setStatus(4)->setUser($user);
            $organism = $organismsRepository->findOneBy([
                'organismName' => 'Bip-Bip'
            ]);

            $collect = new Collects();
            $collect->setCollector($organism)->addClient($user)->setDateCollect(new DateTime('now'));
            $estimation->setCollect($collect);

            $em->persist($estimation);
            $em->persist($collect);
            $em->flush();
        }

        $params = [
            //STRUCTURE HEADER VALUE
            'headerValue' => [
                'accountNumber' => $_ENV['account_chrono'],
                'idEmit' => 'CHRFR',
                'identWebPro' => '',
                'subAccount' => '',
            ],
            //STRUCTURE SHIPPERVALUE (expediteur)
            'shipperValue' => [
                'shipperAdress1' => $user->getAddress(),
                'shipperAdress2' => '',
                'shipperCity' => $user->getCity(),
                'shipperCivility' => 'M',
                'shipperContactName' => "$name $firstname",
                'shipperCountry' => 'FR',
                'shipperCountryName' => 'FRANCE',
                'shipperEmail' => $user->getEmail(),
                'shipperMobilePhone' => '0' . $user->getPhoneNumber(),
                'shipperName' => $firstname,
                'shipperName2' => $name,
                'shipperPhone' => '0' . $user->getPhoneNumber(),
                'shipperPreAlert' => 0,
                'shipperZipCode' => $user->getPostCode(),
            ],
            //STRUCTURE CUSTOMERVALUE
            'customerValue' => [
                'customerAdress1' => $organism->getOrganismAddress(),
                'customerAdress2' => '',
                'customerCity' => $organism->getOrganismCity(),
                'customerCivility' => 'M',
                'customerContactName' => 'Natacha',
                'customerCountry' => 'FR',
                'customerCountryName' => 'FRANCE',
                'customerEmail' => $_ENV['mail_Natacha'],
                'customerMobilePhone' => '',
                'customerName' => $organism->getOrganismName(),
                'customerName2' => '',
                'customerPhone' => $_ENV['mobile_Natacha'],
                'customerPreAlert' => 0,
                'customerZipCode' => $organism->getOrganismPostcode(),
                'printAsSender' => 'N',
            ],
            //STRUCTURE RECIPIENTVALUE (destinataire)
            'recipientValue' => [
                'recipientAdress1' => $organism->getOrganismAddress(),
                'recipientAdress2' => '',
                'recipientCity' => $organism->getOrganismCity(),
                'recipientContactName' => 'Natacha',
                'recipientCountry' => 'FR',
                'recipientCountryName' => 'FRANCE',
                'recipientEmail' => $_ENV['mail_Natacha'],
                'recipientMobilePhone' => '',
                'recipientName' => $organism->getOrganismName(),
                'recipientName2' => '',
                'recipientPhone' => $_ENV['mobile_Natacha'],
                'recipientPreAlert' => 0,
                'recipientZipCode' => $organism->getOrganismPostcode(),
                'recipientCivility' => 'M',
            ],
            //STRUCTURE REFVALUE
            'refValue' => [
                'customerSkybillNumber' => '',
                'PCardTransactionNumber' => '',
                'recipientRef' => 'E-' . $_GET['estimation'] . '/' . date_format(new DateTime("now"), "d_m_Y"),
                'shipperRef' => 'U-' . $user->getId(),
            ],
            //STRUCTURE SKYBILLVALUE
            'skybillValue' => [
                'bulkNumber' => 1,
                'codCurrency' => 'EUR',
                'codValue' => 0,
                'customsCurrency' => 'EUR',
                'customsValue' => 0,
                'evtCode' => 'DC',
                'insuredCurrency' => 'EUR',
                'insuredValue' => 0,
                'masterSkybillNumber' => '?',
                'objectType' => 'MAR',
                'portCurrency' => 'EUR',
                'portValue' => 0,
                'productCode' => '01',
                'service' => '0',
                'shipDate' => date('c'),
                'shipHour' => date('G'),
                'skybillRank' => 1,
                'weight' => 0.150,
                'weightUnit' => 'KGM',
                'height' => '5',
                'length' => '25',
                'width' => '10',
            ],
            //STRUCTURE SKYBILLPARAMSVALUE
            'skybillParamsValue' => [
                'mode' => 'SLT|PDF|XML|XML2D',
                'withReservation' => 1,
            ],

            //OTHERS
            'password' => $_ENV['mdp'],
            'modeRetour' => '3',
            'numberOfParcel' => '1',
            'version' => '2.0',
            'multiparcel' => 'N'
        ];

        $clientCh->shippingMultiParcelV2($params);

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
        $filename = date("Ymd") . "C" . $user->getId() . "P" . $estimation->getId() . ".pdf";

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

        //$user = $estimation->getUser();
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
        $this->addFlash("success", "Félicitations, tu vas recevoir un mail contenant le numéro à 
        présenter au bureau de poste");

        return $this->redirectToRoute("user_show");
    }
}
