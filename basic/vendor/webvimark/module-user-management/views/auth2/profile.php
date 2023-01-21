<?php

/**
 * @var $this yii\web\View
 * @var $model webvimark\modules\UserManagement\models\forms\LoginForm
 */
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

?>


<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">


<script src="js/comboTreePlugin.js"></script>
<link href="css/comboTree.css" rel="stylesheet" />

<style>
.select2-container {
    display: block !important;
    line-height: 1.7 !important;
    width: 100% !important;
}

label {
    color: black !important;
    font-weight: 600;
    font-size: 14px;
}

.treeFilter span:hover {
    background-color: #276678;
    color: white;
    cursor: pointer;
    font-weight: 500 !important;
}


.treeFilter ul>li>span.ComboTreeItemChlid {
    background-color: white
}

.treeFilter ul>li>span.comboTreeItemTitle {
    display: block;
    padding: 5px;
    border: 1px solid gainsboro;
    border-radius: 5px;
    font-size: small;
    font-weight: 600;
}

.treeFilter input[type="checkbox"] {
    margin-right: 10px;
}

.treeFilter span {
    font-weight: 400;
    background-color: white
}

.treeFilter li {
    font-size: 16px;
    border-radius: 5px;
    margin-block: 2px;
}

.treeFilter input.dropdownInput,
input.multiplesFilter {
    padding: 7px;
    width: 100%;
    margin-bottom: 3px;
    border-radius: 5px;
    border: 1px solid gainsboro;

}

.treeFilter ul {
    list-style: none;
    border-radius: 1px solid gainsboro;
}

.treeFilter input.dropdownInput:focus,
input.multiplesFilter:focus,
input.dropdownInput:focus-within,
input.multiplesFilter:focus-within {
    color: #fff !important;
    background-color: #276678;
    border-color: #51585e;
}

.treeFilter input.dropdownInput:focus::placeholder,
input.multiplesFilter:focus::placeholder {
    color: #fff !important;
}
</style>
<div class="formPg">
    <div class="col-lg-8 m-0 p-0">
        <div class="panel form_container">
            <h4 class="formHeading">
                User Profile</h4>


            <div class="panel-body">



                <div class="col-lg-12 bg-white p-3">
                    <?php
$form = ActiveForm::begin([
    'id' => 'profile-form',
    'options' => ['autocomplete' => 'off'],
    'validateOnBlur' => false,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}",
    ],
])
?>

                    <div style="margin-bottom:1.5em">
                        <?=
$form->field($model, 'username')
->textInput(['placeholder' => $model->getAttributeLabel('username'), 'autocomplete' => 'off'])->label('Username')
?>
                    </div>
                    <div style="margin-bottom:1.5em">
                        <?=
$form->field($model, 'fullname')
->textInput(['placeholder' => $model->getAttributeLabel('fullname'), 'autocomplete' => 'off'])->label('Fullname')
?>
                    </div>
                    <div style="margin-bottom:1.5em">
                        <?=
$form->field($model, 'email')
->textInput(['placeholder' => $model->getAttributeLabel('email'), 'autocomplete' => 'off'])->label('Email')
?>
                    </div>
                    <div style="margin-bottom:1.5em">
                        <?=
$form->field($model, 'designation')
->textInput(['placeholder' => $model->getAttributeLabel('designation'), 'autocomplete' => 'off'])->label('Designation')
?>
                    </div>
                    <input type="hidden" name="User[hl_access]" id="hierarchyIds" />
                    <div class="treeFilter form-group ">
                        <label>Business Hierarchy <span style='color:#f00; font-weight:bold;'>*</span></label>

                        <input type="text" style="font-weight:600; font-size: small;
                   " id="hierarchyFilter" class="form-control" required>
                    </div>
                    <div class="form-group col-sm-12 col-lg-12 mt-5" style="padding:0px">
                        <label>Manager</label>
                        <select name="User[manager_id]" class="form-control" id="exampleFormControlSelect2">
                            <option value="select">Please select Business Hierarchy first</option>
                        </select>
                    </div>

                    <div class="form-group" align="right">
                        <?=Html::submitButton('Save', ['class' => 'btn btn-success ml-3 mt-2 px-5'])?>
                    </div>
                    <?php ActiveForm::end()?>


                </div>

            </div>
        </div>
    </div>
</div>
<script>
var hierarchys = []
var instance;

$(document).ready(function() {
    $.ajax({
        url: `<?php echo yii\helpers\Url::toRoute('/user-management/auth2/all-hierarchy-levels'); ?>`,
        success: function(result) {
            instance = $('#hierarchyFilter').comboTree({
                source: JSON.parse(result),
                isMultiple: true,
                cascadeSelect: true,
                selectableLastNode: false,
            });
        }
    });


    $('.js-example-basic-multiple').select2({
        placeholder: "Please select hierarchy before manager",
        tags: [],
        ajax: {
            url: `<?php echo yii\helpers\Url::toRoute('/user-management/auth2/all-hierarchy-levels'); ?>`,
            dataType: 'json',
            delay: 250,
            data: function(data) {
                return {
                    searchTerm: data.term // search term
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        }

    });
});

$("#hierarchyFilter").on('click', function() {
    instance.onChange(function() {
        hierarchys = [];
        hierarchys.push(instance.getSelectedIds());
        $('#hierarchyIds').val(instance.getSelectedIds());
        $.ajax({
            url: `<?php echo yii\helpers\Url::toRoute('/user-management/auth2/manager-list'); ?>&hl_ids=` +
                hierarchys + ``,
            success: function(result) {
                var masterList = '<option value="-1">select</option>';
                $.each(JSON.parse(result), function(
                    key, val) {
                    masterList += `<option value=` + val.id +
                        `>` +
                        val.name + `</option>`
                });

                $('#exampleFormControlSelect2').html(masterList);
            }
        })

    })
})

// $("#profile-form'").on('submit', function() {
//     $.post(`<?php echo yii\helpers\Url::toRoute('/user-management/auth2/profile'); ?>`, {
//             hierarchys: [instance.getSelectedIds()]
//         },
//         function(data, status) {
//             alert("Data: " + data + "\nStatus: " + status);
//         });

// })
</script>