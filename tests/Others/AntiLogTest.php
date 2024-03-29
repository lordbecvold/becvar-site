<?php

namespace App\Tests\Others;

use App\Service\Manager\AuthManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AntiLogTest
 * 
 * Test cases for the AntiLog functionality.
 *
 * @package App\Tests\Others
 */
class AntiLogTest extends WebTestCase
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
     * Create a mock instance of the AuthManager.
     *
     * @param bool $logged Whether the user is logged in or not.
     * @return object The mock AuthManager instance.
     */
    private function createAuthManagerMock(bool $logged = true): object
    {
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isUserLogedin')->willReturn($logged);
        $authManagerMock->method('getUsername')->willReturn('testing-user');

        return $authManagerMock;
    }

    /**
     * Test setting AntiLog for an authenticated user.
     */
    public function testAntiLogSet(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to admin init controller
        $this->client->request('GET', '/antilog/5369362536');

        $this->assertResponseStatusCodeSame(302); 
        $this->assertTrue($this->client->getResponse()->isRedirect('/admin/dashboard'));
    }

    /**
     * Test setting AntiLog for a non-authenticated user.
     */
    public function testAntiLogNonAuth(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock(false));

        // make post request to admin init controller
        $this->client->request('GET', '/antilog/5369362536');

        // get response data
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(401);
        $this->assertEquals('error to set anti-log for non authentificated users!', $responseData['message']);
    }
}
