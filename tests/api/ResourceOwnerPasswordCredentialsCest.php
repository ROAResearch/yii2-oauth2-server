<?php

use app\fixtures\{UserFixture, OauthScopesFixture};
use Codeception\Util\HttpCode;
use roaresearch\yii2\oauth2server\fixtures\OauthClientsFixture;
use yii\helpers\Json;

/**
 * @author Christopher CM <ccastaneira@tecnoce.com>
 */
class ResourceOwnerPasswordCredentialsCest
{
    public static $token;
    public static $scopeToken;

    public function fixtures(ApiTester $I): void
    {
        $I->haveFixtures([
            'user' => UserFixture::class,
            'scopes' => OauthScopesFixture::class,
            'clients' => OauthClientsFixture::class,
        ]);
    }

    /**
     * @depends fixtures
     */
    public function accessTokenRequest(ApiTester $I): void
    {
        $I->wantTo('Request a new access token.');
        $I->amHttpAuthenticated('testclient', 'testpass');

        $I->sendPOST('/oauth2/token', [
            'grant_type' => 'password',
            'username' => 'erau',
            'password' => 'password_0',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'access_token' => 'string:regex(/[0-9a-f]{40}/)',
            'refresh_token' => 'string:regex(/[0-9a-f]{40}/)',
        ]);

        self::$token = $I->grabDataFromResponseByJsonPath('$.access_token')[0];
    }

    /**
     * @depends fixtures
     */
    public function accessTokenRequestInvalid(ApiTester $I): void
    {
        $I->wantTo('Request a new access token with invalid credentials.');
        $I->amHttpAuthenticated('testclient', 'testpass');

        $I->sendPOST('/oauth2/token', [
            'grant_type' => 'password',
            'username' => 'wrong_user',
            'password' => 'password_0',
        ]);

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();

        $I->seeResponseMatchesJsonType([
            'name' => 'string',
            'message' => 'string',
        ]);
    }

    /**
     * @depends fixtures
     */
    public function accessTokenRequestWithScopes(ApiTester $I): void
    {
        $I->wantTo('Request a new access token with scope.');
        $I->amHttpAuthenticated('testclient', 'testpass');

        $I->sendPOST('/oauth2/token', [
            'grant_type' => 'password',
            'username' => 'erau',
            'password' => 'password_0',
            'scope' => 'user',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'access_token' => 'string:regex(/[0-9a-f]{40}/)',
            'refresh_token' => 'string:regex(/[0-9a-f]{40}/)',
        ]);

        self::$scopeToken = $I->grabDataFromResponseByJsonPath(
            '$.access_token'
        )[0];
    }

    /**
     * @depends accessTokenRequest
     * @depends accessTokenRequestWithScopes
     */
    public function requestToResource(ApiTester $I): void
    {
        $I->wantTo('Request a resource controller.');
        $I->sendGET('/site/index', [
            'accessToken' => self::$token,
        ]);

         $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @depends accessTokenRequest
     */
    public function failedScopedRequest(ApiTester $I): void
    {
        $I->wantTo('Fail on a resource controller with scope.');
        $I->sendGET('/site/user', [
            'accessToken' => self::$token,
        ]);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    /**
     * @depends accessTokenRequest
     */
    public function successScopedRequest(ApiTester $I): void
    {
        $I->wantTo('Success on a resource controller with scope.');
        $I->sendGET('/site/user', [
            'accessToken' => self::$scopeToken,
        ]);

         $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @depends fixtures
     * @depends SingleUseTokenCest:singleUseRequest
     */
    public function requestToResourceIvalid(ApiTester $I): void
    {
        $I->wantTo('Request a resource controller with invalid token.');

        $I->sendGET('/site/index', [
            'accessToken' => 'InvalidToken',
        ]);

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'Unauthorized',
            'message' => 'Your request was made with invalid credentials.',
        ]);
    }
}
