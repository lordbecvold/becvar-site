<?php

namespace App\Tests\Admin;

use App\Manager\AuthManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin todo manager component test
 *
 * @package App\Tests\Admin
 */
class TodoManagerTest extends WebTestCase
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
     * @return object
     */
    private function createAuthManagerMock(): object
    {
        $authManagerMock = $this->createMock(AuthManager::class);

        // init fake testing value
        $authManagerMock->method('isUserLogedin')->willReturn(true);

        return $authManagerMock;
    }

    /**
     * Test if the todo manager page loads successfully.
     */
    public function testTodoManager(): void
    {
        // use fake auth manager instance
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to todo manager controller
        $this->client->request('GET', '/admin/todos');

        // test response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK); 
        $this->assertSelectorTextContains('title', 'Admin | todos');
        $this->assertSelectorTextContains('body', 'Completed');
        $this->assertSelectorExists('form[name="new_todo_form"]');
        $this->assertSelectorExists('textarea[name="new_todo_form[text]"]');
        $this->assertSelectorExists('button:contains("Add")');
    }

    /**
     * Test if the completed todo manager page loads successfully.
     */
    public function testTodoManagerCompleted(): void
    {
        // use fake auth manager instance
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make post request to todo manager controller
        $this->client->request('GET', '/admin/todos/completed');

        // test response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK); 
        $this->assertSelectorTextContains('title', 'Admin | todos');
        $this->assertSelectorTextContains('body', 'Uncompleted');
        $this->assertSelectorNotExists('form[name="new_todo_form"]');
        $this->assertSelectorNotExists('textarea[name="new_todo_form[text]"]');
        $this->assertSelectorNotExists('button:contains("Add")');
    }
}
