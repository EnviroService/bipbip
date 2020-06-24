<?php


namespace App\Command;

use App\Repository\CollectsRepository;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class PayPerDay extends Command
{
    protected static $defaultName = 'app.payperday';

    private $collectes;
    private $mailer;

    public function __construct(CollectsRepository $collectsRepo, MailerInterface $mailer)
    {
        parent::__construct(null);
        $this->collectes = $collectsRepo->findByTomorowCollect();
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Indique le montant journalier que BipBip doit donner aux collecteurs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = "public/uploads/";
        $fileName = "dayCollects.csv";
        $handle = fopen($directory . $fileName, 'w');
        fputcsv($handle, [
            'date de collecte',
            'organisme',
            'adresse',
            'nombre tel',
            'montant total'
        ]);

        foreach ($this->collectes as $collecte) {
            $dateCollecte = $collecte->getDateCollect()->format('d-m-Y');
            $organisme = $collecte->getCollector();
            $lieu =
                $organisme->getOrganismAddress()
                . " "
                . $organisme->getOrganismPostCode()
                . " "
                . $organisme->getOrganismCity();
            $estimations = $collecte->getEstimations();

            $totalPrice = [];
            $nbrTel = 0;
            foreach ($estimations as $estimation) {
                array_push($totalPrice, $estimation->getEstimatedPrice());
                $nbrTel++;
            }
            $total = array_sum($totalPrice);

            fputcsv($handle, [
                $dateCollecte,
                $organisme->getOrganismName(),
                $lieu,
                $nbrTel,
                "$total" . "€"
            ]);
        }
        fclose($handle);

        $email = (new TemplatedEmail())
            ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
            ->addTo(new Address('contact@bipbip-mobile.fr', 'BipBip Mobile')) // For Natacha
            ->replyTo('github-test@bipbip-mobile.fr')
            ->subject("Téléphones à collectés aujourd'hui")
            ->htmlTemplate(
                'contact/sendMailForDayCollect.html.twig'
            )
            ->attachFromPath($directory . $fileName);

        $this->mailer->send($email);
    }
}
