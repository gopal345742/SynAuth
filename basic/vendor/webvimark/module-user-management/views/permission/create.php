<?php
/**
 *
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var webvimark\modules\UserManagement\models\rbacDB\Permission $model
 */

use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;

$this->title = UserManagementModule::t('back', 'Permission creation');
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Permissions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="formPg">
    <div class="col-lg-6 ">
        <div class="panel  form_container">
            <div class="new-panel-heading">
                <h4 class="formHeading">
                    <?=Html::encode($this->title)?> </h4>
            </div>
            <div class="panel-body">
                <?=$this->render('_form', [
    'model' => $model,
])?>
            </div>
        </div>
    </div>
</div>