<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\EventRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly TeamRepository $teamRepository,
        private readonly RequestStack $requestStack,
        private readonly UserRepository $userRepository,
        private readonly CommentRepository $commentRepository
    ){
    }

    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        $teams = $this->teamRepository->findBy([], ['points' => 'DESC']);
        $eventsAll = $this->eventRepository->findAll();

        $events = [];
        foreach ($eventsAll as $event) {
            if(!array_key_exists($event->getRound(), $events)) {
                $events[$event->getRound()] = [];
            }

            $events[$event->getRound()][] = $event;
        }

        $user = $this->fetchUser();

        return $this->render('base.html.twig', [
            'controller_name' => 'HomeController',
            'comments' => $this->commentRepository->findAll(),
            'eventsData' => $events,
            'teams' => $teams,
            'loggedIn' => $user !== null,
            'user' => $user,
            'admin' => $user !== null && in_array('ROLE_ADMIN', $user->getRoles(), true)
        ]);
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
