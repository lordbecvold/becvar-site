<?php

namespace App\Tests\Controller\Public;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HomeControllerTest
 *
 * Test cases for the Home component
 *
 * @package App\Tests\Public
 */
class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    /**
     * Test accessing the Home page
     *
     * @return void
     */
    public function testHomePage(): void
    {
        // make get request
        $this->client->request('GET', '/');

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
