<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Exceptions thrown specifically thrown in api logic.
 *
 * Class ApiException
 * @package App\Exceptions
 */
class ApiException extends \Exception
{

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var int
     */
    protected $statusCode;
    
    /**
     * Gets data on the exception.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets "extra" data on the exception.
     *
     * @param mixed $data
     *
     * @return ApiException
     */
    public function setData($data): ApiException
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return ApiException
     */
    public function setStatusCode(int $statusCode): ApiException
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param $message
     * @return ApiException
     */
    public function setMessage($message): ApiException
    {
        $this->message = $message;
        return $this;
    }

}
