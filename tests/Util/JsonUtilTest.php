<?php

namespace Tests\Unit\Util;

use Twig\Environment;
use App\Util\JsonUtil;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class JsonUtilTest
 *
 * Test cases for JsonUtil class
 *
 * @package Tests\Unit\Util
 */
class JsonUtilTest extends TestCase
{
    private JsonUtil $jsonUtil;
    private ErrorManager $errorManager;

    protected function setUp(): void
    {
        // mock dependencies
        /** @var Environment $twigMock */
        $twigMock = $this->createMock(Environment::class);
        $this->errorManager = new ErrorManager($twigMock);

        // create instance of JsonUtil
        $this->jsonUtil = new JsonUtil($this->errorManager);
    }

    /**
     * Test getJson method with different targets
     *
     * @return void
     */
    public function testGetJsonFromFile(): void
    {
        // test with existing JSON file
        $expectedData = ['key' => 'value'];
        $filePath = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($filePath, json_encode($expectedData));

        // get JSON data from file
        $jsonData = $this->jsonUtil->getJson($filePath);

        // assert the data
        $this->assertEquals($expectedData, $jsonData);

        // clean up the test file
        unlink($filePath);
    }

    /**
     * Test getJson method with different targets
     *
     * @return void
     */
    public function testGetJsonWithInvalidTarget(): void
    {
        // set expect exception
        $this->expectException(HttpException::class);

        // call the method
        $this->jsonUtil->getJson('non_existent_file.json');
    }

    /**
     * Test getJson method with different targets
     *
     * @return void
     */
    public function testGetJsonWithInvalidData(): void
    {
        // test with invalid JSON data
        $invalidJson = '{"key": "value"';
        $filePath = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($filePath, $invalidJson);

        // get JSON data from file
        $jsonData = $this->jsonUtil->getJson($filePath);
        $this->assertEmpty($jsonData);

        // clean up the test file
        unlink($filePath);
    }
}
