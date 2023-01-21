<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 */

$this->title = UserManagementModule::t('back', 'Changing password for user: ') . ' ' . $user->username;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $user->username, 'url' => ['view', 'id' => $user->id]];
$this->params['breadcrumbs'][] = UserManagementModule::t('back', 'Changing password');
?>
<div class="user-update col-lg-6 formPg">

    <div class="panel form_container">
        <h4 class="formHeading">
            <?=Html::encode($this->title)?>
        </h4>
        <div class="panel-body" style="max-height:100%">
            <div class="user-form p-3">

                <?php $form = ActiveForm::begin([
    'id' => 'user',
//'layout'=>'horizontal',
]);?>

                <?=$form->field($user, 'password')->passwordInput(['maxlength' => 255, 'autocomplete' => 'off'])?>

                <?=$form->field($user, 'repeat_password')->passwordInput(['maxlength' => 255, 'autocomplete' => 'off'])?>


                <div class="form-group">
                    <div class="" align="right">
                        <?php
$disable = '';
if ($user->mode_of_authentication == 'ad') {
    $disable = 'disabled';
}
if ($user->isNewRecord): ?>
                        <?=Html::submitButton(
    '<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
    ['class' => 'btn btn-success btn-sm']
)?>
                        <?php else: ?>
                        <?=Html::submitButton(
    '<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'),
    ['class' => 'btn btn-success btn-sm ' . $disable]
)?>
                        <?php endif;
?>
                    </div>
                </div>

                <?php ActiveForm::end();?>

            </div>


        </div>
    </div>
</div>