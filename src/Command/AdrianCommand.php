<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Event;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:adrian',
    description: 'Fixtures',
)]
class AdrianCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeamRepository $teamRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /*$data = [
            "Manchester City",
            "Arsenal",
            "Tottenham",
            "Chelsea",
            "Manchester United",
            "Liverpool",
            "Newcastle"
        ];

        foreach ($data as $teamName) {
            $team = new Team();
            $team->setName($teamName);
            $team->setPoints(0);

            $this->entityManager->persist($team);
        }*/

        /*$event = new Event();
        $event->setHomeTeam($this->teamRepository->find(1));
        $event->setAwayTeam($this->teamRepository->find(2));
        $event->setHomeScore(2);
        $event->setAwayScore(3);
        $event->setRound(1);

        $this->entityManager->persist($event);

        $event2 = new Event();
        $event2->setHomeTeam($this->teamRepository->find(3));
        $event2->setAwayTeam($this->teamRepository->find(4));
        $event2->setHomeScore(1);
        $event2->setAwayScore(1);
        $event2->setRound(1);
        $this->entityManager->persist($event2);

        $event3 = new Event();
        $event3->setHomeTeam($this->teamRepository->find(5));
        $event3->setAwayTeam($this->teamRepository->find(6));
        $event3->setRound(2);
        $this->entityManager->persist($event3);

        $this->entityManager->flush();*/

        $user = new User();
        $user->setEmail('admin@gmail.com'); // Admin123
        $user->setRoles(['ROLE_ADMIN']);

        $user2 = new User();
        $user2->setEmail('john@gmail.com'); //John1234

        $this->entityManager->persist($user);
        $this->entityManager->persist($user2);

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
