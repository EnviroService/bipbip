<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SoapClient;
use SoapFault;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/mode-envoi", name="mode_envoi")
     */
    public function choiceTransport()
    {
        $user = $this->getUser();


        if ($user == null) {
            $this->addFlash("error", "Tu a été déconnecté pour le bien de la planéte... Connecte toi à nouveau ..");
            return $this->redirectToRoute('app_login');
        } else {
            return $this->render("user/choiceEnvoi.html.twig", [
                'user' => $user
            ]);
        }
    }

    /**
     * @Route("/envoi-chronopost", name="envoi_chronopost")
     */
    public function envoiChronopost()
    {
        $user = $this->getUser();
        if ($user == null) {
            $this->addFlash("error", "Tu a été déconnecté pour le bien de la planéte... Connecte toi à nouveau ..");
            $this->redirectToRoute('app_login');
        }
        return $this->render("user/envoi_chronopost.html.twig", [
            'user' => $user
        ]);
    }

    /**
     * @Route("/chronopost/{id}", name="api_chronopost_ae")
     * @return Response
     * @throws SoapFault
     */
    public function apiChronopostAe(User $user)
    {

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
                'shipperMobilePhone' => $user->getPhoneNumber(),
                'shipperName' => $firstname,
                'shipperName2' => $name,
                'shipperPhone' => $user->getPhoneNumber(),
                'shipperPreAlert' => 0,
                'shipperZipCode' => $user->getPostCode(),
            ],
            //STRUCTURE CUSTOMERVALUE
            'customerValue' => [
                'customerAdress1' => '40 RUE JEAN JAURES',
                'customerAdress2' => '',
                'customerCity' => 'MONFRIN',
                'customerCivility' => 'M',
                'customerContactName' => 'Jean MARTIN',
                'customerCountry' => 'FR',
                'customerCountryName' => 'FRANCE',
                'customerEmail' => 'jerem62026@gmail.com',
                'customerMobilePhone' => '0611223344',
                'customerName' => 'The Journal',
                'customerName2' => '',
                'customerPhone' => '0133333333',
                'customerPreAlert' => 0,
                'customerZipCode' => '72000',
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
                'recipientMobilePhone' => '0655667788',
                'recipientName' => 'BipBip Mobile',
                'recipientName2' => '',
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
            'modeRetour' => '1',
            'numberOfParcel' => 1,
            'version' => '',
            'multiparcel' => 'N'
        ];

        // YOU CAN FIND PARAMETERS YOU NEED IN HERE
        //var_dump($client_ch->__getFunctions());
        //var_dump($client_ch->__getTypes());

        $results = $clientCh->shippingMultiParcelV2($params);
        $pdf = $results->return->resultMultiParcelValue->pdfEtiquette;

        try {
            //Objet StdClass
            $results = $clientCh->shippingMultiParcelV2($params);
            $pdf = $results->return->resultMultiParcelValue->pdfEtiquette;
        } catch (SoapFault $soapFault) {
            //var_dump($soapFault);
            echo "Request :<br>", htmlentities($clientCh->__getLastRequest()), "<br>";
            echo "Response :<br>", htmlentities($clientCh->__getLastResponse()), "<br>";
        }

        return new Response($pdf, 200, ['Content-Type' => 'application/pdf']);
    }

    /**
     * @Route("/chronopost/{id}", name="api_chronopost_se")
     * @return Response
     * @throws SoapFault
     */
    public function apiChronopostSe(User $user)
    {

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
                'shipperMobilePhone' => $user->getPhoneNumber(),
                'shipperName' => $firstname,
                'shipperName2' => $name,
                'shipperPhone' => "0788232290",
                'shipperPreAlert' => 0,
                'shipperZipCode' => $user->getPostCode(),
            ],
            //STRUCTURE CUSTOMERVALUE
            'customerValue' => [
                'customerAdress1' => '40 RUE JEAN JAURES',
                'customerAdress2' => '',
                'customerCity' => 'MONFRIN',
                'customerCivility' => 'M',
                'customerContactName' => 'Jean MARTIN',
                'customerCountry' => 'FR',
                'customerCountryName' => 'FRANCE',
                'customerEmail' => 'jerem62026@gmail.com',
                'customerMobilePhone' => '0611223344',
                'customerName' => 'The Journal',
                'customerName2' => '',
                'customerPhone' => '0133333333',
                'customerPreAlert' => 0,
                'customerZipCode' => '72000',
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
                'recipientMobilePhone' => '0655667788',
                'recipientName' => 'BipBip Mobile',
                'recipientName2' => '',
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
                'mode' => 'SLT|PDF|XML|XML2D',
                'withReservation' => '1',
            ],
            //OTHERS
            'password' => '255562',
            'modeRetour' => '3',
            'numberOfParcel' => '1',
            'version' => '',
            'multiparcel' => 'N'
        ];

        // YOU CAN FIND PARAMETERS YOU NEED IN HERE
        //var_dump($client_ch->__getFunctions());
        //var_dump($client_ch->__getTypes());

        $results = $clientCh->shippingMultiParcelV2($params);
        //$reservation = $results->return->reservationNumber;
        $etiquette = $results->return->resultMultiParcelValue;

        try {
            //Objet StdClass
            $results = $clientCh->shippingMultiParcelV2($params);
        } catch (SoapFault $soapFault) {
            //var_dump($soapFault);
            echo "Request :<br>", htmlentities($clientCh->__getLastRequest()), "<br>";
            echo "Response :<br>", htmlentities($clientCh->__getLastResponse()), "<br>";
        }

        $this->addFlash("success", "Félicitation, tu va recevoir un sms contenant le numéro à 
        présenter au bureau de poste");

        return new Response($etiquette);
    }
}
