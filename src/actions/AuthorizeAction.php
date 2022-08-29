<?php

namespace roaresearch\yii2\oauth2server\actions;

use roaresearch\yii2\oauth2server\{
    models\AuthForm,
    Module as OAuth2Module
};
use Yii;
use yii\base\Response as YiiResponse;

/**
 * Action used to create an authorization code for external client.
 *
 * @author Angel (Faryshta) Guevara <aguevara@invernaderolabs.com>
 */
class AuthorizeAction extends \yii\base\Action
{
    /**
     * @var string|array $loginUri uri to be used to redirect not logged users.
     * @see yii\helpers\Url::to()
     */
    public string|array $loginUri = ['/site/login'];

    /**
     * @var string $modelClass full class name for the model used to validate
     *   the authorization request.
     */
    public string $modelClass = AuthForm::class;

    /**
     * @var string $viewRoute the route for the file used to render the
     *   HTML authorization form.
     * @see yii\web\Controller::render()
     */
    public string $viewRoute = '@roaresearch/yii2/oauth2server/views/authorize';

    /**
     * @var ?string $modelName the name of the model used on the HTML form.
     * @see yii\base\Model::load()
     */
    public ?string $modelName = '';

    /**
     * @var string|OAuth2Module $oauth2Module the module used to generate the
     *   authorization code. If its an string then that will be used to extract
     *   the actual module from the application.
     * @see yii\base\Application::getModule()
     */
    public string|OAuth2Module $oauth2Module = 'oauth2';

    /**
     * Check if the user is logged in and show the authorization logic or
     * redirect the user to login before continuing
     */
    public function run()
    {
        return Yii::$app->user->getIsGuest()
            ? $this->loginRedirect()
            : $this->handle();
    }

    /**
     * Shows the HTML authorization form on GET request. Validates the
     * authorization request on POST and determines if the access code can be
     * generated.
     *
     * @return mixed
     */
    protected function handle()
    {
        $model = $this->createModel();
        $req = Yii::$app->request;
        $model->load($req->get(), '');

        if (is_string($this->oauth2Module)) {
            $this->oauth2Module = Yii::$app->getModule($this->oauth2Module);
            $this->oauth2Module->initOauth2Server();
        }

        if (
            $model->load($req->post(), $this->modelName)
            && $model->validate()
        ) {
            $resp = $this->oauth2Module->handleAuthorizeRequest(
                $model->authorized,
                Yii::$app->user->getId()
            );

            return $resp->isRedirection()
                ? $this->controller->redirect(
                    $resp->getHttpHeader('Location'),
                    $resp->getStatusCode(),
                )
                : $resp->send();
        }

        return $this->render($model);
    }

    /**
     * @return YiiResponse the response to handle redirection
     */
    protected function loginRedirect(): YiiResponse
    {
        return $this->controller->redirect($this->loginUri);
    }

    /**
     * @return AuthForm creates the model that will validate the authorization
     *   request
     */
    protected function createModel(): AuthForm
    {
        return new ($this->modelClass)();
    }

    /**
     * Renders the HTML authorization form
     *
     * @param AuthForm $model
     * @return string
     */
    protected function render(AuthForm $model): string
    {
        return $this->controller->render($this->viewRoute, ['model' => $model]);
    }
}
