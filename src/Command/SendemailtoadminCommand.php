<?php

namespace App\Command;

use App\Repository\EstimationsRepository;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendemailtoadminCommand extends Command
{
    protected static $defaultName = 'app:sendemailtoadmin';

    private $mailer;
    private $estimations;
    public function __construct(MailerInterface $mailer, EstimationsRepository $estimations)
    {
        parent::__construct(null);
        $this->mailer = $mailer;
        $this->estimations = $estimations->findByEstimatedYesterday();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send Mail to admin with estimations of the day')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // create csv file, format COLLECTEES_DATE_NBITEMS.csv
        $path = "public/uploads/reporting/collectees/";
        $today = new DateTime('now');
        $nbitems = 3;
        $filename = "COLLECTEES_" . $today->format('Ymd') . "_" . $nbitems .".csv";

        $file = fopen($path . $filename, 'w'); // writing mode
        if ($file != false) {
            $headerData = ['id', 'title', 'price']; // TODO change for csv Enviro
            fputcsv($file, $headerData);
            $fileData = [ // TODO change with datas
                ['1', 'riri', '3'],
                ['2', 'loulou', '2'],
                ['3', 'fifi', '1'],
            ];
            foreach ($fileData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }

        // mail for admin to show collected phones during a day
        $yesterday = new DateTime('-1 day');
        $email = (new TemplatedEmail())
            ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
            ->to(new Address('jyaire@gmail.com', 'Jean-Roch Masson'))
            ->addTo(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile')) // For Paul
            ->addTo(new Address('github-prod@bipbip-mobile.fr', 'BipBip Mobile')) // For Natacha
            ->replyTo('github-test@bipbip-mobile.fr')
            ->subject("Estimations du ".$yesterday-> format('d/m/Y'))
            ->htmlTemplate(
                'contact/sentmailauto.html.twig'
            )
            ->context([
                'estimations' => $this->estimations,
            ])
            ->attachFromPath($path.$filename);

        $this->mailer->send($email);
    }
}
