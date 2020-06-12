<?php


namespace App\Command;

use App\Repository\CollectsRepository;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PayPerDay extends Command
{
    protected static $defaultName = 'app.payperday';

    private $collectes;

    public function __construct(CollectsRepository $collectsRepo)
    {
        parent::__construct(null);
        $this->collectes = $collectsRepo->findByTomorowCollect();
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
            'nombre de telephone',
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
            foreach ($estimations as $estimation) {
                array_push($totalPrice, $estimation->getEstimatedPrice());
            }
            $total = array_sum($totalPrice);

            fputcsv($handle, [
                $dateCollecte,
                $organisme->getOrganismName(),
                $lieu,
                "$total" . "€"
            ]);
        }

        fclose($handle);
    }
}
