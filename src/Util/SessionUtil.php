<?php

namespace App\Util;

use App\Manager\ErrorManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SessionUtil
 *
 * SessionUtil provides session management functions
 *
 * @package App\Util
 */
class SessionUtil
{
    private SecurityUtil $securityUtil;
    private ErrorManager $errorManager;

    public function __construct(SecurityUtil $securityUtil, ErrorManager $errorManager)
    {
        $this->securityUtil = $securityUtil;
        $this->errorManager = $errorManager;
    }

    /**
     * Start a new session if not already started
     *
     * @return void
     */
    public function startSession(): void
    {
        if (session_status() == PHP_SESSION_NONE && (!headers_sent())) {
            session_start();
        }
    }

    /**
     * Destroy the current session
     *
     * @return void
     */
    public function destroySession(): void
    {
        $this->startSession();
        session_destroy();
    }

    /**
     * Check if a session with the specified name exists
     *
     * @param string $sessionName The name of the session to check
     *
     * @return bool Whether the session exists
     */
    public function checkSession(string $sessionName): bool
    {
        $this->startSession();
        return isset($_SESSION[$sessionName]);
    }

    /**
     * Set a session value.
     *
     * @param string $sessionName The name of the session
     * @param string $sessionValue The value to set for the session
     *
     * @return void
     */
    public function setSession(string $sessionName, string $sessionValue): void
    {
        $this->startSession();
        $_SESSION[$sessionName] = $this->securityUtil->encryptAes($sessionValue);
    }

    /**
     * Get the decrypted value of a session
     *
     * @param string $sessionName The name of the session
     *
     * @return mixed The decrypted session value
     */
    public function getSessionValue(string $sessionName): mixed
    {
        $this->startSession();

        // decrypt session value
        $value = $this->securityUtil->decryptAes($_SESSION[$sessionName]);

        // check if session data is decrypted
        if ($value == null) {
            $this->destroySession();
            $this->errorManager->handleError(
                'error to decrypt session data',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $value;
    }
}
