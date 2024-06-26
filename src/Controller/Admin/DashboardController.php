<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use App\Entity\Todo;
use App\Entity\Image;
use App\Entity\Paste;
use App\Util\SiteUtil;
use App\Entity\Message;
use App\Entity\Visitor;
use App\Util\DashboardUtil;
use App\Manager\LogManager;
use App\Manager\BanManager;
use App\Manager\AuthManager;
use App\Manager\ServiceManager;
use App\Manager\VisitorManager;
use Symfony\Component\String\ByteString;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class DashboardController
 *
 * Dashboard controller provides the homepage of the admin site.
 * Dashboard components: warning box, services controller, host info, server/database counters.
 *
 * @package App\Controller\Admin
 */
class DashboardController extends AbstractController
{
    private SiteUtil $siteUtil;
    private BanManager $banManager;
    private LogManager $logManager;
    private AuthManager $authManager;
    private DashboardUtil $dashboardUtil;
    private ServiceManager $serviceManager;
    private VisitorManager $visitorManager;

    public function __construct(
        SiteUtil $siteUtil,
        BanManager $banManager,
        LogManager $logManager,
        AuthManager $authManager,
        DashboardUtil $dashboardUtil,
        ServiceManager $serviceManager,
        VisitorManager $visitorManager
    ) {
        $this->siteUtil = $siteUtil;
        $this->banManager = $banManager;
        $this->logManager = $logManager;
        $this->authManager = $authManager;
        $this->dashboardUtil = $dashboardUtil;
        $this->serviceManager = $serviceManager;
        $this->visitorManager = $visitorManager;
    }

    /**
     * Display the admin dashboard.
     *
     * @return Response object representing the HTTP response.
     */
    #[Route('/admin/dashboard', methods: ['GET'], name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            // user data
            'user_name' => $this->authManager->getUsername(),
            'user_role' => $this->authManager->getUserRole(),
            'user_pic' => $this->authManager->getUserProfilePic(),

            // warning system
            'is_web_user_sudo' => $this->dashboardUtil->isWebUserSudo(),
            'web_service_username' => $this->dashboardUtil->getWebUsername(),
            'is_ssl' => $this->siteUtil->isSsl(),
            'is_maintenance' => $this->siteUtil->isMaintenance(),
            'is_dev_mode' => $this->siteUtil->isDevMode(),
            'is_services_list_exist' => $this->serviceManager->isServicesListExist(),
            'is_browser_list_exist' => $this->dashboardUtil->isBrowserListFound(),
            'anti_log_enabled' => $this->logManager->isEnabledAntiLog(),
            'not_installed_requirements' => $this->serviceManager->getNotInstalledRequirements(),

            // dashboard (services controller)
            'services' => $this->serviceManager->getServices(),
            'is_ufw_installed' => $this->serviceManager->isServiceInstalled('ufw'),
            'is_ufw_running' => $this->serviceManager->isUfwRunning(),

            // dashboard data (System info)
            'operating_system' => str_replace('DISTRIB_ID=', '', $this->dashboardUtil->getSoftwareInfo()['distro']['operating_system']),
            'kernal_version' => $this->dashboardUtil->getSoftwareInfo()['distro']['kernal_version'],
            'kernal_arch' => $this->dashboardUtil->getSoftwareInfo()['distro']['kernal_arch'],

            // dashboard data (counters)
            'unreaded_logs_count' => $this->dashboardUtil->getDatabaseEntityCount(new Log(), ['status' => 'unreaded']),
            'messages_count' => $this->dashboardUtil->getDatabaseEntityCount(new Message(), ['status' => 'open']),
            'todos_count' => $this->dashboardUtil->getDatabaseEntityCount(new Todo(), ['status' => 'non-completed']),
            'images_count' => $this->dashboardUtil->getDatabaseEntityCount(new Image()),
            'pastest_count' => $this->dashboardUtil->getDatabaseEntityCount(new Paste()),
            'visitors_count' => $this->dashboardUtil->getDatabaseEntityCount(new Visitor()),
            'online_visitors_count' => count($this->visitorManager->getOnlineVisitorIDs()),
            'banned_visitors_count' => $this->banManager->getBannedCount(),
            'online_users_count' => count($this->authManager->getOnlineUsersList()),
            'server_uptime' => $this->dashboardUtil->getHostUptime(),
            'cpu_usage' => $this->dashboardUtil->getCpuUsage(),
            'ram_usage' => $this->dashboardUtil->getRamUsage()['used'],
            'drive_usage' => $this->dashboardUtil->getDriveUsage()
        ]);
    }

    /**
     * Run service action.
     *
     * @param Request $request object representing the HTTP request.
     *
     * @return Response object representing the HTTP response.
     */
    #[Route('/admin/dashboard/runner', methods: ['GET'], name: 'admin_service_manager')]
    public function serviceActionRunner(Request $request): Response
    {
        // get query parameters
        $serviceName = $this->siteUtil->getQueryString('service', $request);
        $action = $this->siteUtil->getQueryString('action', $request);

        // check if action is emergency shutdown
        if ($serviceName == 'emergency' && $action == 'shutdown') {
            return $this->redirectToRoute('admin_emergency_shutdown');
        } else {
            // run normal action
            $this->serviceManager->runAction($serviceName, $action);
        }
        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * Emergency shutdown page.
     *
     * @param Request $request object representing the HTTP request.
     *
     * @return Response object representing the HTTP response.
     */
    #[Route('/admin/dashboard/emergency/shutdown', methods: ['GET', 'POST'], name: 'admin_emergency_shutdown')]
    public function emergencyShutdown(Request $request): Response
    {
        // init default resources
        $errorMsg = null;

        // generate configmation code
        $confirmCode = ByteString::fromRandom(16)->toString();

        // check if request is post
        if ($request->isMethod('POST')) {
            // get post data
            $formSubmit = $request->request->get('submitShutdown');
            $shutdownCode = $request->request->get('shutdownCode');
            $confirmCode = $request->request->get('confirmCode');

            // check if form submited
            if (isset($formSubmit)) {
                // check if codes submited
                if (isset($shutdownCode) && isset($confirmCode)) {
                    // check if codes is valid
                    if ($shutdownCode == $confirmCode) {
                        // ! execute shutdown !
                        $this->serviceManager->runAction('emergency_cnA1OI5jBL', 'shutdown_MEjP9bqXF7');
                    } else {
                        $errorMsg = 'confirmation codes is not matched';
                    }
                } else {
                    $errorMsg = 'You must enter all values';
                }
            }
        }

        // render emergency shutdown page view
        return $this->render('admin/elements/confirmation/emergency-shutdown.html.twig', [
            // user data
            'user_name' => $this->authManager->getUsername(),
            'user_role' => $this->authManager->getUserRole(),
            'user_pic' => $this->authManager->getUserProfilePic(),

            // form data
            'error_msg' => $errorMsg,
            'confirm_code' => $confirmCode
        ]);
    }
}
