<?php

namespace roaresearch\yii2\oauth2server;

use OAuth2\{Request, Response};
use ReflectionClass;
use Yii;
use yii\{
    base\BootstrapInterface,
    base\InvalidConfigException,
    i18n\PhpMessageSource,
    web\UrlRule,
};

/**
 * For example,
 *
 * ```php
 * 'oauth2' => [
 *     'class' => 'roaresearch\yii2\oauth2server\Module',
 *     'tokenParamName' => 'accessToken',
 *     'tokenAccessLifetime' => 3600 * 24,
 *     'storageMap' => [
 *         'user_credentials' => 'common\models\User',
 *     ],
 *     'grantTypes' => [
 *         'user_credentials' => [
 *             'class' => 'OAuth2\GrantType\UserCredentials',
 *         ],
 *         'refresh_token' => [
 *             'class' => 'OAuth2\GrantType\RefreshToken',
 *             'always_issue_new_refresh_token' => true
 *         ]
 *     ]
 * ]
 * ```
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var bool whether the oauth2 server was initialized
     */
    private bool $serverInitialized = false;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = controllers::class;

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
    public string $tokenParamName;

    /**
     * @var int max access lifetime in seconds
     */
    public int $tokenAccessLifetime;

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
     * @inheritdoc
     */
    public function urlRules(): array
    {
        return [
            [
                'class' => UrlRule::class,
                'pattern' => $this->getUniqueId() . '/<action:\w+>',
                'route' => $this->getUniqueId() . '/rest/<action>',
                'verb' => ['POST'],
            ],
            [
                'class' => UrlRule::class,
                'pattern' => $this->getUniqueId() . '/<action:\w+>',
                'route' => $this->getUniqueId() . '/rest/options',
                'verb' => ['OPTIONS'],
            ],
        ];
    }

    /**
     * Initializes the oauth2 server and its dependencies.
     */
    public function initOauth2Server(): void
    {
        if ($this->serverInitialized) {
            return;
        }

        $this->serverInitialized = true;
        $this->modelMap = array_merge($this->defaultModelMap, $this->modelMap);
        $this->storageMap = array_merge($this->defaultStorageMap, $this->storageMap);
        foreach ($this->modelMap as $name => $definition) {
            Yii::$container->set(models::class . '\\' . $name, $definition);
        }

        foreach ($this->storageMap as $name => $definition) {
            Yii::$container->set($name, $definition);
        }

        $storages = [];
        foreach(array_keys($this->storageMap) as $name) {
            $storages[$name] = Yii::$container->get($name);
        }

        $grantTypes = [];
        foreach($this->grantTypes as $name => $options) {
            if(!isset($storages[$name]) || empty($options['class'])) {
                throw new InvalidConfigException(
                    'Invalid grant types configuration.'
                );
            }

            $class = $options['class'];
            unset($options['class']);

            $reflection = new ReflectionClass($class);
            $config = array_merge([0 => $storages[$name]], [$options]);

            $instance = $reflection->newInstanceArgs($config);
            $grantTypes[$name] = $instance;
        }

        $this->set('server', Yii::$container->get(Server::class, [
            $this,
            $storages,
            [
                'token_param_name' => $this->tokenParamName,
                'access_lifetime' => $this->tokenAccessLifetime,
            ],
            $grantTypes
        ]));
        $this->set('request', Request::createFromGlobals());
        $this->set('response', new Response());
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->initOauth2Server();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app): void
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules($this->urlRules());
        } else {
            $this->controllerNamespace = commands::class;
        }

        $this->registerTranslations($app);
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
     * Register translations for this module
     */
    public function registerTranslations($app): void
    {
        $route = 'roaresearch/yii2/oauth2/';

        $app->get('i18n')->translations[$route . '*'] ??= [
            'class' => PhpMessageSource::class,
            'basePath' => __DIR__ . '/messages',
            'fileMap' => [
                $route . 'oauth2server' => 'oauth2server.php',
            ],
        ];
    }

    /**
     * Translate module message
     *
     * @param string $category
     * @param string $message
     * @param array $params
     * @param ?string $language
     * @return string
     */
    public static function t(
        string $category,
        string $message,
        array $params = [],
        ?string $language = null
    ): string {
        return Yii::t(
            'roaresearch/yii2/oauth2/' . $category,
            $message,
            $params,
            $language
        );
    }
}
