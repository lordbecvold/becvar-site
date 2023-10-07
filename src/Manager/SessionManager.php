<?php

namespace App\Manager;

use App\Util\SecurityUtil;

/*
    Session manager provides session managment
*/

class SessionManager
{
    private $securityUtil;

    public function __construct(SecurityUtil $securityUtil)
    {
        $this->securityUtil = $securityUtil;
    }

    public function startSession(): void 
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function destroySession() {
        $this->startSession();
        session_destroy();
    }

    public function checkSession(string $session_name): bool {
        $this->startSession();
        if (isset($_SESSION[$session_name])) {
            return true;
        } else {
            return false;
        }
    }

    public function setSession(string $session_name, string $session_value): void 
    {
        $this->startSession();
        $_SESSION[$session_name] = $this->securityUtil->encrypt_aes($session_value);
    }

    public function getSessionValue(string $session_name): ?string 
    {
        $this->startSession();
        return $this->securityUtil->decrypt_aes($_SESSION[$session_name]);
    }
}
