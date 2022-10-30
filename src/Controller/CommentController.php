<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class CommentController extends AbstractController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UserRepository $userRepository,
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $entityManager,
        private readonly CommentRepository $commentRepository
    ){
    }

    #[Route('/comment', name: 'app_post_comment', methods: ['POST'])]
    public function addComment(Request $request): Response
    {
        $user = $this->fetchUser();

        if($user === null) {
            throw new AccessDeniedHttpException();
        }

        $comment = new Comment();
        $comment->setAuthor($user);
        $comment->setContent($request->request->get('content'));

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $targetUrl = $this->router->generate('app_homepage');
        return new RedirectResponse($targetUrl);
    }

    #[Route('/comment/delete', name: 'app_delete_comment', methods: ['POST'])]
    public function deleteComment(Request $request): Response
    {
        $user = $this->fetchUser();

        if(null === $user)  {
            throw new AccessDeniedHttpException();
        }

        $comment = $this->commentRepository->find($request->request->get('comment'));

        if(null === $comment) {
            $targetUrl = $this->router->generate('app_homepage');
            return new RedirectResponse($targetUrl);
        }

        if($user->getId() !== $comment->getAuthor()->getId()) {
            if(false === in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                throw new AccessDeniedHttpException();
            }
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

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