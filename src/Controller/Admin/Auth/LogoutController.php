<?php

namespace App\Controller\Admin\Auth;

use App\Manager\AuthManager;
use App\Manager\ErrorManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class LogoutController
 *
 * Logout controller provides user logout function
 * Note: Login uses its own authenticator (not Symfony auth)
 *
 * @package App\Controller\Admin\Auth
 */
class LogoutController extends AbstractController
{
    private AuthManager $authManager;
    private ErrorManager $errorManager;

    public function __construct(AuthManager $authManager, ErrorManager $errorManager)
    {
        $this->authManager = $authManager;
        $this->errorManager = $errorManager;
    }

    /**
     * User logout handler
     *
     * @throws \App\Exception\AppErrorException Logout process error
     *
     * @return Response object representing the HTTP response
     */
    #[Route('/logout', methods: ['GET'], name: 'auth_logout')]
    public function logout(): Response
    {
        // check if user loggedin
        if ($this->authManager->isUserLogedin()) {
            $this->authManager->logout();
        }

        // verify user logout
        if (!$this->authManager->isUserLogedin()) {
            return $this->redirectToRoute('auth_login');
        } else {
            // handle logpout error
            return $this->errorManager->handleError(
                'logout error: unknown error in logout function',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
