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
        // mail for bipbip
        $yesterday = new DateTime('-1 day');
        $email = (new TemplatedEmail())
            ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
            ->to(new Address('jyaire@gmail.com', 'Jean-Roch Masson'))
            ->replyTo('github-test@bipbip-mobile.fr')
            ->subject("Estimations du ".$yesterday-> format('d/m/Y'))
            ->htmlTemplate(
                'contact/sentmailauto.html.twig'
            )
            ->context([
                'estimations' => $this->estimations,
            ]);

        $this->mailer->send($email);
    }
}
