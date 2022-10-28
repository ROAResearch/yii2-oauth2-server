<?php

namespace roaresearch\yii2\oauth2server;

use OAuth2\{
    ClientAssertionType\ClientAssertionTypeInterface,
    RequestInterface,
    ResponseInterface,
    ScopeInterface,
    Server as BaseServer,
    TokenType\TokenTypeInterface
};

class Server extends BaseServer
{
    public function __construct(
        protected Module $module,
        $storage = [],
        array $config = [],
        array $grantTypes = [],
        array $responseTypes = [],
        TokenTypeInterface $tokenType = null,
        ScopeInterface $scopeUtil = null,
        ClientAssertionTypeInterface $clientAssertionType = null
    ) {
        parent::__construct(
            $storage,
            $config,
            $grantTypes,
            $responseTypes,
            $tokenType,
            $scopeUtil,
            $clientAssertionType
        );
    }

    public function createAccessToken(
        $clientId,
        $userId,
        $scope = null,
        $includeRefreshToken = true
    ) {
        return $this->getAccessTokenResponseType()->createAccessToken(
            $clientId,
            $userId,
            $scope,
            $includeRefreshToken
        );
    }

    /**
     * @inheritdoc
     */
    public function verifyResourceRequest(
        RequestInterface $request = null,
        ResponseInterface $response = null,
        $scope = null
    ) {
        parent::verifyResourceRequest(
            $request ?: $this->module->getRequest(),
            $response ?: $this->module->getResponse(),
            $scope
        );
    }

    /**
     * @inheritdoc
     */
    public function handleTokenRequest(
        RequestInterface $request = null,
        ResponseInterface $response = null
    ) {
        return parent::handleTokenRequest(
            $request ?: $this->module->getRequest(),
            $response ?: $this->module->getResponse()
        );
    }
}
