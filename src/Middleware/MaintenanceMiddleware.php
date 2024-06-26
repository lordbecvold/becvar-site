<?php

namespace App\Middleware;

use App\Util\SiteUtil;
use App\Manager\ErrorManager;

/**
 * Class MaintenanceMiddleware
 *
 * This middleware is used to check if the application is in maintenance mode.
 *
 * @package App\Middleware
 */
class MaintenanceMiddleware
{
    private SiteUtil $siteUtil;
    private ErrorManager $errorManager;

    public function __construct(SiteUtil $siteUtil, ErrorManager $errorManager)
    {
        $this->siteUtil = $siteUtil;
        $this->errorManager = $errorManager;
    }

    /**
     * Check if the application is in maintenance mode.
     *
     * @return void
     */
    public function onKernelRequest(): void
    {
        // check if MAINTENANCE_MODE enabled
        if ($this->siteUtil->isMaintenance()) {
            die($this->errorManager->handleErrorView('maintenance'));
        }
    }
}
