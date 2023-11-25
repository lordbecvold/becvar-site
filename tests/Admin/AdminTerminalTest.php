<?php

namespace App\Tests\Admin;

use App\Manager\AuthManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Admin terminal component test
 *
 * @package App\Tests\Admin
 */
class AdminTerminalTest extends WebTestCase
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
        parent::setUp();

        // create client instance
        $this->client = static::createClient();
    }

    /**
     * Create a mock object for AuthManager.
     *
     * @param string $role
     * @return object
     */
    private function createAuthManagerMock(string $role = 'Admin'): object
    {
        $authManagerMock = $this->createMock(AuthManager::class);

        // init fake testing value
        $authManagerMock->method('isUserLogedin')->willReturn(true);

        // check if simulated admin request
        if ($role == 'Admin') {
            $authManagerMock->method('isAdmin')->willReturn(true);
        }

        $authManagerMock->method('getUserRole')->willReturn($role);

        return $authManagerMock;
    }

    /**
     * Test if the user with no permissions is redirected.
     */
    public function testAdminTerminalNoPermissions(): void
    {
        // use fake auth manager instance
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock('User'));

        // make post request to admin init controller
        $this->client->request('GET', '/admin/terminal');

        // test response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK); 
        $this->assertSelectorTextContains('title', 'Admin | terminal');
        $this->assertSelectorTextContains('h2', 'Sorry you dont have permission to this page');
    }

    /**
     * Test if the admin terminal page is accessible.
     */
    public function testAdminTerminal(): void
    {
        // use fake auth manager instance
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to admin init controller
        $this->client->request('GET', '/admin/terminal');

        // test response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK); 
        $this->assertSelectorTextContains('title', 'Admin | terminal');
        $this->assertSelectorTextContains('body', '$');
    }
}