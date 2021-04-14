<?php

use Codeception\Util\HttpCode;
use roaresearch\yii2\oauth2server\fixtures\OauthClientsFixture;

/**
 * @author Christopher CM <ccastaneira@tecnoce.com>
 */
class ClientCredentialsCest
{
    public function fixtures(ApiTester $I): void
    {
        $I->haveFixtures([
            'clients' => OauthClientsFixture::class,
        ]);
    }

    /**
     * @depends fixtures
     */
    public function accessTokenRequestValid(ApiTester $I): void
    {
        $I->wantTo('Request a new access token.');
        $I->amHttpAuthenticated('testclient', 'testpass');

        $I->sendPOST('/oauth2/token', [
            'grant_type' => 'client_credentials',
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'access_token' => 'string:regex(/[0-9a-f]{40}/)',
        ]);
    }

    /**
     * @depends fixtures
     */
    public function accessTokenRequestInvalid(ApiTester $I): void
    {
        $I->wantTo('Request a new access token with invalid credentials.');
        $I->amHttpAuthenticated('testclient', 'wrongpass');

        $I->sendPOST('/oauth2/token', [
            'grant_type' => 'client_credentials',
        ]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();

        $I->seeResponseMatchesJsonType([
            'name' => 'string',
            'message' => 'string',
        ]);
    }
}
