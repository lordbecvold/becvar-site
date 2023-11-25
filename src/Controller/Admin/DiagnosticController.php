<?php

namespace App\Controller\Admin;

use App\Util\SiteUtil;
use App\Util\DashboardUtil;
use App\Manager\AuthManager;
use App\Manager\ServiceManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Diagnostic controller provides diagnostics with web & host server errors scan.
 */
class DiagnosticController extends AbstractController
{
    /** * @var SiteUtil */
    private SiteUtil $siteUtil;

    /** * @var AuthManager */
    private AuthManager $authManager;

    /** * @var DashboardUtil */
    private DashboardUtil $dashboardUtil;

    /** * @var ServiceManager */
    private ServiceManager $serviceManager;

    /**
     * DiagnosticController constructor.
     *
     * @param SiteUtil       $siteUtil
     * @param AuthManager    $authManager
     * @param DashboardUtil  $dashboardUtil
     * @param ServiceManager $serviceManager
     */
    public function __construct(
        SiteUtil $siteUtil,
        AuthManager $authManager,
        DashboardUtil $dashboardUtil,
        ServiceManager $serviceManager
    ) {
        $this->siteUtil = $siteUtil;
        $this->authManager = $authManager;
        $this->dashboardUtil = $dashboardUtil;
        $this->serviceManager = $serviceManager;
    }

    /**
     * Display diagnostics information.
     *
     * @return Response
     */
    #[Route('/admin/diagnostic', name: 'admin_diagnostics')]
    public function diagnostic(): Response
    {
        // check if user logged in
        if ($this->authManager->isUserLogedin()) {
            return $this->render('admin/diagnostic.html.twig', [
                // user data
                'user_name' => $this->authManager->getUsername(),
                'user_role' => $this->authManager->getUserRole(),
                'user_pic' => $this->authManager->getUserProfilePic(),    
                
                // system diagnostic
                'is_system_linux' => $this->dashboardUtil->isSystemLinux(),
                'drive_usage' => $this->dashboardUtil->getDriveUsage(),
                'cpu_usage' => $this->dashboardUtil->getCpuUsage(),
                'ram_usage' => $this->dashboardUtil->getRamUsage()['used'],
                'is_web_user_sudo' => $this->dashboardUtil->isWebUserSudo(),
                'web_service_username' => $this->dashboardUtil->getWebUsername(),
                
                // web diagnostics
                'is_ssl' => $this->siteUtil->isSsl(),
                'is_www_subdomain' => str_starts_with($_SERVER['HTTP_HOST'], 'www'),
                'is_dev_mode' => $this->siteUtil->isDevMode(),
                'is_maintenance' => $this->siteUtil->isMaintenance(),   
                'is_services_list_exist' => $this->serviceManager->isServicesListExist(),
                'is_browser_list_exist' => $this->dashboardUtil->isBrowserListFound()
            ]);
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }
}
