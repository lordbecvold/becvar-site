<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use App\Entity\Todo;
use App\Entity\Image;
use App\Entity\Paste;
use App\Util\SiteUtil;
use App\Entity\Message;
use App\Entity\Visitor;
use App\Helper\LogHelper;
use App\Util\SecurityUtil;
use App\Util\DashboardUtil;
use App\Manager\AuthManager;
use App\Util\VisitorInfoUtil;
use App\Manager\ServiceManager;
use Symfony\Component\String\ByteString;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/*
    Dashboard controller provides homepage of admin site
*/

class DashboardController extends AbstractController
{
    private $siteUtil;
    private $logHelper;
    private $authManager;
    private $securityUtil;
    private $dashboardUtil;
    private $serviceManager;
    private $visitorInfoUtil;

    public function __construct(
        SiteUtil $siteUtil,
        LogHelper $logHelper,
        AuthManager $authManager, 
        SecurityUtil $securityUtil,
        DashboardUtil $dashboardUtil,
        ServiceManager $serviceManager,
        VisitorInfoUtil $visitorInfoUtil
    ) {
        $this->siteUtil = $siteUtil;
        $this->logHelper = $logHelper;
        $this->authManager = $authManager;
        $this->securityUtil = $securityUtil;
        $this->dashboardUtil = $dashboardUtil;
        $this->serviceManager = $serviceManager;
        $this->visitorInfoUtil = $visitorInfoUtil;
    }

    #[Route('/admin', name: 'admin_init')]
    public function admin(): Response
    {
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/dashboard/emergency/shutdown', name: 'admin_emergency_shutdown')]
    public function emergencyShutdown (): Response
    {
        $error_msg = null;

        // generate configmation code
        $confirm_code = ByteString::fromRandom(16)->toString();

        // check if form submited
        if (isset($_POST['submitShutdown'])) {

            // check if data is empty
            if (empty($_POST['confirmCode']) or empty($_POST['shutdownCode'])) {
                $error_msg = 'You must enter all values';
            } else {
                // get form data & escape
                $code_1 = $this->securityUtil->escapeString($_POST['confirmCode']);
                $code_2 = $this->securityUtil->escapeString($_POST['shutdownCode']);

                // check if codes is valid
                if ($code_1 == $code_2) {

                    // execute shutdown
                    $this->serviceManager->runAction('emergency_cnA1OI5jBL', 'shutdown_MEjP9bqXF7');
                } else {
                    $error_msg = 'confirmation codes is not matched';
                }
            }
        }

        return $this->render('admin/elements/confirmation/emergency-shutdown.html.twig', [
            // component properties
            'is_mobile' => $this->visitorInfoUtil->isMobile(),
            'is_dashboard' => false,

            // user data
            'user_name' => $this->authManager->getUsername(),
            'user_role' => $this->authManager->getUserRole(),
            'user_pic' => $this->authManager->getUserProfilePic(),

            // form data
            'error_msg' => $error_msg,
            'confirm_code' => $confirm_code
        ]);
    } 

    #[Route('/admin/dashboard/runner/{service_name}/{action}', name: 'admin_service_manager')]
    public function serviceRunner(string $service_name, string $action): Response
    {
        // check if user logged in
        if ($this->authManager->isUserLogedin()) {

            // escape values
            $service_name = $this->securityUtil->escapeString($service_name);
            $action = $this->securityUtil->escapeString($action);

            // check if action is emergency shutdown
            if ($service_name == 'emergency' && $action == 'shutdown') {
                return $this->redirectToRoute('admin_emergency_shutdown'); 
            } else {
                // run normal action
                $this->serviceManager->runAction($service_name, $action);
            }
        } else {
            return $this->redirectToRoute('auth_login');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        // check if user logged in
        if ($this->authManager->isUserLogedin()) {
            return $this->render('admin/dashboard.html.twig', [
                // component properties
                'is_mobile' => $this->visitorInfoUtil->isMobile(),
                'is_dashboard' => true,

                // user data
                'user_name' => $this->authManager->getUsername(),
                'user_role' => $this->authManager->getUserRole(),
                'user_pic' => $this->authManager->getUserProfilePic(),

                // warning system
                'is_warnings_empty' => $this->dashboardUtil->isWarninBoxEmpty(),
                'service_dir_exist' => file_exists($_ENV['SERVICES_DIR']),
                'service_dir' => $_ENV['SERVICES_DIR'],
                'is_web_user_sudo' => $this->dashboardUtil->isWebUserSudo(),
                'is_ssl' => $this->siteUtil->isSsl(),
                'is_maintenance' => $this->siteUtil->isMaintenance(),   
                'is_dev_mode' => $this->siteUtil->isDevMode(),
                'is_services_list_exist' => $this->serviceManager->isServicesListExist(),
                'anti_log_enabled' => $this->logHelper->isEnabledAntiLog(),

                // dashboard (services controller)
                'services' => $this->serviceManager->getServices(),
                
                // dashboard data (System info)
                'operating_system' => str_replace("DISTRIB_ID=", "", $this->dashboardUtil->getSoftwareInfo()["distro"]["operating_system"]),
                'kernal_version' => $this->dashboardUtil->getSoftwareInfo()["distro"]["kernal_version"],
                'kernal_arch' => $this->dashboardUtil->getSoftwareInfo()["distro"]["kernal_arch"],

                // dashboard data (counters)
                'unreaded_logs_count' => $this->dashboardUtil->getDatabaseEntityCount(new Log, ['status' => 'unreaded']),
                'messages_count' => $this->dashboardUtil->getDatabaseEntityCount(new Message, ['status' => 'open']),
                'todos_count' => $this->dashboardUtil->getDatabaseEntityCount(new Todo, ['status' => 'non-completed']),
                'images_count' => $this->dashboardUtil->getDatabaseEntityCount(new Image),
                'pastest_count' => $this->dashboardUtil->getDatabaseEntityCount(new Paste),
                'visitors_count' => $this->dashboardUtil->getDatabaseEntityCount(new Visitor),
                'server_uptime' => $this->dashboardUtil->getHostUptime(),   
                'cpu_usage' => $this->dashboardUtil->getCpuUsage(),   
                'ram_usage' => $this->dashboardUtil->getRamUsage()['used'],   
                'drive_usage' => $this->dashboardUtil->getDriveUsage()
            ]);
        } else {
            return $this->redirectToRoute('auth_login');
        }
    }
}
