<?php

namespace App\Command;

use App\Repository\ReportingRepository;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SendemailtoadminCommand extends Command
{
    protected static $defaultName = 'app:sendemailtoadmin';

    private $mailer;
    private $toBeCollected;
    private $collected;
    public function __construct(MailerInterface $mailer, ReportingRepository $reporting)
    {
        parent::__construct(null);
        $this->mailer = $mailer;
        $this->toBeCollected = $reporting->findByEstimatedYesterday();
        $this->collected = $reporting->findByCollectedYesterday();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send 2 mails to admin with estimated and collected of the 24h before')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // create csv file, format A-COLLECTER_DATE_NBITEMS.csv for phones to be collected from 24h
        $path = "public/uploads/reporting/a-collecter/";
        $today = new DateTime('now');
        $nbitems = count($this->toBeCollected);
        $filename = "A-COLLECTER_" . $today->format('Ymd') . "_" . $nbitems .".csv";

        $file = fopen($path . $filename, 'w'); // writing mode
        if ($file != false) {
            $headerData = [
                'Customer',
                'Model',
                'Serial Number',
                'Reference Number',
                'WO CODE',
                'PROGRAM',
                'WO TYPE',
                'WO INITIAL STATUS',
                'GROUP',
                'SITE',
                'PURCHASE DATE',
                'ReportedIssue',
                'Customer_Info_1',
                'Customer_Info_2',
                'Customer_Info_3',
            ];
            fputcsv($file, $headerData);

            $fileData = [];
            foreach ($this->toBeCollected as $collect) {
                $estimation = $collect->getEstimation();
                $customer = "BIPBIP COLLECTE";
                $model = $estimation->getModel().' '.$estimation->getCapacity();
                $serialNumber = $estimation->getImei();
                $referenceNumber = $estimation->getId();
                $prixReprise = $estimation->getEstimatedPrice();
                array_push($fileData, [
                    $customer,
                    $model,
                    $serialNumber,
                    $referenceNumber,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $prixReprise,
                    '',
                ]);
            }
            foreach ($fileData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }

        // mail for admin to show collected phones during a day
        $yesterday = new DateTime('-1 day');
        $email = (new TemplatedEmail())
            ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
            ->to(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile')) // For Paul
            ->addTo(new Address('github-prod@bipbip-mobile.fr', 'BipBip Mobile')) // For Natacha
            ->replyTo('github-test@bipbip-mobile.fr')
            ->subject("Téléphones engagés en collecte le ".$yesterday-> format('d/m/Y'))
            ->htmlTemplate(
                'contact/mailAutoToBeCollected.html.twig'
            )
            ->context([
                'toBeCollected' => $this->toBeCollected,
                'collected' => $this->collected,
            ])
            ->attachFromPath($path.$filename);

        $this->mailer->send($email);

        // create csv file, format COLLECTEES_DATE_NBITEMS.csv for phones collected from 24h
        $path = "public/uploads/reporting/collectees/";
        $today = new DateTime('now');
        $nbitems = count($this->collected);
        $filename = "COLLECTEES_" . $today->format('Ymd') . "_" . $nbitems .".csv";

        $file = fopen($path . $filename, 'w'); // writing mode
        if ($file != false) {
            $headerData = [
                'Customer',
                'Model',
                'Serial Number',
                'Reference Number',
                'WO CODE',
                'PROGRAM',
                'WO TYPE',
                'WO INITIAL STATUS',
                'GROUP',
                'SITE',
                'PURCHASE DATE',
                'ReportedIssue',
                'Customer_Info_1',
                'Customer_Info_2',
                'Customer_Info_3',
            ];
            fputcsv($file, $headerData);

            $fileData = [];
            foreach ($this->collected as $collect) {
                $estimation = $collect->getEstimation();
                $customer = "BIPBIP COLLECTE";
                $model = $estimation->getModel().' '.$estimation->getCapacity();
                $serialNumber = $estimation->getImei();
                $referenceNumber = $estimation->getId();
                $prixReprise = $estimation->getEstimatedPrice();
                array_push($fileData, [
                    $customer,
                    $model,
                    $serialNumber,
                    $referenceNumber,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $prixReprise,
                    '',
                ]);
            }
            foreach ($fileData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }

        // mail for admin to show collected phones during a day
        $yesterday = new DateTime('-1 day');
        $email = (new TemplatedEmail())
            ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
            ->to(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile')) // For Paul
            ->addTo(new Address('github-prod@bipbip-mobile.fr', 'BipBip Mobile')) // For Natacha
            ->replyTo('github-test@bipbip-mobile.fr')
            ->subject("Téléphones collectés le ".$yesterday-> format('d/m/Y'))
            ->htmlTemplate(
                'contact/mailAutoCollected.html.twig'
            )
            ->context([
                'toBeCollected' => $this->toBeCollected,
                'collected' => $this->collected,
            ])
            ->attachFromPath($path.$filename);

        $this->mailer->send($email);
    }
}
