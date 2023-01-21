<?php
/**
 *
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var webvimark\modules\UserManagement\models\rbacDB\Role $model
 */
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;

$this->title = UserManagementModule::t('back', 'Role creation');
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="formPg">
    <div class="col-lg-6">
        <div class="panel form_container">
            <h4 class="formHeading">
                <?=Html::encode($this->title)?>
            </h4>
            <div class="panel-body" style="max-height:100%">
                <?=$this->render('_form', [
    'model' => $model,
])?> </div>
        </div>
    </div>
</div>