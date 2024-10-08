<?php

namespace App\Tests\Event\Subscriber;

use App\Manager\LogManager;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use App\Event\Subscriber\ExceptionEventSubscriber;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Class ExceptionEventSubscriberTest
 *
 * Test the exception event subscriber
 *
 * @package App\Tests\Event\Subscriber
 */
class ExceptionEventSubscriberTest extends TestCase
{
    private LogManager & MockObject $logManager;
    private LoggerInterface & MockObject $logger;
    private ExceptionEventSubscriber $subscriber;

    protected function setUp(): void
    {
        // mock dependencies
        $this->logManager = $this->createMock(LogManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // create instance of ExceptionEventSubscriber
        $this->subscriber = new ExceptionEventSubscriber($this->logManager, $this->logger);
    }

    /**
     * Test if the error message can be logged
     *
     * @return void
     */
    public function testCanBeEventLogged(): void
    {
        // test error message without blocked pattern
        $this->assertTrue($this->subscriber->canBeEventLogged('normal error message'));

        // test error message with blocked pattern
        $this->assertFalse($this->subscriber->canBeEventLogged('log-error: Something went wrong'));
    }

    /**
     * Test if message log is blocked
     *
     * @return void
     */
    public function testExceptionDoesNotLogBlockedError(): void
    {
        $exception = new \Exception('Unknown database error');
        $trace = [['function' => 'handleError']];
        $reflector = new \ReflectionClass($exception);
        $property = $reflector->getProperty('trace');
        $property->setAccessible(true);
        $property->setValue($exception, $trace);

        // create a new exception event
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        /** @var Request $request */
        $request = $this->createMock(Request::class);
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        // check if the log manager logs the exception
        $this->logManager->expects($this->never())->method('log');

        // check if the logger logs the error message
        $this->logger->expects($this->once())->method('error')->with('Unknown database error');

        // handle the exception event
        $this->subscriber->onKernelException($event);
    }
}
