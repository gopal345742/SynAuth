<?php

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 */
$this->title = UserManagementModule::t('back', 'Editing user: ') . ' ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = UserManagementModule::t('back', 'Editing');
?>


<div class="formPg">
    <div class="col-lg-6">
        <div class="panel form_container">
            <h4 class="formHeading">
                <?=Html::encode($this->title)?>
            </h4>
            <div class="panel-body" style="max-height:100%">
                <?=$this->render('_form', compact('model'))?>
            </div>
        </div>
    </div>
</div>