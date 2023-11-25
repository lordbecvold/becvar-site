<?php

namespace App\Tests\Public;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test cases for the Image Uploader component.
 *
 * @package App\Tests\Public
 */
class ImageUploaderTest extends WebTestCase
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
     * Test accessing the Image Uploader page.
     *
     * This test checks if the page loads successfully and if the expected form elements are present.
     *
     * @return void
     */
    public function testImageUploadPage()
    {
        // make get request
        $this->client->request('GET', '/image/uploader');

        // test response
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('p[class=form-title]');
        $this->assertSelectorExists('input[name=userfile]');
        $this->assertSelectorExists('input[name=submitUpload]');
    }
}
