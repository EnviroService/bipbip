<?php

namespace App\Command;

use App\Repository\UserRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnonymiseOldUsersCommand extends Command
{
    protected static $defaultName = 'app:anonymiseoldusers';

    private $users;
    private $em;

    public function __construct(UserRepository $users, EntityManagerInterface $em)
    {
        parent::__construct(null);
        $date = new DateTime('now');
        $date->sub(new DateInterval('P3Y'));
        $this->users = $users->findOldUsers($date);
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Anonymise users not connected since 3 years');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->users;
        $em = $this->em;

        if (!empty($users)) {
            // if result, give them last signin in 1970 to forget them
            foreach ($users as $user) {
                $newEmail = $user->getId() . "@olduser.bipbip";
                $user->setEmail($newEmail)
                    ->setLastname("xxx")
                    ->setPhonenumber("0000000000")
                    ->setAddress("xxx")
                    ->setSigninDate(DateTime::createFromFormat('Y-m-d', "1970-01-01"))
                    ->setOrganism(null)
                    ->setCollect(null);
                $em->persist($user);
            }
            $em->flush();
        }
    }
}
