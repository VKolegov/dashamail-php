<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 25/01/2021 03:43
 */

namespace VKolegov\DashaMail\API;


use VKolegov\DashaMail\API\Methods\Lists;
use VKolegov\DashaMail\DashaMailConnection;

class EntryPoint
{
    /**
     * @var DashaMailConnection
     */
    private $connection;

    /**
     * @var Lists
     */
    private $lists = null;

    public function __construct($username, $password)
    {
        $this->connection = new DashaMailConnection($username, $password);
    }

    /**
     * @return Lists
     */
    public function Lists() {
        if (!$this->lists) {
            $this->lists = new Lists($this->connection);
        }
        return $this->lists;
    }
}
