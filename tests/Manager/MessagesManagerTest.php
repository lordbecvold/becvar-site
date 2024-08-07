<?php

namespace App\Tests\Manager;

use App\Util\SecurityUtil;
use App\Manager\ErrorManager;
use App\Manager\VisitorManager;
use PHPUnit\Framework\TestCase;
use App\Manager\MessagesManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class MessagesManagerTest
 *
 * Test the messages manager class
 *
 * @package App\Tests\Manager
 */
class MessagesManagerTest extends TestCase
{
    /**
     * Test save message
     *
     * @return void
     */
    public function testSaveMessage(): void
    {
        // mock dependencies
        $securityUtil = $this->createMock(SecurityUtil::class);
        $errorManager = $this->createMock(ErrorManager::class);
        $visitorManager = $this->createMock(VisitorManager::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        // mock EntityManager to expect method calls
        $entityManager->expects($this->once())
            ->method('persist');
        $entityManager->expects($this->once())
            ->method('flush');

        // mock VisitorManager behavior
        $visitorManager->expects($this->once())
            ->method('updateVisitorEmail');

        // mock SecurityUtil to encrypt message
        $securityUtil->expects($this->once())
            ->method('encryptAes')
            ->willReturn('encrypted_message');

        // instantiate MessagesManager with mocked dependencies
        $messagesManager = new MessagesManager(
            $securityUtil,
            $errorManager,
            $visitorManager,
            $entityManager
        );

        // call saveMessage method with test data
        $result = $messagesManager->saveMessage('John Doe', 'john@example.com', 'Hello World', '127.0.0.1', 'visitor_123');

        // assert that the method returns true upon successful save
        $this->assertTrue($result);
    }
}
