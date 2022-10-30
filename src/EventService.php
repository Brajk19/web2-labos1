<?php

declare(strict_types=1);

namespace App;

use App\Repository\EventRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventService
{

    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function recalculateStandings(): void
    {
        $teams = $this->teamRepository->findAll();
        $events = $this->eventRepository->findAll();

        $teamPointsMap = [];
        foreach ($teams as $team) {
            $teamPointsMap[$team->getId()] = 0;
        }

        foreach ($events as $event) {
            $homeScore = $event->getHomeScore();

            if(null === $homeScore) {
                continue;
            }

            $awayScore = $event->getAwayScore();

            $homeWin = match (true) {
                $homeScore > $awayScore => true,
                $homeScore < $awayScore => false,
                default => null //draw
            };

            if(true === $homeWin) {
                $teamPointsMap[$event->getHomeTeam()->getId()] += 3;
            } else if (false === $homeWin) {
                $teamPointsMap[$event->getAwayTeam()->getId()] += 3;
            } else {
                $teamPointsMap[$event->getHomeTeam()->getId()] += 1;
                $teamPointsMap[$event->getAwayTeam()->getId()] += 1;
            }
        }

        foreach ($teams as $team) {
            $team->setPoints(
                $teamPointsMap[$team->getId()]
            );
        }

        $this->entityManager->flush();
    }
}