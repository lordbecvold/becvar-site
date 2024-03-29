<?php

namespace App\Tests\Api;

use App\Service\Manager\AuthManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ChatTest
 * 
 * Admin chat API test
 *
 * @package App\Tests\Api
 */
class ChatTest extends WebTestCase
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
     * @param bool $logged
     * @return object
     */
    private function createAuthManagerMock(bool $logged): object
    {
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isUserLogedin')->willReturn($logged);
        $authManagerMock->method('getUserToken')->willReturn('testing-user-token');

        return $authManagerMock;
    }

    /**
     * Test posting a chat message.
     */
    public function testPostMessage(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock(true));

        // // build post request
        $this->client->request('POST', '/api/chat/save/message', [], [], [], json_encode([
            'message' => 'Testing message: +ěščřžýáíé´=éíáýžřčš12345678ANFJNJNUJBZV',
        ]));

        // get response data
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // test response
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('chat message saved', $responseData['message']);
    }

    /**
     * Test posting an empty chat message.
     */
    public function testPostEmptyMessage(): void
    {
        // use fake auth manager instance
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock(true));

        // make request
        $this->client->request('POST', '/api/chat/save/message', [], [], [], json_encode([]));

        // get response data
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // test response
        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('chat message not saved', $responseData['message']);
    }

    /**
     * Test posting a chat message without authentication.
     */
    public function testPostNonAuthMessage(): void
    {
        // use fake auth manager instance
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock(false));

        // build post request
        $this->client->request('POST', '/api/chat/save/message', [], [], [], json_encode([
            'message' => 'This is non authentificated message!'
        ]));

        // get response data
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(401);
        $this->assertEquals('error to save message: only for authenticated users!', $responseData['message']);
    }

    /**
     * Test getting chat messages.
     */
    public function testGetMessages(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock(true));

        // make request
        $this->client->request('GET', '/api/chat/get/messages');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test getting chat messages without authentication.
     */
    public function testNonAuthGetMessages(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock(false));

        // make request
        $this->client->request('GET', '/api/chat/get/messages');

        // get response data
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(401);
        $this->assertEquals('error to get messages: only for authenticated users!', $responseData['message']);
    }
}
