<?php

namespace roaresearch\yii2\oauth2server\exceptions;

class HttpTokenException extends \yii\web\HttpException
{
    /**
     * Constructor.
     * @param integer $status HTTP status code, such as 404, 500, etc.
     * @param string $message error message
     * @param string $errorUri error uri
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct(
        int $status,
        $message = null,
        public ?string $errorUri = null,
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($status, $message, $code, $previous);
    }
}
