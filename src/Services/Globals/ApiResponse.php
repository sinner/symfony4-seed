<?php

namespace App\Services\Globals;

use JMS\Serializer\Annotation as JMS;

/**
 * Base response for all api services.
 * Class ApiResponse
 * @package EN\ApiResponse\ApiBundle\Response
 */
class ApiResponse
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"api_response"})
     */
    protected $code = 200;
    
    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"api_response"})
     */
    protected $isSuccess = true;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"api_response"})
     */
    private $message;

    /**
     * @var mixed
     * @JMS\Groups({"api_response"})
     */
    private $data;

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return ApiResponse
     */
    public function setCode(int $code): ApiResponse
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Sets the status message.
     * @param string $message
     * @return ApiResponse
     */
    public function setMessage(string $message): ApiResponse
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Sets the isSuccess state..
     *
     * @param bool $isSuccess
     *
     * @return ApiResponse
     */
    public function setIsSuccess(bool $isSuccess): ApiResponse
    {
        $this->isSuccess = $isSuccess;
        return $this;
    }

    /**
     * Sets the status data.
     *
     * @param $data
     * @return ApiResponse
     */
    public function setData($data): ApiResponse
    {
        $this->data = $data;
        return $this;
    }
}
