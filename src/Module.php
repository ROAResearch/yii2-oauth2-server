<?php

namespace tecnocen\oauth2server;

use Oauth2\Request;
use Oauth2\Response;
use ReflectionClass;
use tecnocen\roa\urlRules\UrlRuleCreator;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\i18n\PhpMessageSource;
use yii\web\UrlRule;

/**
 * For example,
 *
 * ```php
 * 'oauth2' => [
 *     'class' => 'tecnocen\oauth2server\Module',
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
class Module extends \yii\base\Module
    implements BootstrapInterface, UrlRuleCreator
{
    /**
     * @inheritdoc
     */
    public function createUrlRules()
    {
        return [
            [
                'class' => UrlRule::class,
                'pattern' => $this->getUniqueId() . '/<action:\w+>',
                'route' => $this->getUniqueId() . '/rest/<action>',
                'verb' => ['POST'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public $controllerNamespace = controllers::class;

    /**
     * @var array Model's map
     */
    public $modelMap = [];

    /**
     * @var array Storage's map
     */
    public $storageMap = [];

    /**
     * @var array GrantTypes collection
     */
    public $grantTypes = [];

    /**
     * @var string name of access token parameter
     */
    public $tokenParamName;

    /**
     * @var type max access lifetime
     */
    public $tokenAccessLifetime;
    /**
     * @var array Model's map
     */
    protected $defaultModelMap = [
        'OauthClients' => models\OauthClients::class,
        'OauthAccessTokens' => models\OauthAccessTokens::class,
        'OauthAuthorizationCodes' => models\OauthAuthorizationCodes::class,
        'OauthRefreshTokens' => models\OauthRefreshTokens::class,
        'OauthScopes' => models\OauthScopes::class,
    ];

    /**
     * @var array Storage's map
     */
    protected $defaultStorageMap = [
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
    public function bootstrap($app)
    {
        $this->modelMap = array_merge($this->defaultModelMap, $this->modelMap);
        $this->storageMap = array_merge($this->defaultStorageMap, $this->storageMap);
        foreach ($this->_modelMap as $name => $definition) {
            Yii::$container->set(models::class . '\\' . $name, $definition);
        }

        foreach ($this->_storageMap as $name => $definition) {
            Yii::$container->set($name, $definition);
        }

        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = commands::class;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();

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
     * Gets Oauth2 Server
     *
     * @return Server
     */
    public function getServer()
    {
        return $this->get('server');
    }

    /**
     * Gets Oauth2 Response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * Gets Oauth2 Request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * Register translations for this module
     *
     * @return array
     */
    public function registerTranslations()
    {
        if(!isset(Yii::$app->get('i18n')->translations['modules/oauth2/*'])) {
            Yii::$app->get('i18n')->translations['modules/oauth2/*'] = [
                'class'    => PhpMessageSource::class,
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }

    /**
     * Translate module message
     *
     * @param string $category
     * @param string $message
     * @param array $params
     * @param string $language
     * @return string
     */
    public static function t(
        $category,
        $message,
        $params = [],
        $language = null
    ) {
        return Yii::t(
            'tecnocen/oauth2/' . $category,
            $message,
            $params,
            $language
        );
    }
}
