<?php

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use \app\models\User;

/* @var $this yii\web\View */
/* @var $user app\models\BuAppUserMapping */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'User Access Assignment';
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['/user-management/user/index']];
$this->params['breadcrumbs'][] = ['label' => $user->fullname, 'url' => ['/user-management/user/view', 'id' => $user->id]];
$this->params['breadcrumbs'][] = $this->title;
$params['user_id'] = $user->id;
?>

<style>
    .panel-body {
        overflow: auto;
        height: 500px;
    }
</style>

<div class="bu-app-user-mapping-form idxPg">
    <?= Html::beginForm(['save-roles', 'id' => $user->id]) ?>

    <div class="row">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h5 class="pageHeading">
                <?= Html::encode($this->title) ?> </h5>
            <div class="row">
                <?php
                if (isset($_GET['redirect_from']) && $_GET['redirect_from'] == 'create') {
                    echo Html::submitButton('Save & Continue..', ['value' => 'Save', 'class' => 'btn btn-sm btn-success save_values', 'style' => 'float:right;margin-right:18px;']);
                } else {
                    echo Html::submitButton('Save', ['value' => 'Save', 'class' => 'btn btn-sm btn-success save_values', 'style' => 'float:right;margin-right:18px;']);
                }
                ?>
            </div>
        </div>
    </div>


    <div class="row tab-content">
        <div class="col-sm-6 ">
            <div class="panel  box" id="panel_display">
                <div class="new-panel-heading">
                    <strong>
                        <span class="glyphicon glyphicon-th"></span> Business Hierarchy Levels
                    </strong>
                </div>

                <div class="panel-body">

                    <?= Html::hiddenInput('user_role_id', '', ['id' => 'user_role_id']) ?>
                    <?= Html::hiddenInput('hl_type', '', ['id' => 'hl_type']) ?>

                    <div id="load_levels">
                        <div class="box-header with-border">
                            <h3><input type="checkbox" onclick="check_all_bu($(this))" class="parent_checkbox"
                                       name="HL[]" id="parent_BU_all" value="all_bu" data-check="all"
                                       onchange="check_all($(this))" style="margin-right:10px;"><label for="parent_BU_all"
                                       class="custom-checked">All</label></h3>
                        </div>
                        <?php
                        $params['hl_type'] = 'BU';

                        echo $result = \app\helpers\HierarchyLevel_helper::getUserHierarchyLevels($bu_records, $params);
                        ?>
                    </div>
                    <br>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="panel box" id="panel_display">
                <div class="new-panel-heading">
                    <strong>
                        <span class="glyphicon glyphicon-th"></span> Technology Hierarchy Levels
                    </strong>
                </div>

                <div class="panel-body">

                    <div id="load_levels">
                        <div class="box-header with-border">
                            <h3><input type="checkbox" onclick="check_all_tc($(this))" class="parent_checkbox"
                                       name="HL[]" id="parent_TC_all" value="all_tc" data-check="all"
                                       style="margin-right:10px;"><label for="parent_TC_all"
                                       class="custom-checked">All</label></h3>
                        </div>
                        <?php
                        $params['hl_type'] = 'TC';

                        echo $result = \app\helpers\HierarchyLevel_helper::getUserHierarchyLevels($tc_records, $params);
                        ?>
                    </div>
                    <br>

                </div>
            </div>
        </div>
    </div>
<!--    <div class="row panel">
        <?php
//        if (isset($_GET['redirect_from']) && $_GET['redirect_from'] == 'create') {
//            echo GhostHtml::submitButton('Save & Continue..', ['value' => 'Save', 'class' => 'btn btn-success save_values', 'style' => 'float:left;margin-right:18px;']);
//        } else {
//            echo GhostHtml::submitButton('Save', ['value' => 'Save', 'class' => 'btn btn-success save_values', 'style' => 'float:left;margin-right:18px;']);
//        }
        ?>
    </div>-->
    <?= Html::endForm() ?>
</div>
<script type="text/javascript">
    function test(bu_id, apps_bu) {

        var a = bu_id;
        var b = "#" + apps_bu;

        var remember = document.getElementById(a);

        if (remember.checked) {
            $(b).show();
        } else {
            $(b).hide();
            $(b).find('input[type=checkbox]:checked').removeAttr('checked');
        }

    }

    function check_all_child(obj) {

        if (obj.prop("checked") == true) {
            $('.panel').find('input[data-check="' + obj.val() + '"]').prop("checked", true);
            $('.panel').find('input[data-check="' + obj.val() + '"]').each(function () {
                check_all_child($(this));
            });
        } else {
            //alert(obj.attr('data-check'));
            //$('.panel').find('input[data-check="0"]').prop("checked", false);
            $('.panel').find('input[value="' + obj.attr('data-check') + '"]').prop("checked", false);
            $('.panel').find('input[data-check="' + obj.val() + '"]').prop("checked", false);
            $('.panel').find('input[value="' + obj.attr('data-check') + '"]');

            $('.panel').find('input[data-check="' + obj.val() + '"]').each(function () {
                check_all_child($(this));
            });
        }
        //checkboxes.prop("checked", checkboxes.prop("checked"));
    }

    function check_all_tc(obj) {
        if (obj.prop("checked") == true) {
            $('.TC').prop("checked", true);
        } else {
            $('.TC').prop("checked", false);
        }
    }

    function check_all_bu(obj) {
        if (obj.prop("checked") == true) {
            $('.BU').prop("checked", true);
        } else {
            $('.BU').prop("checked", false);
        }
    }
</script>