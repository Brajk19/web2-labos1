<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\Auth0Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Riskio\OAuth2\Client\Provider\Auth0;
use Riskio\OAuth2\Client\Provider\Auth0ResourceOwner;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class Auth0Authenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly RequestStack $requestStack
    ){
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/auth0', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_auth0_check';
    }

    public function authenticate(Request $request): Passport
    {
        /** @var Auth0Client $client */
        $client = $this->clientRegistry->getClient('auth0');

        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var Auth0ResourceOwner $facebookUser */
                $auth0User = $client->fetchUserFromToken($accessToken);

                $data = $auth0User->toArray();

                $user = $this->userRepository->findOneBy(['email' => $auth0User->getEmail()]);
                if(null === $user) {
                    throw new BadRequestException();
                }

                $session = $this->requestStack->getSession();
                $session->set('access_token', $accessToken->getToken());

                $user->setAccessToken($accessToken->getToken());
                $user->setAuth0Id($data['sub']);

                $this->entityManager->flush();
                return $user;
            })
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->router->generate('app_homepage');

        return new RedirectResponse($targetUrl);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}