<?php

namespace roaresearch\yii2\oauth2server\models;

use Yii;
use yii\db\IntegrityException;

/**
 * Form model used to validate an authorization request.
 *
 * @author Angel (Faryshta) Guevara <aguevara@invernaderolabs.com>
 */
class AuthForm extends \yii\base\Model
{
    /**
     * @var ?bool $authorized whether the user authorize access to the external
     *   client
     */
    public ?bool $authorized = null;

    /**
     * @var ?bool $client_id the id of the client requesting authorization
     */
    public ?string $client_id = null;

    /**
     * @var ?string $scopes the requested scopes which will be permited when
     *   creating an access token
     */
    public ?string $scopes = null;

    /**
     * @var ?string $response_type the
     */
    public ?string $response_type = null;
    public ?string $state = null;
    public ?string $redirect_uri = null;

    protected ?OauthClients $clientModel = null;
    protected array $scopesList = [];

    public function rules()
    {
        $validatedClient = fn () => !$this->hasErrors('client_id');

        return [
            [
                [
                    'authorized',
                    'client_id',
                    'response_type',
                    'state',
                    'redirect_uri',
                ],
                'required',
            ],
            [['authorized'], 'boolean'],
            [['redirect_uri'], 'url'],
            [
                [
                    'client_id',
                    'scopes',
                    'response_type',
                    'state',
                    'redirect_uri',
                ],
                'string',
            ],
            [
                ['client_id'],
                'exist',
                'targetClass' => OauthClients::class,
            ],
            [
                ['scopes'],
                function ($attribute) {
                    try {
                         $this->getScopesList();
                    } catch (IntegrityException $e) {
                         $this->addError($atribute, $e->getMessage());
                    }
                },
                'when' => $validatedClient,
            ],
            [
                ['redirect_uri'],
                function ($attribute) {
                    if (
                        !$this->getClientModel()
                            ->validateUri($this->redirect_uri)
                    ) {
                        $this->addError(
                            $attribute,
                            "Redirection URI not recognized by client."
                        );
                    }
                },
                'when' => $validatedClient,
            ],
        ];
    }

    public function getClientModel(): ?OauthClients
    {
        if (empty($this->client_id) || isset($this->clientModel)) {
            return $this->clientModel;
        }

        $this->clientModel = OauthClients::findOne($this->client_id)
            ?: throw new IntegrityException(
                "Unknown client '{$this->client_id}'"
            );

        return $this->clientModel;
    }

    public function getScopesList(): array
    {
        if (empty($this->scopes) || !empty($this->scopesList)) {
            return $this->scopesList;
        }

        $clientModel = $this->getClientModel();
        foreach (explode(' ', $this->scopes) as $scope) {
            $this->scopesList[$scope] = $clientModel->assureScope($scope);
        }

        return $this->scopesList;
    }
}
