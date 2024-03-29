<?php

namespace App\Tests\Admin;

use App\Service\Manager\AuthManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class LogReaderTest
 * 
 * Admin log reader component test
 *
 * @package App\Tests\Admin
 */
class LogReaderTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser Instance for making requests.
     */
    private $client;

    /**
     * Set up before each test.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    /**
     * Create a mock object for AuthManager.
     *
     * @return object
     */
    private function createAuthManagerMock(): object
    {
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isUserLogedin')->willReturn(true);

        return $authManagerMock;
    }

    /**
     * Test if the log reader page loads successfully.
     */
    public function testLogReaderLoad(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to logs page
        $this->client->request('GET', '/admin/logs?page=1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK); 
        $this->assertSelectorTextContains('title', 'Admin | logs');
        $this->assertSelectorTextContains('body', 'Delete all');
        $this->assertSelectorTextContains('body', 'Readed all');
        $this->assertSelectorTextContains('body', 'Unfiltered');
        $this->assertSelectorTextContains('body', 'Logs reader');
        $this->assertSelectorTextContains('body', 'Basic info');
    }

    /**
     * Test if the log reader delete page loads successfully.
     */
    public function testLogReaderDelete(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to logs page
        $this->client->request('GET', '/admin/logs/delete');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK); 
        $this->assertSelectorTextContains('title', 'Admin | confirmation');
        $this->assertSelectorTextContains('body', 'Are you sure you want to delete logs?');
        $this->assertSelectorTextContains('body', 'Yes');
        $this->assertSelectorTextContains('body', 'No');
    }
}
