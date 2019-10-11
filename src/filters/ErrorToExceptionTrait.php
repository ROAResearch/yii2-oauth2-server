<?php

namespace roaresearch\yii2\oauth2server\filters;

use roaresearch\yii2\oauth2server\{exceptions\HttpTokenException, Module};
use Yii;
use yii\web\HttpException;

/**
 * Trait to be applied to `\yii\base\Filter` classes which initialize the
 * OAuth2 Server and handles its responses.
 */
trait ErrorToExceptionTrait
{
    /**
     * @var string the unique id for the oauth2 module
     */
    public $oauth2Module = 'oauth2';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {

        if (parent::beforeAction($action)) {
            if (is_string($this->oauth2Module)) {
                $this->oauth2Module = Yii::$app->getModule(
                    $this->oauth2Module
                );
            }
            $this->oauth2Module->initOauth2Server();

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterAction($event, $result)
    {
        $this->ensureSuccessResponse();

        return $result;
    }

    /**
     * Ensures that the OAuth2 Server returned a success response, otherwise
     * throws an `HttpTokenException`
     * @throws HttpTokenException
     */
    protected function ensureSuccessResponse()
    {
        $response = $this->oauth2Module->getResponse();
        if($response === null
            || $response->isInformational()
            || $response->isSuccessful()
            || $response->isRedirection()
        ) {
            return;
        }

        throw new HttpTokenException(
            $response->getStatusCode(),
            $this->getErrorMessage($response),
            $response->getParameter('error_uri')
        );
    }

    /**
     * Returns the translated error message on an unsuccessful response.
     *
     * @param \OAuth2\Response $response
     * @return string
     */
    protected function getErrorMessage(\OAuth2\Response $response): string
    {
        return Module::t(
                'oauth2server',
                $response->getParameter('error_description')
            )
            ?: Module::t(
                'oauth2server',
                'An internal server error occurred'
            );
    }
}
