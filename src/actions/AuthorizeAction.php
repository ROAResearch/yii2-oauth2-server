<?php

namespace roaresearch\yii2\oauth2server\actions;

use roaresearch\yii2\oauth2server\{
    models\AuthForm,
    Module as OAuth2Module
};
use Yii;

class AuthorizeAction extends \yii\base\Action
{
    public string|array $loginUri = ['/site/login'];

    public string $modelClass = AuthForm::class;

    public string $viewRoute = '@roaresearch/yii2/oauth2server/views/authorize';

    public ?string $modelName = '';

    public string|OAuth2Module $oauth2Module = 'oauth2';

    public function run()
    {
        return Yii::$app->user->getIsGuest()
            ? $this->loginRedirect()
            : $this->handle();
    }

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

    protected function loginRedirect()
    {
        return $this->controller->redirect($this->loginUri);
    }

    protected function createModel(): AuthForm
    {
        return new ($this->modelClass)();
    }

    protected function render(AuthForm $model)
    {
        return $this->controller->render($this->viewRoute, ['model' => $model]);
    }
}
