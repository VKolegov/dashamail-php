<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 25/01/2021 03:31
 */

namespace VKolegov\DashaMail\API\Methods;


use VKolegov\DashaMail\DashaMailConnection;
use VKolegov\DashaMail\Exceptions\DashaMailRequestErrorException;

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
     * Retrieves existing address list
     * @param int|null $listId If provided, data returned for specific list
     * @return array array of lists, or one list if $listId is provided
     * @throws DashaMailRequestErrorException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailConnectionException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailInvalidResponseException
     */
    public function get($listId = null)
    {
        $methodName = 'lists.get';
        $params = [];

        if ($listId !== null) {
            if (!is_int($listId) || $listId <= 0) {
                throw new \InvalidArgumentException("\$listId should be positive integer");
            }
            $params['list_id'] = $listId;
        }

        $responseData = $this->connection->callMethod($methodName, $params);

        if (count($responseData) === 1) {
            return $responseData[0];
        }

        return $responseData;
    }

    /**
     * Creates new address list
     * @param string $name Future address list name
     * @param array $params 'company', 'phone', etc...
     * @return int Address list ID
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


    /**
     * Updated list data
     * @param int $listId
     * @param array $params 'name', 'phone', 'company', etc...
     * @return bool Was operation successful?
     * @throws DashaMailRequestErrorException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailConnectionException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailInvalidResponseException
     * @see https://dashamail.ru/api_details/?method=lists.update
     */
    public function update($listId, $params = [])
    {
        $methodName = 'lists.update';

        if (!is_int($listId) || $listId <= 0) {
            throw new \InvalidArgumentException("\$listId should be positive integer");
        }

        $params = array_merge(
            [
                'list_id' => $listId
            ], $params
        );

        return $this->connection->callMethod($methodName, $params, 'POST');
    }

    /**
     * Removes address list
     * @param int $listId Address list ID
     * @return int number of subscribers removed
     * @throws DashaMailRequestErrorException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailConnectionException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailInvalidResponseException
     */
    public function delete($listId)
    {
        $methodName = 'lists.delete';

        if (!is_int($listId) || $listId <= 0) {
            throw new \InvalidArgumentException("\$listId should be positive integer");
        }

        $params = [
            'list_id' => $listId
        ];

        $responseData = $this->connection->callMethod($methodName, $params, 'POST');

        if ($responseData['deleted_members'] === 'empty') {
            return 0;
        }

        return $responseData['deleted_members'];
    }

    /**
     * Imports subscribers from file
     * accepted formats: ("xls","csv","xlsx","xml","ods","slk","gnumeric")
     *
     * WARNING: CAN TAKE A LONG TIME
     * CONSIDER ADJUSTING PHP 'max_execution_time' setting while making this request
     *
     * @param int $listId Address list ID
     * @param string $fileUrl File URL
     * @param string $fileType accepted file formats: ("xls","csv","xlsx","xml","ods","slk","gnumeric")
     * @param int $emailColumn number of column containing email (starting from 0)
     * @param array $params
     * @return true
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailConnectionException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailInvalidResponseException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailRequestErrorException
     * @see https://dashamail.ru/api_details/?method=lists.upload
     */
    public function upload($listId, $fileUrl, $fileType = 'csv', $emailColumn = 0, $params = [])
    {
        $methodName = 'lists.upload';

        if (!is_int($listId) || $listId <= 0) {
            throw new \InvalidArgumentException("\$listId should be positive integer");
        }

        if (!is_string($fileUrl) || strlen($fileUrl) < 1) {
            throw new \InvalidArgumentException("\$fileUrl should be non-empty string");
        }

        $params = array_merge(
            [
                'list_id' => $listId,
                'file' => $fileUrl, // according to real world practice
                'url' => $fileUrl, // according to documentation (29.01.2021)
                'email' => $emailColumn,
                'type' => $fileType,
            ], $params
        );

        // TODO: expect codes 26, 27, 32
        // see https://dashamail.ru/codes/

        $result = $this->connection->callMethod($methodName, $params, 'POST');

        if ($result['type'] === 'success') {
            return true;
        }

        throw new DashaMailRequestErrorException(
            $result['text'], 2, null, $params
        );
    }

    /**
     * Adds multiple subscribers to list in one hgo
     * @param int $listId Address list ID
     * @param array $subscribers Array of
     * @param false $update should existing entries be updated
     * @param false $noCheck should emails be checked for validity
     * @return array
     * @throws DashaMailRequestErrorException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailConnectionException
     * @throws \VKolegov\DashaMail\Exceptions\DashaMailInvalidResponseException
     * @see https://dashamail.ru/api_details/?method=lists.add_member_batch
     */
    public function addSubscribers($listId, $subscribers, $update = false, $noCheck = false)
    {
        $methodName = 'lists.add_member_batch';

        if (!is_int($listId) || $listId <= 0) {
            throw new \InvalidArgumentException("\$listId should be positive integer");
        }

        if (!is_array($subscribers) || count($subscribers) < 1) {
            throw new \InvalidArgumentException("\$subscribers should be non-empty array");
        }

        $params = [
            'list_id' => $listId,
            'update' => $update,
            'no_check' => $noCheck
        ];

        $batch = ""; // string of members
        foreach ($subscribers as $subscriber) {

            if (!is_array($subscriber) && !is_string($subscriber)) {
                throw new \InvalidArgumentException(
                    "\$subscribers should be multi-dimensional array or string"
                );
            }

            if (is_array($subscriber)) {
                if (count($subscriber) > 11) {
                    throw new \InvalidArgumentException(
                        "\$subscribers elements can't be more than 11 elements"
                    );
                }
                $batch .= implode(',', $subscriber);
            } else {
                $batch .= $subscriber;
            }

            $batch .= ';';
        }

        $params['batch'] = $batch;

        $result = $this->connection->callMethod($methodName, $params, 'POST');

        // converting member ids to array
        if (strlen($result['member_ids']) > 0) {
            $result['member_ids'] = explode(',', $result['member_ids']);
        } else {
            $result['member_ids'] = [];
        }

        return $result;
    }
}
