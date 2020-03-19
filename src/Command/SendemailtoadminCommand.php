<?php

namespace App\Command;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SendemailtoadminCommand extends Command
{
    protected static $defaultName = 'app:sendemailtoadmin';

    private $mailer;
    public function __construct(MailerInterface $mailer)
    {
        parent::__construct(null);
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send Mail to admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // mail for bipbip
        $email = (new TemplatedEmail())
            ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
            ->to(new Address('jyaire@gmail.com', 'Jean-Roch Masson'))
            ->replyTo('github-test@bipbip-mobile.fr')
            ->subject("Message reçu maintenant")
            ->htmlTemplate(
                'contact/sentmailauto.html.twig'
            );

        $this->mailer->send($email);
    }
}
