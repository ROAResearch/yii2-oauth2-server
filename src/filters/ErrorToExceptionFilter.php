<?php

namespace roaresearch\yii2\oauth2server\filters;

/**
 * Filter to initialize the OAuth2Server and handle its responses.
 */
class ErrorToExceptionFilter extends \yii\base\ActionFilter
{
    use ErrorToExceptionTrait;
}
