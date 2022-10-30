<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\Auth0Client;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Riskio\OAuth2\Client\Provider\Auth0;
use Riskio\OAuth2\Client\Provider\Auth0ResourceOwner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Auth0Controller extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
    ){
    }

    #[Route('/connect/auth0', name: 'connect_auth0')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        //dd($clientRegistry->getClient('auth0'));
        return $clientRegistry
            ->getClient('auth0') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect();
    }

    #[Route('/connect/auth0/check', name: 'connect_auth0_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request)
    {
        $request->getSession()->clear();

        $targetUrl = $this->router->generate('app_homepage');

        return new RedirectResponse($targetUrl);
    }
}
