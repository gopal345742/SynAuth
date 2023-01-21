<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\forms\ChangeOwnPasswordForm $model
 */
$this->title = UserManagementModule::t('back', 'Change own password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="change-own-password formPg">
    <div class="col-lg-6">
        <div class="panel form_container">
            <div class="new-panel-heading">
                <h4 class="formHeading"><?=Html::encode($this->title)?></h4>
            </div>


            <div class="panel-body">


                <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success text-center">
                    <?=Yii::$app->session->getFlash('success')?>
                </div>
                <?php endif;?>

                <div class="user-form tab-content">

                    <?php
$form = ActiveForm::begin([
    'id' => 'user',
    'enableClientValidation' => true,
    //'layout'=>'horizontal',
    'validateOnBlur' => false,
]);
?>

                    <?php if ($model->scenario != 'restoreViaEmail'): ?>
                    <?=$form->field($model, 'current_password')->passwordInput(['maxlength' => 255, 'autocomplete' => 'off'])?>

                    <?php endif;?>

                    <?=$form->field($model, 'password')->passwordInput(['maxlength' => 255, 'autocomplete' => 'off'])?>

                    <?=$form->field($model, 'repeat_password')->passwordInput(['maxlength' => 255, 'autocomplete' => 'off'])?>


                    <div class="form-group" align="right">
                        <?=
Html::submitButton(
    '<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'), ['class' => 'btn btn-success btn-sm']
)
?>
                    </div>

                    <?php ActiveForm::end();?>

                </div>


            </div>
        </div>
    </div>
</div>