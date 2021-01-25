<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 24/01/2021 22:09
 */

namespace VKolegov\DashaMail\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VKolegov\DashaMail\DashaMailConnection;
use VKolegov\DashaMail\Exceptions\DashaMailRequestErrorException;

class DashaMailAPIConnectionTest extends TestCase
{

    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;

    protected function setUp()
    {
        parent::setUp();
        $this->username = getenv('DASHAMAIL_USERNAME');
        $this->password = getenv('DASHAMAIL_PASSWORD');
    }

    public function testConstructorWrongCredentials()
    {
        $this->setExpectedException('InvalidArgumentException');
        new DashaMailConnection(null, null);
        new DashaMailConnection('', null);
        new DashaMailConnection(null, '');
        new DashaMailConnection('', '');
        new DashaMailConnection(111, '');
    }

    public function testConstructorCorrectCredentials()
    {
        $connection = new DashaMailConnection($this->username, $this->password);
        $this->assertTrue($connection instanceof DashaMailConnection);
    }

    public function testWrongCredentials()
    {
        $connection = new DashaMailConnection($this->username . 'blabla', $this->password);

        $this->setExpectedException('VKolegov\DashaMail\Exceptions\DashaMailRequestErrorException', '', 2);

        $connection->callMethod('account.get_balance');
    }

    public function testWrongFormat()
    {
        $connection = new DashaMailConnection($this->username, $this->password);

        $this->setExpectedException('InvalidArgumentException');

        $connection->callMethodRaw('account.get_balance', [], 'blablabla');
    }

    public function testPerformSuccessfulGetRequest()
    {
        $connection = new DashaMailConnection($this->username, $this->password);

        $r = $connection->callMethod('account.get_balance');

        $this->assertArrayHasKey('limit_members', $r);
        $this->assertArrayHasKey('limit_emails', $r);
        $this->assertArrayHasKey('members', $r);
    }
}
