<?php

namespace roaresearch\yii2\oauth2server;

use OAuth2\{Request, Response};
use Yii;
use yii\{base\InvalidConfigException, helpers\ArrayHelper};

/**
 * @author Angel (Faryshta) Guevara <aguevara@invernaderolabs.com>
 */
trait OAuth2InitTrait
{
    /**
     * @var bool whether the oauth2 server was initialized
     */
    private bool $oauth2Initialized = false;

    /**
     * @var array Model's map
     */
    public array $modelMap = [];

    /**
     * @var array Storage's map
     */
    public array $storageMap = [];

    /**
     * @var array GrantTypes collection
     */
    public array $grantTypes = [];

    /**
     * @var string name of access token parameter
     */
    public string $tokenParamName = 'access_token';

    /**
     * @var int max access lifetime in seconds
     */
    public int $tokenAccessLifetime = 3600 * 24;

    /**
     * @var array Model's map
     */
    protected array $defaultModelMap = [
        'OauthClients' => models\OauthClients::class,
        'OauthAccessTokens' => models\OauthAccessTokens::class,
        'OauthAuthorizationCodes' => models\OauthAuthorizationCodes::class,
        'OauthRefreshTokens' => models\OauthRefreshTokens::class,
        'OauthScopes' => models\OauthScopes::class,
    ];

    /**
     * @var array Storage's map
     */
    protected array $defaultStorageMap = [
        'access_token' => storage\Pdo::class,
        'authorization_code' => storage\Pdo::class,
        'client_credentials' => storage\Pdo::class,
        'client' => storage\Pdo::class,
        'refresh_token' => storage\Pdo::class,
        'user_credentials' => storage\Pdo::class,
        'public_key' => storage\Pdo::class,
        'jwt_bearer' => storage\Pdo::class,
        'scope' => storage\Pdo::class,
    ];

    /**
     * Initializes the oauth2 server and its dependencies.
     */
    public function initOauth2(): void
    {
        if ($this->oauth2Initialized) {
            return;
        }

        $this->oauth2Initialized = true;
        $this->set('server', $this->createOAuth2Server());
        $this->set('request', $this->createOAuth2Request());
        $this->set('response', $this->createOAuth2Response());
    }

    /**
     * Gets Oauth2 Server
     *
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->get('server');
    }

    /**
     * Gets Oauth2 Response
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->get('response');
    }

    /**
     * Gets Oauth2 Request
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->get('request');
    }

    /**
     * @return bool
     */
    public function validateAuthorizeRequest(): bool
    {
        return $this->getServer()->validateAuthorizeRequest(
            $this->getRequest(),
            $this->getResponse(),
        );
    }

    /**
     * @param bool $authorized
     */
    public function handleAuthorizeRequest(
        bool $authorized,
        string|int $user_id
    ): Response {
        $this->getServer()->handleAuthorizeRequest(
            $this->getRequest(),
            $response = $this->getResponse(),
            $authorized,
            $user_id
        );

        return $response;
    }

    /**
     * @return Server the server to handle the requests with default storages,
     *   config and grant types
     */
    protected function createOAuth2Server(): Server
    {
        $this->modelMap = array_merge($this->defaultModelMap, $this->modelMap);
        $this->storageMap = array_merge($this->defaultStorageMap, $this->storageMap);
        $container = Yii::$container;

        foreach ($this->modelMap as $name => $definition) {
            $container->set(models::class . '\\' . $name, $definition);
        }

        $storages = [];
        foreach ($this->storageMap as $name => $definition) {
            $storages[$name] = $container->set($name, $definition)->get($name);
        }

        $grantTypes = [];
        foreach($this->grantTypes as $name => $options) {
            if(!isset($storages[$name])) {
                throw new InvalidConfigException(
                    "Grant type `$name` must have associated storage."
                );
            }

            if (!$class = ArrayHelper::remove($options, 'class')) {
                throw new InvalidConfigException(
                    "Grant type `$name` config must have a 'class' index."
                );
            }

            $grantTypes[$name] = new $class($storages[$name], $options);
        }

        return new Server(
            $this,
            $storages,
            $this->oauth2ServerConfig(),
            $grantTypes,
        );
    }

    /**
     * @return Request default request built from global parameters
     */
    protected function createOAuth2Request(): Request
    {
        return Request::createFromGlobals();
    }

    /**
     * @return Response empty response to be filled by the server
     */
    protected function createOAuth2Response(): Response
    {
        return new Response();
    }

    /**
     * @return array the config passed directly to the oauth2 server
     */
    protected function oauth2ServerConfig(): array
    {
        return [
            'token_param_name' => $this->tokenParamName,
            'access_lifetime' => $this->tokenAccessLifetime,
        ];
    }
}
