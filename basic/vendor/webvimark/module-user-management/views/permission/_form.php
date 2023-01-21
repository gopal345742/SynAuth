<?php
/**
 * @var yii\widgets\ActiveForm $form
 * @var webvimark\modules\UserManagement\models\rbacDB\Permission $model
 */

use webvimark\modules\UserManagement\models\rbacDB\AuthItemGroup;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'id' => 'role-form',
    //'layout'=>'horizontal',
    'validateOnBlur' => false,
])?>

<?=$form->field($model, 'description')->textInput(['maxlength' => 255, 'autofocus' => $model->isNewRecord ? true : false])?>

<?=$form->field($model, 'name')->textInput(['maxlength' => 64])?>

<?=$form->field($model, 'group_code')
->dropDownList(ArrayHelper::map(AuthItemGroup::find()->asArray()->all(), 'code', 'name'), ['prompt' => ''])?>

<div class="form-group" align="right">
    <div class="col-sm-offset-3 ">
        <?php if ($model->isNewRecord): ?>
        <?=Html::submitButton(
    '<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
    ['class' => 'btn btn-success btn-sm']
)?>
        <?php else: ?>
        <?=Html::submitButton(
    '<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'),
    ['class' => 'btn btn-success btn-sm']
)?>
        <?php endif;?>
    </div>
</div>
<?php ActiveForm::end()?>