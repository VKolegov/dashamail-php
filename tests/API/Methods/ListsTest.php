<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 25/01/2021 04:07
 */

namespace VKolegov\DashaMail\Tests\API\Methods;

use Brush\Accounts\Developer;
use Brush\Pastes\Draft;
use Faker\Factory;
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

    public function testGet()
    {
        $lists = $this->listsApi->get();

        $this->assertTrue(is_array($lists));
    }

    public function testAdd()
    {
        $this->testListId = $this->listsApi->add(
            $this->testListName
        );

        // Expecting newly created address list ID
        $this->assertTrue(is_int($this->testListId));
    }

    public function testGetSpecificList()
    {
        $this->testAdd();

        $list = $this->listsApi->get($this->testListId);

        $this->assertTrue(is_array($list));

        $this->assertArrayHasKey('id', $list);
        $this->assertArrayHasKey('name', $list);
        $this->assertEquals($this->testListName, $list['name']);
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

    public function testUpdateListName()
    {
        $newName = 'PHPUNIT TEST UPDATE NAME';
        $this->testAdd();

        $success = $this->listsApi->update(
            $this->testListId,
            ['name' => $newName]
        );

        $this->assertTrue($success);

        // Requesting and comparing if new name was actually set
        $updatedList = $this->listsApi->get(
            $this->testListId
        );

        $this->assertEquals($newName, $updatedList['name']);
    }

    public function testDeleteEmptyList()
    {
        $this->testAdd();

        $count = $this->listsApi->delete($this->testListId);

        $this->assertTrue(is_int($count));

        $this->testListId = null;
    }

    public function testSuccessfulListUpload()
    {
        $this->testAdd();

        $url = $this->getFileUrl();

        $result = $this->listsApi->upload(
            $this->testListId,
            $url
        );

        $this->assertTrue($result);
    }

    /**
     * @return string URL to download file
     */
    private function getFileUrl()
    {

        $faker = Factory::create();

        // 5 megabytes RAM
        $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');

        for ($i = 0; $i < 50; $i++) {
            fputcsv($csv, [$faker->email], ';');
        }
        rewind($csv);

        $csvText = stream_get_contents($csv);

        $draft = new Draft();
        $draft->setContent($csvText);
        $draft->setTitle('phpunit_dashamail_sdk_test.csv');
        $draft->setExpiry('10M'); // 10 minutes (minimal value)
        $draft->setVisibility(0); // 0=public 1=unlisted 2=private

        // the Developer class manages a developer key
        $developer = new Developer('50s3ST61uoK8kEhol5FcUxPNfqeAs1Q-');

        $paste = $draft->paste($developer);

        $url = $paste->getUrl();

        // extracting code
        preg_match_all('/https:\/\/pastebin\.com\/(.+)/', $url, $regexpMatches);

        $code = $regexpMatches[1][0];

        return 'https://pastebin.com/raw/'. $code;
    }

}
