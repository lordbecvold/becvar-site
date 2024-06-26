<?php

namespace App\Tests\Controller\Admin;

use App\Manager\AuthManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DashboardTest
 *
 * Admin dashboard component test
 *
 * @package App\Tests\Admin
 */
class DashboardTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    /**
     * Create a mock object for AuthManager.
     *
     * @return object The mock object
     */
    private function createAuthManagerMock(): object
    {
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isUserLogedin')->willReturn(true);
        $authManagerMock->method('getUsername')->willReturn('phpunit-user');
        $authManagerMock->method('getUserRole')->willReturn('Admin');
        $authManagerMock->method('getUserProfilePic')->willReturn('image-code');

        return $authManagerMock;
    }

    /**
     * Test if the admin dashboard page loads successfully.
     *
     * @return void
     */
    public function testAdminDashboard(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to admin dashboard controller
        $this->client->request('GET', '/admin/dashboard');

        // assert
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | dashboard');
        $this->assertSelectorExists('main[class="admin-page"]');
        $this->assertSelectorExists('img[alt="profile_picture"]');
        $this->assertSelectorExists('span[class="role-line"]');
        $this->assertSelectorTextContains('h3', 'phpunit-user');
        $this->assertSelectorTextContains('#wrarning-box', 'Warnings');
        $this->assertSelectorTextContains('body', 'Service status');
        $this->assertSelectorTextContains('body', 'System info');
        $this->assertSelectorTextContains('body', 'Visitors info');
        $this->assertSelectorTextContains('body', 'SERVER: Online');
        $this->assertSelectorTextContains('.card-title', 'Logs');
        $this->assertSelectorTextContains('body', 'Messages');
        $this->assertSelectorTextContains('body', 'Todos');
        $this->assertSelectorTextContains('body', 'Images');
        $this->assertSelectorTextContains('body', 'Pastes');
        $this->assertSelectorTextContains('body', 'Visitors');
        $this->assertSelectorTextContains('body', 'Server uptime');
        $this->assertSelectorTextContains('body', 'CPU usage [CORE/AVG]');
        $this->assertSelectorTextContains('body', 'Memory usage [RAM]');
        $this->assertSelectorTextContains('body', 'Disk space');
        $this->assertSelectorExists('a[class="logout-link"]');
        $this->assertSelectorExists('span[class="menu-text"]');
        $this->assertSelectorExists('div[class="sidebar"]');
    }

    /**
     * Test the admin emergency shutdown confirmation page.
     *
     * @return void
     */
    public function testAdminEmergencyShutdownConfirmation(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to admin dashboard controller
        $this->client->request('GET', '/admin/dashboard/emergency/shutdown');

        // assert
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | confirmation');
        $this->assertSelectorExists('main[class="admin-page"]');
        $this->assertSelectorTextContains('.form-title', 'Confirmation');
        $this->assertSelectorTextContains('.form-sub-title', 'please repeat the verification code');
        $this->assertSelectorExists('input[name="confirmCode"]');
        $this->assertSelectorExists('input[name="shutdownCode"]');
        $this->assertSelectorExists('input[value="Shutdown"]');
    }
}
