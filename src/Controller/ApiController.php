<?php

namespace App\Controller;

use SoapClient;
use SoapFault;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @return Response
     * @throws SoapFault
     * @Route("/chronopost", name="api_chronopost")
     */
    public function apiChronopost()
    {
        $wsdl = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl";
        $clientCh = new SoapClient($wsdl);
        //$clientCh->soap_defencoding = 'UTF-8';
        //$clientCh->decode_utf8 = false;

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
                'shipperAdress1' => '1 rue du Général',
                'shipperAdress2' => '',
                'shipperCity' => 'Pom Pom Galli',
                'shipperCivility' => 'M',
                'shipperContactName' => 'George Abitbol',
                'shipperCountry' => 'FR',
                'shipperCountryName' => 'FRANCE',
                'shipperEmail' => 'jerem62026@gmail.com',
                'shipperMobilePhone' => '0788232290',
                'shipperName' => 'George',
                'shipperName2' => 'Abitbol',
                'shipperPhone' => '0788232290',
                'shipperPreAlert' => 0,
                'shipperZipCode' => '50000',
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
}
