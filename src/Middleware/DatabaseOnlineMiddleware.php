<?php

namespace App\Middleware;

use App\Manager\ErrorManager;
use \Doctrine\DBAL\Connection as Connection;

/*
    This middleware used to check the availability of the database
*/

class DatabaseOnlineMiddleware
{
    private $errorManager;
    private $doctrineConnection;

    public function __construct(
        ErrorManager $errorManager,
        Connection $doctrineConnection
    ) {
        $this->errorManager = $errorManager;
        $this->doctrineConnection = $doctrineConnection;
    }

    public function onKernelRequest(): void
    {
        try {
            // select for connection try
            $this->doctrineConnection->executeQuery("SELECT 1");
        } catch (\Exception $e) {

            // return error if not connected
            $this->errorManager->handleError('database connection error: '.$e->getMessage(), 500);
        }
    }
}
