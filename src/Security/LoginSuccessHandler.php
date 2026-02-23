<?php

namespace App\Security;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /** @var UrlGeneratorInterface */
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();

        // Ensure $user is an instance of App\Entity\User
        if (!$user instanceof User) {
            return new RedirectResponse($this->router->generate('app_home'));
        }

        $roles = $user->getRoles();

        // Admin goes to dashboard
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        // Regular user goes to Step 6 page
        if (in_array('ROLE_USER', $roles, true)) {
            // Now getToken is safe
            return new RedirectResponse($this->router->generate('exchange_step_1', [
                'token' => $user->getToken()
            ]));
        }

        // fallback
        return new RedirectResponse($this->router->generate('app_home'));
    }
}
