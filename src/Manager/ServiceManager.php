<?php

namespace App\Manager;

use App\Util\JsonUtil;

/**
 * Class AuthManager
 *
 * Service manager provides all services methods (start, stop, status)
 *
 * @package App\Manager
*/
class ServiceManager
{
    private JsonUtil $jsonUtil;
    private LogManager $logManager;
    private AuthManager $authManager;
    private ErrorManager $errorManager;

    public function __construct(
        JsonUtil $jsonUtil,
        LogManager $logManager,
        AuthManager $authManager,
        ErrorManager $errorManager
    ) {
        $this->jsonUtil = $jsonUtil;
        $this->logManager = $logManager;
        $this->authManager = $authManager;
        $this->errorManager = $errorManager;
    }

    /**
     * Gets the list of services.
     *
     * @return array<array<string>>
     */
    public function getServices(): ?array
    {
        // get services list from services.json
        $servicesList = $this->getServicesJson();
        $services = [];

        // check if services list load valid
        if ($servicesList != null) {
            // execute separate service row
            foreach ($servicesList as $value) {
                // check if service is enabled
                if ($value['enable']) {
                    // build service array
                    $serviceArray = [
                        'service_name' => $value['service_name'],
                        'display_name' => $value['display_name'],
                        'enable' => $value['enable']
                    ];

                    // get service status
                    if ($this->isServiceRunning($value['service_name'])) {
                        $serviceArray += ['status' => 'online'];
                    } else {
                        $serviceArray += ['status' => 'offline'];
                    }

                    // add serviceArray array to services
                    array_push($services, $serviceArray);
                }
            }
        } else {
            $this->logManager->log('app-error', 'error to get services-list.json file, try check app root if file exist');
        }

        return $services;
    }

    /**
     * Runs an action on a specified service.
     *
     * @param string $serviceName The name of the service.
     * @param string $action The action to run on the service.
     *
     * @return void
     */
    public function runAction(string $serviceName, string $action): void
    {
        // check if user logged in
        if ($this->authManager->isUserLogedin()) {
            $command = null;

            // check if action is emergency shutdown
            if ($serviceName == 'emergency_cnA1OI5jBL' && $action == 'shutdown_MEjP9bqXF7') {
                $this->emergencyShutdown();
            } elseif ($serviceName == 'ufw') {
                $command = 'sudo ufw ' . $action;
            } else {
                // build action
                $command = 'sudo systemctl ' . $action . ' ' . $serviceName;
            }

            // log action
            $this->logManager->log('action-runner', $this->authManager->getUsername() . ' ' . $action . 'ed ' . $serviceName);

            // executed final command
            $this->executeCommand($command);
        } else {
            $this->errorManager->handleError('error action runner is only for authentificated users', 401);
        }
    }

    /**
     * Checks if a service is or is php extension installed.
     *
     * @param string $serviceName The name of the service.
     *
     * @return bool The service is installed, false otherwise.
     */
    public function isServiceInstalled(string $serviceName): bool
    {
        // check dpkg package
        exec('dpkg -l | grep ' . escapeshellarg($serviceName), $output, $returnCode);

        if ($returnCode === 0) {
            return true;
        }

        // check php extension
        if (extension_loaded($serviceName)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a service is running.
     *
     * @param string $service The name of the service.
     *
     * @return bool The service is running, false otherwise.
     */
    public function isServiceRunning(string $service): bool
    {
        $output = shell_exec('systemctl is-active ' . $service);

        if ($output == null) {
            return false;
        }

        // check if service running
        if (trim($output) == 'active') {
            return true;
        }

        return false;
    }

    /**
     * Checks if a socket is open.
     *
     * @param string $ip The IP address.
     * @param int $port The port number.
     *
     * @return string Online if the socket is open, Offline otherwise.
     */
    public function isSocktOpen(string $ip, int $port): string
    {
        // open service socket
        $service = @fsockopen($ip, $port);

        // check is service online
        if ($service >= 1) {
            return 'Online';
        }

        return 'Offline';
    }

    /**
     * Checks if a process is running.
     *
     * @param string $process The name of the process.
     *
     * @return bool The process is running, false otherwise.
     */
    public function isProcessRunning(string $process): bool
    {
        exec('pgrep ' . $process, $pids);

        // check if outputed pid
        if (empty($pids)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if UFW (Uncomplicated Firewall) is running.
     *
     * @return bool UFW is running, false otherwise.
     */
    public function isUfwRunning(): bool
    {
        try {
            // execute cmd
            $output = shell_exec('sudo ufw status');

            // check if output is string value
            if (is_string($output)) {
                // check if ufw running
                if (str_starts_with($output, 'Status: active')) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->errorManager->handleError('error to get ufw status' . $e->getMessage(), 500);
        }

        return false;
    }

    /**
     * Checks if the services list file exists.
     *
     * @return bool The services list file exists, false otherwise.
     */
    public function isServicesListExist(): bool
    {
        // check if services list exist
        if ($this->getServicesJson() != null) {
            return true;
        }

        return false;
    }

    /**
     * Executes a command.
     *
     * @param string $command The command to execute.
     *
     * @return void
     */
    public function executeCommand($command): void
    {
        try {
            shell_exec($command);
        } catch (\Exception $e) {
            $this->errorManager->handleError('error to executed command: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Initiates an emergency shutdown.
     *
     * @return void
     */
    public function emergencyShutdown(): void
    {
        $this->logManager->log('action-runner', $this->authManager->getUsername() . ' initiated emergency-shutdown');
        $this->executeCommand('sudo poweroff');
    }

    /**
     * Gets the services list from the services.json file.
     *
     * @return array<object> The services list.
     */
    public function getServicesJson(): ?array
    {
        return $this->jsonUtil->getJson(__DIR__ . '/../../config/becwork/services.json');
    }

    /**
     * Get a list of required applications that are not installed.
     *
     * This method reads a JSON file containing a list of required applications
     * and checks if each application is installed. It returns an array of applications
     * that are not found on the system.
     *
     * @return array<string> List of applications that are not installed.
     */
    public function getNotInstalledRequirements(): array
    {
        $notFoundApps = [];

        // get list of required apps
        $appList = $this->jsonUtil->getJson(__DIR__ . '/../../config/becwork/package-requirements.json');

        // check if app is installed
        foreach ($appList as $app) {
            if (!$this->isServiceInstalled($app)) {
                array_push($notFoundApps, $app);
            }
        }

        // return not found requirements
        return $notFoundApps;
    }
}
