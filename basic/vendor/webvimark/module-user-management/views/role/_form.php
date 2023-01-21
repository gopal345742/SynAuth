<?php
/**
 * @var yii\widgets\ActiveForm $form
 * @var webvimark\modules\UserManagement\models\rbacDB\Role $model
 */
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin([
    'id' => 'role-form',
    //'layout'         => 'horizontal',
    'validateOnBlur' => false,
])?>

<?=$form->field($model, 'description')->textInput(['maxlength' => 255, 'autofocus' => $model->isNewRecord ? true : false])?>

<?=$form->field($model, 'name')->textInput(['maxlength' => 64])?>


<div class="form-group">
    <div class="" align="right">
        <?php if ($model->isNewRecord): ?>
        <?=Html::submitButton(
    '<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
    ['class' => 'btn btn-sm btn-success']
)?>
        <?php else: ?>
        <?=Html::submitButton(
    '<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'),
    ['class' => 'btn btn-sm btn-success']
)?>
        <?php endif;?>
    </div>
</div>
<?php ActiveForm::end()?>