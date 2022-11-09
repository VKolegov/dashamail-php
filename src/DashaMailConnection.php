<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 24/01/2021 20:45
 */

namespace VKolegov\DashaMail;

use InvalidArgumentException;
use VKolegov\DashaMail\Exceptions\DashaMailConnectionException;
use VKolegov\DashaMail\Exceptions\DashaMailInvalidResponseException;
use VKolegov\DashaMail\Exceptions\DashaMailRequestErrorException;

class DashaMailConnection
{
    const API_URL = 'https://api.dashamail.com/';

    /**
     * @var string used for authentication
     */
    private $username;
    /**
     * @var string used for authentication
     */
    private $password;

    private $allowedResponseFormats = ['json', 'jsonp', 'xml'];

    /**
     * DashaMailConnection constructor.
     * @param string $username Username used for DashaMail authentication
     * @param string $password Password used for DashaMail authentication
     */
    public function __construct($username, $password)
    {
        if (
            !is_string($username) || !is_string($password)
            || strlen($username) < 1 || strlen($password) < 1
        ) {
            throw new InvalidArgumentException("Wrong auth credentials");
        }

        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Initializes cUrl session and performs request
     * @param array $params
     * @param string $httpMethod
     * @return string
     * @throws DashaMailConnectionException
     */
    private function rawRequest($params, $httpMethod = 'GET')
    {
        $url = self::API_URL;

        if ($httpMethod === 'GET') {
            $url .= '?' . http_build_query($params);
        }

        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_POST => $httpMethod === 'POST' ? 1 : 0,
            CURLOPT_POSTFIELDS => $httpMethod === 'POST' ? $params : null,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 600,
            CURLOPT_USERAGENT => 'DashaMail-PHP-SDK',
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        $curlHandle = curl_init($url);
        curl_setopt_array($curlHandle, $curlOptions);

        $responseResult = curl_exec($curlHandle);


        if ($responseResult === false) {
            $responseCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
            $curlErrorMsg = curl_error($curlHandle);
            throw new DashaMailConnectionException("Failed cURL request: [$httpMethod] : $url | $curlErrorMsg", $responseCode);
        }

        // Retrieve Response Status Code
        $httpStatusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

        if ($httpStatusCode < 200 || $httpStatusCode >= 300) {
            throw new DashaMailConnectionException(
                "Got response code $httpStatusCode when accessing $url",
                $httpStatusCode
            );
        }

        curl_close($curlHandle);

        return $responseResult;
    }

    /**
     * Calls API method and returns RAW response
     * @param string $methodName API method name
     * @param array $params API method params
     * @param string $format Return message format: json/jsonp/xml
     * @param string $httpMethod GET/POST
     * @return string Raw response string
     * @throws DashaMailConnectionException
     */
    public function callMethodRaw($methodName, $params = [], $format = 'json', $httpMethod = 'GET')
    {
        if (!is_string($methodName)) {
            throw new InvalidArgumentException("Method name should be string");
        }

        if (!in_array($format, $this->allowedResponseFormats)) {
            throw new InvalidArgumentException(
                "Unknown format, allowed: " . implode(',', $this->allowedResponseFormats)
            );
        }

        $requestParams = array_merge(
            [
                'method' => $methodName,
                'username' => $this->username,
                'password' => $this->password,
                'format' => $format
            ], $params);

        return $this->rawRequest($requestParams, $httpMethod);
    }

    /**
     * Calls API method, checks for errors and returns response 'data' field value if there aren't any
     * @param string $methodName API method name
     * @param array $params API method params
     * @param string $httpMethod GET/POST
     * @return array|bool response 'data' field value
     * @throws DashaMailConnectionException
     * @throws DashaMailRequestErrorException
     * @throws DashaMailInvalidResponseException
     */
    public function callMethod($methodName, $params = [], $httpMethod = 'GET')
    {
        $responseString = $this->callMethodRaw($methodName, $params, 'json', $httpMethod);

        $decodedResponse = json_decode($responseString, true);

        // Validating response structure
        if (!isset($decodedResponse['response'])) {
            throw new DashaMailInvalidResponseException(
                "Wrong response structure"
            );
        }

        $response = $decodedResponse['response'];

        if (!array_key_exists('msg', $response) || !array_key_exists('data', $response)) {
            throw new DashaMailInvalidResponseException(
                "Wrong response structure"
            );
        }

        // Checking for errors/notices in response
        if ($response['msg']['err_code'] !== 0) {

            if ($response['msg']['type'] === 'error') {
                throw new DashaMailRequestErrorException(
                    "DashaMail reported an error while executing the request to method '$methodName': "
                    . $response['msg']['text'],

                    $response['msg']['err_code'],
                    null,
                    $params
                );
            } else {
                trigger_error($response['msg']['text'], E_USER_NOTICE);
            }
        }

        return $response['data'];
    }

}
