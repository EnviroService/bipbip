<?php

namespace App\Controller;

use App\Entity\Collects;
use App\Entity\Estimations;
use App\Entity\User;
use App\Repository\EstimationsRepository;
use App\Repository\OrganismsRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use SoapClient;
use SoapFault;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
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

    /**
     * @Route("/envoi-chronopost", name="envoi_chronopost")
     */
    public function envoiChronopost()
    {
        $user = $this->getUser();
        $estimation = $_GET['id'];
        if ($user == null) {
            $this->addFlash("error", "Tu as été déconnecté pour le bien de la planéte... Connecte-toi 
            à nouveau ...");
            $this->redirectToRoute('app_login');
        }

        return $this->render("user/envoi_chronopost.html.twig", [
            'user' => $user,
            'estimation' => $estimation
        ]);
    }

    /**
     * @Route("/chronopost/{id}", name="api_chronopost_ae")
     * @param User $user
     * @param EstimationsRepository $repository
     * @param EntityManagerInterface $em
     * @param OrganismsRepository $organismsRepository
     * @return Response
     * @throws SoapFault
     */
    public function apiChronopostAe(
        User $user,
        EstimationsRepository $repository,
        EntityManagerInterface $em,
        OrganismsRepository $organismsRepository
    ) {

        if ($this->getUser()->getId() == $user->getId()) {
            $wsdl = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl";
            $clientCh = new SoapClient($wsdl);
            //$clientCh->soap_defencoding = 'UTF-8';
            //$clientCh->decode_utf8 = false;

            $firstname = $user->getFirstname();
            $name = $user->getLastname();

            $params = [
                //STRUCTURE HEADER VALUE
                'headerValue' => [
                    'accountNumber' => '19869502',
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
                    'shipperPhone' => $user->getPhoneNumber(),
                    'shipperPreAlert' => 0,
                    'shipperZipCode' => $user->getPostCode(),
                ],
                //STRUCTURE CUSTOMERVALUE (client)
                'customerValue' => [
                    'customerAdress1' => '391 avenue Clément Ader',
                    'customerAdress2' => '',
                    'customerCity' => 'Wambrechies',
                    'customerCivility' => 'M',
                    'customerContactName' => 'Natacha',
                    'customerCountry' => 'FR',
                    'customerCountryName' => 'FRANCE',
                    'customerEmail' => 'test@gmail.com',
                    'customerMobilePhone' => '',
                    'customerName' => 'Bip Bip Mobile',
                    'customerName2' => '',
                    'customerPhone' => '0133333333',
                    'customerPreAlert' => 0,
                    'customerZipCode' => '59118',
                    'printAsSender' => 'N',
                ],
                //STRUCTURE RECIPIENTVALUE (destinataire)
                'recipientValue' => [
                    'recipientAdress1' => '391 avenue Clément Ader',
                    'recipientAdress2' => '',
                    'recipientCity' => 'Wambrechies',
                    'recipientContactName' => 'Natacha',
                    'recipientCountry' => 'FR',
                    'recipientCountryName' => 'FRANCE',
                    'recipientEmail' => 'test@gmail.com',
                    'recipientMobilePhone' => '',
                    'recipientName' => 'Enviro Services',
                    'recipientName2' => 'Bip Bip Mobile',
                    'recipientPhone' => '0455667788',
                    'recipientPreAlert' => 0,
                    'recipientZipCode' => '59118',
                    'recipientCivility' => 'M',
                ],
                //STRUCTURE REFVALUE
                'refValue' => [
                    'customerSkybillNumber' => '123456789',
                    'PCardTransactionNumber' => '',
                    'recipientRef' => 24,
                    'shipperRef' => 000000000000001,
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
                    'weight' => 2,
                    'weightUnit' => 'KGM',
                    'height' => '10',
                    'length' => '30',
                    'width' => '40',
                ],

                //STRUCTURE SKYBILLPARAMSVALUE
                'skybillParamsValue' => [
                    'mode' => 'PPR',
                    'withReservation' => 0,
                ],
                //OTHERS
                'password' => '255562',
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

                // Création d'un nom de fichier pour la sauvegarde.
                $idUser = $user->getId();

                // Récupération de l'id de l'estimation passée en GET
                $estimationId = $_GET['estimation'];
                $date = date("d_M_Y");
                $repertory = "uploads/etiquettes/";
                $filenameSave = $repertory . "id" . $idUser . "_" . $date . "_E" . $estimationId . ".pdf";
                $filename = "id" . $idUser . "_" . $date . "_E" . $estimationId . ".pdf";

                if ($_GET['status'] == 2) {
                    $estimation = $repository->find($estimationId);
                    $estimation->setStatus(2)->setUser($user);
                    $em->persist($estimation);

                    $organism = $organismsRepository->findOneBy([
                        'organismName' => 'Bip-Bip'
                    ]);

                    $collect = new Collects();
                    $collect->setCollector($organism)->addClient($user)->setDateCollect(new DateTime('now'));
                    $em->persist($collect);
                    $em->flush();
                }

                $openDir = scandir($repertory);

                if (!empty($openDir)) {
                    foreach ($openDir as $value) {
                        if ($filename === $value) {
                            $this->addFlash('danger', 'Votre étiquette a déjà été enregistrée, 
                        elle est disponible sur votre profil');
                            return $this->redirectToRoute('user_show', [
                                'id' => $idUser
                            ]);
                        }
                    }
                }

                $fichier = fopen($filenameSave, "w");
                fwrite($fichier, $pdf);
                fclose($fichier);

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
            $id = $this->getUser()->getId();

            return $this->redirectToRoute("user_show", [
                'id' => $id
            ]);
        }
        $id = $this->getUser()->getId();

        return $this->redirectToRoute("user_show", [
            'id' => $id
        ]);
    }

    /**
     * @Route("/chronopost/se/{id}", name="api_chronopost_se")
     * @return Response
     * @throws SoapFault
     */
    public function apiChronopostSe(
        User $user,
        EstimationsRepository $repository,
        EntityManagerInterface $em
    ) {

        $wsdl = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl";
        $clientCh = new SoapClient($wsdl);
        //$clientCh->soap_defencoding = 'UTF-8';
        //$clientCh->decode_utf8 = false;

        $firstname = $user->getFirstname();
        $name = $user->getLastname();

        $params = [
            //STRUCTURE HEADER VALUE
            'headerValue' => [
                'accountNumber' => '19869502',
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
                'shipperMobilePhone' => "",
                'shipperName' => $firstname,
                'shipperName2' => $name,
                'shipperPhone' => "0788232290",
                'shipperPreAlert' => 0,
                'shipperZipCode' => $user->getPostCode(),
            ],
            //STRUCTURE CUSTOMERVALUE
            'customerValue' => [
                'customerAdress1' => '391 avenue Clément Ader',
                'customerAdress2' => '',
                'customerCity' => 'Wambrechies',
                'customerCivility' => 'M',
                'customerContactName' => 'Natacha',
                'customerCountry' => 'FR',
                'customerCountryName' => 'FRANCE',
                'customerEmail' => 'test@gmail.com',
                'customerMobilePhone' => '0611223344',
                'customerName' => 'BipBip Mobile',
                'customerName2' => '',
                'customerPhone' => '0133333333',
                'customerPreAlert' => 0,
                'customerZipCode' => '59118',
                'printAsSender' => 'N',
            ],
            //STRUCTURE RECIPIENTVALUE (destinataire)
            'recipientValue' => [
                'recipientAdress1' => '391 avenue Clément Ader',
                'recipientAdress2' => '',
                'recipientCity' => 'Wambrechies',
                'recipientContactName' => 'Natacha',
                'recipientCountry' => 'FR',
                'recipientCountryName' => 'FRANCE',
                'recipientEmail' => 'test@gmail.com',
                'recipientMobilePhone' => '',
                'recipientName' => 'Bip Bip Mobile',
                'recipientName2' => '',
                'recipientPhone' => '0655667788',
                'recipientPreAlert' => 0,
                'recipientZipCode' => '59118',
                'recipientCivility' => 'M',
            ],
            //STRUCTURE REFVALUE
            'refValue' => [
                'customerSkybillNumber' => '123456789',
                'PCardTransactionNumber' => '',
                'recipientRef' => 24,
                'shipperRef' => 000000000000001,
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
                'weight' => 2,
                'weightUnit' => 'KGM',
                'height' => '10',
                'length' => '30',
                'width' => '40',
            ],
            //STRUCTURE SKYBILLPARAMSVALUE
            'skybillParamsValue' => [
                'mode' => 'SLT|PDF|XML|XML2D',
                'withReservation' => 1,
            ],

            //OTHERS
            'password' => '255562',
            'modeRetour' => '3',
            'numberOfParcel' => '1',
            'version' => '2.0',
            'multiparcel' => 'N'
        ];

        // YOU CAN FIND PARAMETERS YOU NEED IN HERE
        //var_dump($client_ch->__getFunctions());
        //var_dump($client_ch->__getTypes());

        if ($_GET['status'] == 2) {
            $estimation = $repository->find($_GET['estimation']);
            $estimation->setStatus(2)->setUser($user);
            $em->persist($estimation);
            $em->flush();
        }

        $clientCh->shippingMultiParcelV2($params);

        $this->addFlash("success", "Félicitations, tu vas recevoir un mail contenant le numéro à 
        présenter au bureau de poste");

        return $this->redirectToRoute("user_show", [
            'id' => $user->getId()
        ]);
    }
}
