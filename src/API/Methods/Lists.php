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

    /**
     * Creates new address base
     * @param string $name Future address base name
     * @param array|null $params
     * @return int Address Base ID
     * @throws DashaMailRequestErrorException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailConnectionException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailInvalidResponseException
     * @see https://dashamail.ru/api_details/?method=lists.add
     */
    public function add($name, $params = [])
    {
        $methodName = 'lists.add';
        if (!is_string($name) || strlen($name) < 1) {
            throw new \InvalidArgumentException("\$name should be non-empty string");
        }

        $params = array_merge(
            [
                'name' => $name,
            ],
            $params
        );

        $responseData = $this->connection->callMethod($methodName, $params, 'POST');

        return $responseData['list_id'];
    }
}
