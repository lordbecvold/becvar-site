<?php

namespace App\Tests\Controller\Admin;

use App\Manager\AuthManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TodoManagerTest
 *
 * Admin todo manager component test
 *
 * @package App\Tests\Admin
 */
class TodoManagerTest extends WebTestCase
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
     * @return object
     */
    private function createAuthManagerMock(): object
    {
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isUserLogedin')->willReturn(true);

        return $authManagerMock;
    }

    /**
     * Test if the todo manager page loads successfully.
     *
     * @return void
     */
    public function testTodoManager(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to todo manager controller
        $this->client->request('GET', '/admin/todos');

        // assert
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | todos');
        $this->assertSelectorTextContains('body', 'Completed');
        $this->assertSelectorExists('form[name="new_todo_form"]');
        $this->assertSelectorExists('textarea[name="new_todo_form[text]"]');
        $this->assertSelectorExists('button:contains("Add")');
    }

    /**
     * Test if the completed todo manager page loads successfully.
     *
     * @return void
     */
    public function testTodoManagerCompleted(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to todo manager controller
        $this->client->request('GET', '/admin/todos/completed');

        // assert
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | todos');
        $this->assertSelectorTextContains('body', 'Uncompleted');
        $this->assertSelectorNotExists('form[name="new_todo_form"]');
        $this->assertSelectorNotExists('textarea[name="new_todo_form[text]"]');
        $this->assertSelectorNotExists('button:contains("Add")');
    }
}
