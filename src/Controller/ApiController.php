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
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
// Lien API chronopost avec envoi
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

            // Création d'un nom de fichier pour la sauvegarde.
            $idUser = $user->getId();
            $estimationId = $_GET['estimation'];
            $repertory = "uploads/etiquettes/";
            $filenameSave = $repertory . "id" . $idUser . "_E" . $estimationId . ".pdf";
            $filename = "id" . $idUser . "_E" . $estimationId . ".pdf";

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
                    'customerAdress1' => '391 avenue Clément Ader',
                    'customerAdress2' => '',
                    'customerCity' => 'Wambrechies',
                    'customerCivility' => 'M',
                    'customerContactName' => 'Natacha',
                    'customerCountry' => 'FR',
                    'customerCountryName' => 'FRANCE',
                    'customerEmail' => $_ENV['mail_Natacha'],
                    'customerMobilePhone' => '',
                    'customerName' => 'Bip Bip',
                    'customerName2' => '',
                    'customerPhone' => $_ENV['mobile_Natacha'],
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
                    'recipientEmail' => $_ENV['mail_Natacha'],
                    'recipientMobilePhone' => '',
                    'recipientName' => 'Enviro Services',
                    'recipientName2' => 'Bip Bip',
                    'recipientPhone' => $_ENV['mobile_Natacha'],
                    'recipientPreAlert' => 0,
                    'recipientZipCode' => '59118',
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

                // statut estimation "2" correspond à une génération d'étiquette Chronopost, sauvée sur le serveur
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
// Lien avec l'API chronopost Sans envoi : génération du code
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
                'shipperMobilePhone' => "",
                'shipperName' => $firstname,
                'shipperName2' => $name,
                'shipperPhone' => '0' . $user->getPhoneNumber(),
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
                'customerEmail' => $_ENV['mail_Natacha'],
                'customerMobilePhone' => '',
                'customerName' => 'BipBip',
                'customerName2' => '',
                'customerPhone' => $_ENV['mobile_Natacha'],
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
                'recipientEmail' => $_ENV['mail_Natacha'],
                'recipientMobilePhone' => '',
                'recipientName' => 'Bip Bip',
                'recipientName2' => '',
                'recipientPhone' => $_ENV['mobile_Natacha'],
                'recipientPreAlert' => 0,
                'recipientZipCode' => '59118',
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

        // YOU CAN FIND PARAMETERS YOU NEED IN HERE
        //var_dump($client_ch->__getFunctions());
        //var_dump($client_ch->__getTypes());

        // statut estimation "4" correspond à une génération de code envoyé au client
        if ($_GET['status'] == 4) {
            $estimation = $repository->find($_GET['estimation']);
            $estimation->setStatus(4)->setUser($user);
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
