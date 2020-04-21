<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendemailtooldusersCommand extends Command
{
    protected static $defaultName = 'app:sendemailtooldusers';

    private $mailer;
    private $users;
    public function __construct(MailerInterface $mailer, UserRepository $users)
    {
        parent::__construct(null);
        $this->mailer = $mailer;
        $this->users = $users->findFutureOldUsers();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send mail to old user 15 days before deletion')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!empty($this->users)) {
            foreach ($this->users as $user) {
                // mail for users 1 month before the end of 3 years inactivity
                $email = (new TemplatedEmail())
                    ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                    ->to(new Address($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname()))
                    ->replyTo('github-test@bipbip-mobile.fr')
                    ->subject("Ton compte BipBip sera bientôt supprimé, reviens-vite !")
                    ->htmlTemplate(
                        'contact/mailAutoDeleteAccount.html.twig'
                    );

                $this->mailer->send($email);
            }
        }
    }
}
