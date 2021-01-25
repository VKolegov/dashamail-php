<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 25/01/2021 12:08
 */

namespace VKolegov\DashaMail\Tests\API;

use VKolegov\DashaMail\API\EntryPoint;
use PHPUnit\Framework\TestCase;
use VKolegov\DashaMail\API\Methods\Lists;
use VKolegov\DashaMail\DashaMailConnection;

class EntryPointTest extends TestCase
{
    /**
     * @var EntryPoint
     */
    private $apiEntryPoint;

    protected function setUp()
    {
        parent::setUp();

        $this->apiEntryPoint = new EntryPoint(
            getenv('DASHAMAIL_USERNAME'),
            getenv('DASHAMAIL_PASSWORD')
        );
    }

    public function testLists()
    {
        $lists = $this->apiEntryPoint->Lists();

        $this->assertTrue($lists instanceof Lists);
    }
}
