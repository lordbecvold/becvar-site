<?php

namespace App\Tests\Admin;

use App\Service\Manager\AuthManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AccountSettingsTest
 * 
 * Admin account settings test
 *
 * @package App\Tests\Admin
 */
class AccountSettingsTest extends WebTestCase
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
     * Test if the account settings table page is loaded successfully.
     */
    public function testAccountSettingsTable(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make get request to account settings admin component
        $this->client->request('GET', '/admin/account/settings');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | settings');
        $this->assertSelectorTextContains('h2', 'Account settings');
        $this->assertSelectorTextContains('body', 'profile-pic');
        $this->assertSelectorTextContains('body', 'username');
        $this->assertSelectorTextContains('body', 'password');
    }

    /**
     * Test if the account settings table page for changing the profile picture is loaded successfully.
     */
    public function testAccountSettingsTableChangePicForm(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make get request to account settings admin component
        $this->client->request('GET', '/admin/account/settings/pic');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | settings');
        $this->assertSelectorTextContains('.form-title', 'Change profile image');
        $this->assertSelectorTextContains('button', 'Upload Image');
    }

    /**
     * Test if the account settings table page for changing the username is loaded successfully.
     */
    public function testAccountSettingsTableChangeUsernameForm(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make get request to account settings admin component
        $this->client->request('GET', '/admin/account/settings/username');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | settings');
        $this->assertSelectorTextContains('.form-title', 'Change username');
        $this->assertSelectorTextContains('button', 'Change username');
    }

    /**
     * Test if the account settings table page handles an empty username change form submission correctly.
     */
    public function testAccountSettingsTableChangeUsernameEmptyForm(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // build post request
        $this->client->request('POST', '/admin/account/settings/username', [
            'username_change_form' => [
                'username' => ''
            ],
        ]);

        $this->assertSelectorTextContains('.form-title', 'Change username');
        $this->assertSelectorTextContains('button', 'Change username');
        $this->assertSelectorTextContains('li:contains("Please enter a username")', 'Please enter a username');
    }

    /**
     * Test if the account settings table page handles a short username change form submission correctly.
     */
    public function testAccountSettingsTableChangeUsernameShortForm(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // build post request
        $this->client->request('POST', '/admin/account/settings/username', [
            'username_change_form' => [
                'username' => 'a'
            ],
        ]);

        $this->assertSelectorTextContains('.form-title', 'Change username');
        $this->assertSelectorTextContains('button', 'Change username');
        $this->assertSelectorTextContains('li:contains("Your username should be at least 4 characters")', 'Your username should be at least 4 characters');
    }

    /**
     * Test if the account settings table page for changing the password is loaded successfully.
     */
    public function testAccountSettingsTableChangePasswordForm(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // make get request to account settings admin component
        $this->client->request('GET', '/admin/account/settings/password');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('title', 'Admin | settings');
        $this->assertSelectorTextContains('.form-title', 'Change password');
        $this->assertSelectorExists('form[name="password_change_form"]');
        $this->assertSelectorExists('input[name="password_change_form[password]"]');
        $this->assertSelectorExists('input[name="password_change_form[repassword]"]');
        $this->assertSelectorExists('button:contains("Change password")');
    }

    /**
     * Test if the account settings table page handles a password change form submission with non-matching passwords correctly.
     */
    public function testAccountSettingsTableChangePasswordNotMatchForm(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // build post request
        $this->client->request('POST', '/admin/account/settings/password', [
            'password_change_form' => [
                'password' => 'testing_password_1',
                'repassword' => 'testing_password_2'
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('body', 'Your passwords is not match!');
    }

    /**
     * Test if the account settings table page handles an empty password change form submission correctly.
     */
    public function testAccountSettingsTableChangePasswordEmptyForm(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // build post request
        $this->client->request('POST', '/admin/account/settings/password', [
            'password_change_form' => [
                'password' => '',
                'repassword' => ''
            ],
        ]);

        $this->assertSelectorTextContains('.form-title', 'Change password');
        $this->assertSelectorTextContains('button', 'Change password');
        $this->assertSelectorTextContains('li:contains("Please enter a password")', 'Please enter a password');
        $this->assertSelectorTextContains('li:contains("Please enter a repassword")', 'Please enter a repassword');
    }

    /**
     * Test if the account settings table page handles a short password change form submission correctly.
     */
    public function testAccountSettingsTableChangePasswordShortForm(): void
    {
        $this->client->getContainer()->set(AuthManager::class, $this->createAuthManagerMock());

        // build post request
        $this->client->request('POST', '/admin/account/settings/password', [
            'password_change_form' => [
                'password' => 'a',
                'repassword' => 'a'
            ],
        ]);

        $this->assertSelectorTextContains('.form-title', 'Change password');
        $this->assertSelectorTextContains('button', 'Change password');
        $this->assertSelectorTextContains('li:contains("Your password should be at least 8 characters")', 'Your password should be at least 8 characters');
        $this->assertSelectorTextContains('li:contains("Your password should be at least 8 characters")', 'Your password should be at least 8 characters');
    }
}
