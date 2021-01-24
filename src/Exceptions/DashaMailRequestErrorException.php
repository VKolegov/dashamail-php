<?php
/**
 * Created by vkolegov in PhpStorm
 * Date: 24/01/2021 23:39
 */

namespace VKolegov\DashaMail\Exceptions;

use Exception;

class DashaMailRequestErrorException extends Exception
{
    /**
     * @var array
     */
    private $requestParams;

    /**
     * DashaMailRequestError constructor.
     * @param string $message
     * @param int $code
     * @param null $previous
     * @param array $requestParams
     */
    public function __construct($message = "", $code = 0, $previous = null, $requestParams = [])
    {
        parent::__construct($message, $code, $previous);
        $this->requestParams = $requestParams;
    }

    public function getRequestParams()
    {
        return $this->requestParams;
    }
}
