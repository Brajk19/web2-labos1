<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Team;
use App\Entity\User;
use App\EventService;
use App\Repository\CommentRepository;
use App\Repository\EventRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class MatchController extends AbstractController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UserRepository $userRepository,
        private readonly RouterInterface $router,
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TeamRepository $teamRepository
    ){
    }

    #[Route('/match/{event}', name: 'app_match')]
    public function showMatch(Event $event): Response
    {
        if(!in_array('ROLE_ADMIN', $this->fetchUser()->getRoles())) {
            $targetUrl = $this->router->generate('app_homepage');
            return new RedirectResponse($targetUrl);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event
        ]);
    }

    #[Route('/edit/match', name: 'app_match_edit')]
    public function editMatch(Request $request): Response
    {
        if(!in_array('ROLE_ADMIN', $this->fetchUser()->getRoles())) {
            $targetUrl = $this->router->generate('app_homepage');
            return new RedirectResponse($targetUrl);
        }

        $homeScore = $request->request->getInt('homeScore');
        $awayScore = $request->request->getInt('awayScore');
        $event = $this->eventRepository->find(
            $request->request->getInt('event')
        );

        if(null !== $event) {
            $event->setHomeScore($homeScore);
            $event->setAwayScore($awayScore);
            $this->entityManager->flush();

            $eventService = new EventService(
                $this->teamRepository,
                $this->eventRepository,
                $this->entityManager
            );
            $eventService->recalculateStandings();
        }

        $targetUrl = $this->router->generate('app_homepage');
        return new RedirectResponse($targetUrl);
    }

    private function fetchUser(): ?User
    {
        $accessToken = $this->requestStack->getSession()->get('access_token');

        if(null === $accessToken) {
            return null;
        }

        return $this->userRepository->findOneBy(['accessToken' => $accessToken]);
    }
}