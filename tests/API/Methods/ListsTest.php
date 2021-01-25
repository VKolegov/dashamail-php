<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 25/01/2021 04:07
 */

namespace VKolegov\DashaMail\Tests\API\Methods;

use PHPUnit\Framework\TestCase;
use VKolegov\DashaMail\API\EntryPoint;

class ListsTest extends TestCase
{
    /**
     * @var \VKolegov\DashaMail\API\Methods\Lists
     */
    private $listsApi;

    /**
     * @var string name of test address list
     */
    private $testListName;

    /**
     * @var int id of test address list
     */
    private $testListId;

    protected function setUp()
    {
        parent::setUp();
        $apiEntryPoint = new EntryPoint(
            getenv('DASHAMAIL_USERNAME'),
            getenv('DASHAMAIL_PASSWORD')
        );

        $this->listsApi = $apiEntryPoint->Lists();

        $this->testListName = getenv('DASHAMAIL_TEST_LIST');
    }

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->testListId > 0) {
            $this->listsApi->delete($this->testListId);
        }
    }

    public function testAdd()
    {
        $this->testListId = $this->listsApi->add(
            $this->testListName
        );

        // Expecting newly created address base ID
        $this->assertTrue(is_int($this->testListId));
    }

    public function testAddUnexpectedParams()
    {
        $this->testListId = $this->listsApi->add(
            $this->testListName,
            [
                'test' => true,
                'blabla' => 'albalb'
            ]
        );

        // Expecting newly created address list ID
        $this->assertTrue(is_int($this->testListId));
    }

    public function testAddRealParams()
    {
        $this->testListId = $this->listsApi->add(
            $this->testListName,
            [
                'company' => 'Company Name',
                'country' => 'Россия',
                'abuse_name' => 'Spam Complaint Handler',
                'phone' => '88005553535',
                'city' => 'Санкт-Петербург'
            ]
        );

        // Expecting newly created address list ID
        $this->assertTrue(is_int($this->testListId));
    }

    public function testDeleteEmptyList()
    {
        $this->testAdd();

        $count = $this->listsApi->delete($this->testListId);

        $this->assertTrue(is_int($count));

        $this->testListId = null;
    }
}
