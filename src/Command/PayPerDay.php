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
    private $dayCollects = [];

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

    }
}
