<?php

namespace App\Manager;

use App\Entity\Log;
use App\Util\SecurityUtil;
use App\Util\VisitorInfoUtil;
use App\Manager\CookieManager;
use Doctrine\ORM\EntityManagerInterface;

/*
    Log manager provides log functions for save events to database table
*/

class LogManager
{
    private $errorManager;
    private $securityUtil;
    private $entityManager;
    private $cookieManager;
    private $visitorInfoUtil;
    
    public function __construct(
        ErrorManager $errorManager,
        SecurityUtil $securityUtil, 
        CookieManager $cookieManager,
        VisitorInfoUtil $visitorInfoUtil,
        EntityManagerInterface $entityManager
    ) {
        $this->errorManager = $errorManager;
        $this->securityUtil = $securityUtil;
        $this->cookieManager = $cookieManager;
        $this->entityManager = $entityManager;
        $this->visitorInfoUtil = $visitorInfoUtil;
    }

    public function log(string $name, string $value): void 
    {
        // check if logs enabled in config
        if ($this->isLogsEnabled()) {

            // check if antilog is disabled
            if (!$this->isEnabledAntiLog()) {

                // get log level
                $level = $this->getLogLevel();

                // disable database log for level 1 & 2
                if ($name == 'database' && $level < 3) {
                    return;
                }

                // disable paste, image-uploader log for level 1
                if (($name == 'paste' || 'image-uploader') && $level < 2) {
                    return;
                }

                // get current date
                $date = date('d.m.Y H:i:s');

                // get visitor browser agent
                $browser = $this->visitorInfoUtil->getBrowser();

                // get visitor ip address
                $ip_address = $this->visitorInfoUtil->getIP();

                // get visitor id
                $visitor_id = $this->visitorInfoUtil->getVisitorID($ip_address);

                // xss escape inputs
                $name = $this->securityUtil->escapeString($name);
                $value = $this->securityUtil->escapeString($value);
                $browser = $this->securityUtil->escapeString($browser);
                $ip_address = $this->securityUtil->escapeString($ip_address);
                
                // create new log enity
                $LogEntity = new Log();

                // set log entity values
                $LogEntity->setName($name); 
                $LogEntity->setValue($value); 
                $LogEntity->setDate($date); 
                $LogEntity->setIpAddress($ip_address); 
                $LogEntity->setBrowser($browser); 
                $LogEntity->setStatus('unreaded'); 
                $LogEntity->setVisitorId($visitor_id);
                
                // try insert row
                try {
                    $this->entityManager->persist($LogEntity);
                    $this->entityManager->flush();
                } catch (\Exception $e) {
                    $this->errorManager->handleError('log flush error: '.$e->getMessage(), 500);
                }
            }
        }
    }

    public function getLogs(string $status, $username, int $page): ?array
    {
        $repo = $this->entityManager->getRepository(Log::class);
        $per_page = $_ENV['ITEMS_PER_PAGE'];
        
        // calculate offset
        $offset = ($page - 1) * $per_page;
    
        // get logs from database
        try {
            $queryBuilder = $repo->createQueryBuilder('l')
                ->where('l.status = :status')
                ->orderBy('l.id', 'DESC')
                ->setParameter('status', $status)
                ->setFirstResult($offset)  
                ->setMaxResults($per_page);
    
            $logs = $queryBuilder->getQuery()->getResult();
        } catch (\Exception $e) {
            $this->errorManager->handleError('error to get logs: ' . $e->getMessage(), 500);
            $logs = [];
        }
    
        $this->log('database', 'user: ' . $username . ' viewed logs');

        // replace browser with formated value for log reader
        foreach ($logs as $log) {
            $user_agent = $log->getBrowser();
            $formated_browser = $this->visitorInfoUtil->getBrowserShortify($user_agent);
            $log->setBrowser($formated_browser);
        }

        return $logs;
    }

    public function getLogsCount(string $status): int
    {
        $repo = $this->entityManager->getRepository(Log::class);

        try {
            $logs = $repo->findBy(['status' => $status]);   
        } catch (\Exception $e) {
            $this->errorManager->handleError('error to get logs: ' . $e->getMessage(), 500);
            $logs = [];
        } 

        return count($logs);
    }

    public function getLoginLogsCount(): int
    {
        $repo = $this->entityManager->getRepository(Log::class);

        try {
            $logs = $repo->findBy(['name' => 'authenticator']);   
        } catch (\Exception $e) {
            $this->errorManager->handleError('error to get logs: ' . $e->getMessage(), 500);
            $logs = [];
        } 

        return count($logs);
    }

    public function setReaded(): void
    {
        $dql = "UPDATE App\Entity\Log l SET l.status = 'readed'";

        $query = $this->entityManager->createQuery($dql);
        $query->execute();
    }

    public function isLogsEnabled(): bool 
    {
        // check if logs enabled
        if ($_ENV['LOGS_ENABLED'] == 'true') {
            return true;
        } else {
            return false;
        }
    }

    public function setAntiLogCookie(): void
    {
        $this->cookieManager->set('anti-log-cookie', $_ENV['ANTI_LOG_COOKIE'], time() + (60*60*24*7*365));
    }

    public function unsetAntiLogCookie(): void
    {
        $this->cookieManager->unset('anti-log-cookie');
    }

    public function getLogLevel(): int
    {
        return $_ENV['LOG_LEVEL'];
    }

    public function isEnabledAntiLog(): bool
    {
        // check if cookie set
        if (isset($_COOKIE['anti-log-cookie'])) {

            // get cookie token
            $token = $this->cookieManager->get('anti-log-cookie');

            // check if token is valid
            if ($token == $_ENV['ANTI_LOG_COOKIE']) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } 
}