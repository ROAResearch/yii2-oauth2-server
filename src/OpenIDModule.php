<?php

namespace roaresearch\yii2\oauth2server;

/**
 * For example,
 *
 * ```php
 * 'oauth2' => [
 *     'class' => 'roaresearch\yii2\oauth2server\OpenIDModule',
 *     'issuer' => 'example.com',
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
 *         ],
 *     ],
 * ],
 * ```
 *
 * @author Angel (Faryshta) Guevara <aguevara@invernaderolabs.com>
 */
class OpenIDModule extends Module
{
    /**
     * @var string
     */
    public string $issuer;

    /**
     * @inheritdoc
     */
    protected function oauth2ServerConfig(): array
    {
        return parent::oauth2ServerConfig() + [
            'use_openid_connect' => true,
            'issuer' => $this->issuer,
        ];
    }
}
