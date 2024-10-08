<?php

namespace App\Tests\Controller\Admin\Auth;

use App\Entity\User;
use App\Manager\AuthManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegisterTest
 *
 * Register component test
 *
 * @package App\Tests\Admin\Auth
 */
class RegisterTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->removeFakeData();
        parent::tearDown();
    }

    /**
     * Remove fake user data after each test
     *
     * @return void
     */
    private function removeFakeData(): void
    {
        // get entity manager
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        // get user repository
        $userRepository = $entityManager->getRepository(User::class);

        // get fake user
        $fakeUser = $userRepository->findOneBy(['username' => 'testing_username']);

        // check if user exist
        if ($fakeUser) {
            $id = $fakeUser->getId();

            $entityManager->remove($fakeUser);
            $entityManager->flush();

            // reset auto-increment values for the users table
            $connection = $entityManager->getConnection();
            $connection->executeStatement("ALTER TABLE users AUTO_INCREMENT = " . ($id - 1));
        }
    }

    /**
     * Test if the register page is loaded when registration is allowed
     *
     * @return void
     */
    public function testRegisterAllowedLoaded(): void
    {
        // mock auth manager
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isRegisterPageAllowed')->willReturn(true);
        $this->client->getContainer()->set(AuthManager::class, $authManagerMock);

        // make get request to account settings admin component
        $this->client->request('GET', '/register');

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | Login');
        $this->assertSelectorTextContains('.form-title', 'Register admin account');
        $this->assertSelectorExists('form[name="register_form"]');
        $this->assertSelectorExists('input[name="register_form[username]"]');
        $this->assertSelectorExists('input[name="register_form[password]"]');
        $this->assertSelectorExists('input[name="register_form[re-password]"]');
        $this->assertSelectorExists('button:contains("Register")');
    }

    /**
     * Test if the register page redirects when registration is not allowed
     *
     * @return void
     */
    public function testRegisterNonAllowedLoaded(): void
    {
        // mock auth manager
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isRegisterPageAllowed')->willReturn(false);
        $this->client->getContainer()->set(AuthManager::class, $authManagerMock);

        // make get request to account settings admin component
        $this->client->request('GET', '/register');

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /**
     * Test if the register form handles empty submission correctly
     *
     * @return void
     */
    public function testRegisterEmptySubmit(): void
    {
        // mock auth manager
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isRegisterPageAllowed')->willReturn(true);
        $this->client->getContainer()->set(AuthManager::class, $authManagerMock);

        // build post request
        $this->client->request('POST', '/register', [
            'register_form' => [
                'username' => '',
                'password' => '',
                're-password' => ''
            ],
        ]);

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('li:contains("Please enter a username")', 'Please enter a username');
        $this->assertSelectorTextContains('li:contains("Please enter a password")', 'Please enter a password');
        $this->assertSelectorTextContains('li:contains("Please enter a password again")', 'Please enter a password again');
    }

    /**
     * Test if the register form handles passwords that do not match correctly
     *
     * @return void
     */
    public function testRegisterNotMatchPasswordsSubmit(): void
    {
        // mock auth manager
        $authManagerMock = $this->createMock(AuthManager::class);
        $authManagerMock->method('isRegisterPageAllowed')->willReturn(true);
        $this->client->getContainer()->set(AuthManager::class, $authManagerMock);

        // build post request
        $this->client->request('POST', '/register', [
            'register_form' => [
                'username' => 'testing_username',
                'password' => 'testing_password_1',
                're-password' => 'testing_password_2'
            ],
        ]);

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('body', 'Your passwords dont match');
    }
}
