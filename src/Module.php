<?php

namespace roaresearch\yii2\oauth2server;

use Yii;
use yii\{
    base\BootstrapInterface,
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
    use OAuth2InitTrait;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = controllers::class;

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
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->initOauth2();

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
