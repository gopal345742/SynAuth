<?php

use webvimark\extensions\BootstrapSwitch\BootstrapSwitch;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 * @var yii\bootstrap4\ActiveForm $form
 */
?>


<div class="">
    <?php
$form = ActiveForm::begin([
    'id' => 'user',
    //'layout' => 'horizontal',
    'validateOnBlur' => false,
    'enableAjaxValidation' => true,
]);
?>



    <?=
$form->field($model->loadDefaultValues(), 'status')->dropDownList(User::getStatusList())
?>

    <?php if (User::hasPermission('editUserEmail')): ?>

    <?=$form->field($model, 'email')->textInput(['maxlength' => 255, 'autocomplete' => 'off'])->label('Email address')?>

    <?php endif;?>

    <?=
$form->field($model, 'mode_of_authentication')->dropDownList(['ad' => 'AD', 'database' => 'Database'], ['prompt' => 'Select Mode',
    'onchange' => '
               if($(this).val() == "ad"){ var emailid=$("#user-email").val(); $.post("' . Yii::$app->urlManager->createUrl('user-management/user/mode') . '","id="+$(this).val()+"&email="+emailid).done( function( data ) {
                    if(data != ""){
                    $.each( data.split("&"), function(index, value) {
                        var val =value.split("=");
                        if(val[0] == "designation"){
                            $("#user-designation").val(val[1]);
                        }
                        if(val[0] == "fullname"){
                            $("#user-fullname").val(val[1]);
                        }
                        if(val[0] == "manager_id"){
                            $("#user-manager_id").val(val[1]);
                        }
                        if(val[0] == "email"){
                            $("#user-username").val(val[1]);
                        }
                    });
                    }else{
                        alert("Entered email address is not found in the AD server.please change authentication mode");
                    }
                });}',
])->label('Mode Of Authentication');
?>


    <?=$form->field($model, 'username')->textInput(['maxlength' => 50, 'autocomplete' => 'off', 'placeholder' => 'Enter unique username'])?>


    <?=$form->field($model, 'fullname')->textInput(['maxlength' => 50, 'autocomplete' => 'off', 'placeholder' => 'Firstname Lastname'])?>

    <?=$form->field($model, 'designation')->textInput(['maxlength' => 50, 'autocomplete' => 'off', 'placeholder' => 'Designation'])?>


    <?=
$form->field($model, 'manager_id')->dropDownList(ArrayHelper::map(User::getUserManager(), 'id', function ($model) {
    return $model->fullname . '  (' . $model->designation . ')';
}), ['prompt' => 'Select Manager Name..',
]);
?>

    <?php echo $form->field($model, 'vendor_id')->dropDownList(ArrayHelper::map(app\models\VendorCompanies::getGroupedList(), 'vendor_id', 'vendor_name', 'group'), ['prompt' => '--Select--']) ?>


    <div class="form-group" align="right">
        <div class="">
            <?php if ($model->isNewRecord): ?>
            <?=
Html::submitButton(
    '<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'), ['class' => 'btn btn-success btn-sm']
)
?>
            <?php else: ?>
            <?=
Html::submitButton(
    '<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'), ['class' => 'btn btn-success btn-sm']
)
?>
            <?php endif;?>
        </div>
    </div>

    <?php ActiveForm::end();?>

</div>
</div>

<?php BootstrapSwitch::widget()?>

<script>
$(document).ready(function() {

    $(".accordion2 > .accordion-item.is-active").children(".accordion-panel").slideDown();
    $(".accordion2 > .accordion-item > .accordion-thumb").click(function() {

        $(this).parent().toggleClass("is-active").children(".accordion-panel").slideToggle(
            "ease-out");
    });

});
</script>