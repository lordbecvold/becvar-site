<?php

namespace App\Tests\Controller\Admin;

use App\Manager\AuthManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DatabaseBrowserControllerTest
 *
 * Admin database browser component test
 *
 * @package App\Tests\Admin
 */
class DatabaseBrowserControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    /**
     * Create a mock object for AuthManager
     *
     * @param string $role The role of the user
     *
     * @return object The mock object
     */
    private function createAuthManagerMock(string $role): object
    {
        // create mock auth manager
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isUserLogedin')->willReturn(true);
        $authManagerMock->method('getUserRole')->willReturn($role);

        return $authManagerMock;
    }

    /**
     * Test if the database browser list page loads successfully for an admin
     *
     * @return void
     */
    public function testDatabaseBrowserList(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock('Admin'));

        // make post request to database browser
        $this->client->request('GET', '/admin/database');

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | database');
        $this->assertSelectorTextContains('.page-title', 'Select table');
        $this->assertSelectorExists('a[class="db-browser-select-link"]');
    }

    /**
     * Test if the database browser list page restricts access for non-admin users
     *
     * @return void
     */
    public function testDatabaseBrowserListNonPermissions(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock('User'));

        // make post request to database browser
        $this->client->request('GET', '/admin/database');

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | database');
        $this->assertSelectorTextContains('.page-title', 'Sorry you dont have permission to this page');
        $this->assertSelectorNotExists('a[class="db-browser-select-link"]');
    }

    /**
     * Test if the database browser table viewer page loads successfully for an admin
     *
     * @return void
     */
    public function testDatabaseBrowserTableViewer(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock('Admin'));

        // make post request to database browser
        $this->client->request('GET', '/admin/database/table?table=users&page=1');

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | database');
        $this->assertSelectorNotExists('i[class="fa-arrow-left"]');
    }

    /**
     * Test if the database browser new row adder page loads successfully for an admin
     *
     * @return void
     */
    public function testDatabaseBrowserNewRowAdder(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock('Admin'));

        // make post request to database browser
        $this->client->request('GET', '/admin/database/add?table=users&page=1');

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | database');
        $this->assertSelectorTextContains('.title', 'Add new: users');
    }
}
