<?php
/**
 * @var yii\widgets\ActiveForm $form
 * @var webvimark\modules\UserManagement\models\rbacDB\Role $model
 */

use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;

$this->title = UserManagementModule::t('back', 'Editing role: ') . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>


<div class="col-lg-6 formPg">
    <div class="panel form_container">
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-between align-items-center">
                <h4 class="formHeading">
                    <?=Html::encode($this->title)?> </h4>
            </div>
        </div>
        <div class="panel-body">
            <?=$this->render('_form', [
    'model' => $model,
])?>
        </div>
    </div>
</div>