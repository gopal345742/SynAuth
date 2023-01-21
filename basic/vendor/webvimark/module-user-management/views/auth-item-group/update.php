<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\rbacDB\AuthItemGroup $model
 */

$this->title = UserManagementModule::t('back', 'Editing permission group') . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Permission groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->code]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Editing')
?>

<div class="auth-item-group-update formPg">

    <div class="col-lg-6">
        <div class="panel form_container">
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-between align-items-center">
                    <h4 class="formHeading">
                        <?=Html::encode($this->title)?> </h4>
                </div>
            </div>
            <div class="panel-body">

                <?=$this->render('_form', compact('model'))?>
            </div>
        </div>

    </div>
</div>