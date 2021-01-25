<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 25/01/2021 03:31
 */

namespace VKolegov\DashaMail\API\Methods;


use VKolegov\DashaMail\DashaMailConnection;

/**
 * Class Lists
 * @package VKolegov\DashaMail\API\Methods
 */
class Lists
{
    /**
     * @var DashaMailConnection
     */
    private $connection;

    /**
     * Lists constructor.
     * @param DashaMailConnection $connection
     */
    public function __construct(&$connection)
    {
        $this->connection = $connection;
    }
}
