<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?><p>
    <b><?= $model->client_id ?></b> requests authorization
    <b><?= $model->scopes ?></b>
</p><?php
$form = ActiveForm::begin(['id' => 'auth-form']);
    if ($model->hasErrors()) {
        echo $form->errorSummary($model);
    }

    ?><div class="form-group">
        <div class="col-lg-offset-1 col-lg-11"><?=
            Html::submitButton(
                'Authorize',
                [
                    'class' => 'btn btn-primary',
                    'value'=> '1',
                    'name'=> 'authorized',
                ]
            )
        ?></div>
    </div>
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11"><?=
            Html::submitButton(
                'Deny',
                [
                    'class' => 'btn btn-secondary',
                    'value'=> '',
                    'name'=> 'authorized',
                ]
            )
        ?></div>
    </div>
<?php ActiveForm::end() ?>
