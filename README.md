Yii2 OAuth2 Server
==================

A wrapper for implementing an
[OAuth2 Server](https://github.com/bshaffer/oauth2-server-php).

[![Latest Stable Version](https://poser.pugx.org/roaresearch/yii2-oauth2-server/v/stable)](https://packagist.org/packages/roaresearch/yii2-oauth2-server)
[![Total Downloads](https://poser.pugx.org/roaresearch/yii2-oauth2-server/downloads)](https://packagist.org/packages/roaresearch/yii2-oauth2-server)
[![Code Coverage](https://scrutinizer-ci.com/g/roaresearch/yii2-oauth2-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/roaresearch/yii2-oauth2-server/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/roaresearch/yii2-oauth2-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/roaresearch/yii2-oauth2-server/?branch=master)

Scrutinizer [![Build Status Scrutinizer](https://scrutinizer-ci.com/g/roaresearch/yii2-oauth2-server/badges/build.png?b=master&style=flat)](https://scrutinizer-ci.com/g/roaresearch/yii2-oauth2-server/build-status/master)

This project was forked from
[Filsh Original Project](https://github.com/Filsh/yii2-oauth2-server) but the
changes are not transparent, read [UPGRADE.md] to pass to the latest version.

Installation
------------

The preferred way to install this extension is through
[composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist roaresearch/yii2-oauth2-server "*"
```

or add

```json
"roaresearch/yii2-oauth2-server": "~6.0.0"
```

to the require section of your composer.json.

Usage
-----

To use this extension, simply add the following code in your application
configuration as a new module:

```php
    'bootstrap' => ['oauth2'],
    'modules'=>[
        // other modules ...
        'oauth2' => [
            'class' => \roaresearch\yii2\oauth2server\Module::class,
            'tokenParamName' => 'accessToken',
            'tokenAccessLifetime' => 3600 * 24,
            'storageMap' => [
                'user_credentials' => 'app\models\User',
            ],
            'grantTypes' => [
                'user_credentials' => [
                    'class' => 'OAuth2\GrantType\UserCredentials',
                ],
                'refresh_token' => [
                    'class' => 'OAuth2\GrantType\RefreshToken',
                    'always_issue_new_refresh_token' => true
                ],
            ],
        ],
    ],
```

Bootstrap will initialize translation and add the required url rules to
`Yii::$app->urlManager`.

### JWT tokens

There is no JWT token support on this fork, feel free to submit a
(pull request)[https://github.com/roaresearch/yii2-oauth2-server/pulls] to
enable this functionality.

### UserCredentialsInterface

The class passed to `Yii::$app->user->identityClass` must implement the interface
`\OAuth2\Storage\UserCredentialsInterface`, to store oauth2 credentials in user
table.

```php
use Yii;

class User extends common\models\User implements
    \OAuth2\Storage\UserCredentialsInterface
{

    /**
     * Implemented for Oauth2 Interface
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        /** @var \roaresearch\yii2\oauth2server\Module $module */
        $module = Yii::$app->getModule('oauth2');
        $token = $module->getServer()->getResourceController()->getToken();
        return !empty($token['user_id'])
            ? static::findIdentity($token['user_id'])
            : null;
    }

    /**
     * Implemented for Oauth2 Interface
     */
    public function checkUserCredentials($username, $password)
    {
        $user = static::findByUsername($username);
        if (empty($user)) {
            return false;
        }
        return $user->validatePassword($password);
    }

    /**
     * Implemented for Oauth2 Interface
     */
    public function getUserDetails($username)
    {
        $user = static::findByUsername($username);
        return ['user_id' => $user->getId()];
    }
}
```

### Migrations

The next step is to run migrations

```php
yii migrate all -p=@roaresearch/yii2/oauth2server/migrations/tables
yii fixture "*" -n=roaresearch\\yii2\\oauth2server\\fixtures
```

The first commando create the OAuth2 database scheme. The second command insert
test client credentials `testclient:testpass` for `http://fake/`.

### Controllers

To support authentication by access token. Simply add the behaviors for your
controller or module.

```php
use yii\{
    helpers\ArrayHelper,
    auth\HttpBearerAuth,
    filters\auth\QueryParamAuth,
};
use roasearch\yii2\oauth2server\filters\auth\CompositeAuth;

class Controller extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::class,
                'authMethods' => [
                    ['class' => HttpBearerAuth::class],
                    [
                        'class' => QueryParamAuth::class,
                        'tokenParam' => 'accessToken',
                    ],
                ],
            ],
        ]);
    }
}
```

The code above is the same as the default implementation which can be
simplified as:

```php
use yii\helpers\ArrayHelper;
use roaresearch\yii2\oauth2server\filters\auth\CompositeAuth;

class Controller extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => CompositeAuth::class,
        ]);
    }
}
```

### Scopes

The property `roaresearch\yii2\oauth2server\filters\auth\CompositeAuth::$actionScopes`
set which actions require specific scopes. If those scopes are not meet the
action wont be executed, and the server will reply with an HTTP Status Code 403.

```php
public function behaviors()
{
    return ArrayHelper::merge(parent::behaviors(), [
        'authenticator' => [
            'class' => CompositeAuth::class,
            'actionScopes' => [
                'create' => 'default create',
                'update' => 'default edit',
                '*' => 'default', // wildcards are allowed
            ],
        ],,
    ]);
}
```

### Automatically Revoke Tokens

Sometimes its neccessary to revoke a token on each request to prevent the
request from being triggered twice.

To enable this functionality you need to implement
`roaresearch\yii2\oauth2server\RevokeAccessTokenInterface` in the class used to identify
the authenticated user.

```php

use OAuth2\Storage\UserCredentialsInterface;
use roaresearch\yii2\oauth2server\{
    RevokeAccessTokenInterface,
    RevokeAccessTokenTrait,
};

class User extend \yii\db\ActiveRecord implement
    UserCredentialsInterface,
    RevokeAccessTokenInterface
{
    use RevokeAccessTokenTrait; // optional, trait with default implementation.

    // rest of the class.
}
```

Then use the previous class as configuration for `Yii::$app->user->identityClass`

Attaching the action filter `roaresearch\yii2\oauth2server\filters\RevokeAccessToken`
allows to configure the actions to automatically revoke the access token.

```php
public function behaviors()
{
    return [
        'revokeToken' => [
            'class' => \roaresearch\yii2\oauth2server\filters\RevokeAccessToken::class,
            // optional only revoke the token if it has any of the following
            // scopes. if not defined it will always revoke the token.
            'scopes' => ['author', 'seller'],
            // optional whether or not revoke all tokens or just the active one
            'revokeAll' => true,
            // optional if non authenticated users are permited.
            'allowGuests' => true,
            // which actions this behavior applies to.
            'only' => ['create', 'update'],
        ],
    ];
}
```

### Generate Token with JS

To get access token (js example):

```js
var url = window.location.host + "/oauth2/token";
var data = {
    'grant_type':'password',
    'username':'<some login from your user table>',
    'password':'<real pass>',
    'client_id':'testclient',
    'client_secret':'testpass'
};
//ajax POST `data` to `url` here
//
```

## Authorize Action

Action used to generate access codes for external servers. To test its use first
run the provided fixtures so the testclient is loaded into the database.

```
composer run-fixtures
```

If the test url you are using is not on the default uri list, you will have to
modify the information on the table `oauth_clients` in your database.

Then you can test the access code generation by accessing the Yii2 uri

```
/WEB/authorize?client_id=testclient&response_type=code&state=xyz&redirect_uri=http://127.0.0.1:8080/
```

Which must show a minimal form with just 2 buttons to choose whether you deny
or authorize. If you authorize a new access code will be generated and will
redirect to:

```
http://127.0.0.1:8080/?code=[access code]6&state=xyz
```

If you deny access, it will redirect to the same URI with an error code instead.

You can use the class `roaresearch\yii2\oauth2server\actions\AuthorizeAction` to
declare authorize actions at any controller you want.

```php
use roaresearch\yii2\oauth2server\actions\AuthorizeAction;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'authorize' => [
                'class' => AuthorizeAction::class,
                'loginUri' => ['site/login'],
                'viewRoute' => 'authorize',
                'oauth2Module' => 'api/oauth2',
            ],
        ];
    }
}
```

## Built With

* Yii 2: The Fast, Secure and Professional PHP Framework [http://www.yiiframework.com](http://www.yiiframework.com)

## Code of Conduct

Please read [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for details on our code of conduct.

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](/tags).

_Considering [SemVer](http://semver.org/) for versioning rules 9, 10 and 11 talk about pre-releases, they will not be used._

## Authors

* [**Angel Guevara**](https://github.com/Faryshta) - *Initial work*
* [**Carlos Llamosas**](https://github.com/neverabe) - *Initial work*

See also the list of [contributors](/graphs/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

For more, see https://github.com/bshaffer/oauth2-server-php
