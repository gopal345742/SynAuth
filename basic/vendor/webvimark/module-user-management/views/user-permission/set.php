<?php

/**
 * @var yii\web\View $this
 * @var array $permissionsByGroup
 * @var webvimark\modules\UserManagement\models\User $user
 */
use app\models\AuthAssignment;
use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$dataProvider = new ActiveDataProvider([
    'query' => AuthAssignment::find()->where(['user_id' => $user->id])->with(['user.hlUserOwners.fkHl', 'user.agUserOwners.fkAg']),
    'pagination' => false,
]);

BootstrapPluginAsset::register($this);
$this->title = UserManagementModule::t('back', 'Roles and Ownerships for user:') . ' ' . Html::encode($user->fullname);

$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['/user-management/user/index']];
$this->params['breadcrumbs'][] = ['label' => $user->fullname, 'url' => ['/user-management/user/view', 'id' => $user->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<link rel="stylesheet" href="css/jquery.dropdown.css">
<script src="js/jquery.dropdown.js"></script>

<div class="row">
    <div class="col-lg-12 d-flex justify-content-between align-items-center">
        <h5 class="pageHeading">
            <?=HTML::encode($this->title);?>
        </h5>
        <div>
            <button class="btn btn-sm btn-success">
                <?=GhostHtml::a("Back to User Profile", Url::to(['/user-management/user/view', 'id' => Html::encode($user->id)]), ['style' => "text-decoration: underline"])?>
            </button>
        </div>
    </div>
</div>

<div class="row viewPg">

    <div class="col-sm-6">
        <div class="panel box">
            <div class="new-panel-heading">
                <?=$this->title?>
            </div>
            <div class="panel-body">

                <?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['class' => 'vmap_summary custom_table'],
    'columns' => [
        [
            'header' => 'User Role',
            'attribute' => 'item_name',
            'format' => 'html',
            'value' => function ($data) {
                $roles = Role::getAvailableRoles(true);
                foreach ($roles as $role) {
                    if ($role->name === $data->item_name) {
                        return '<label><strong>' . Html::encode($role->description) . '</strong></label>';
                    }
                }
            },
        ],
        [
            'header' => 'Set Ownership',
            'format' => 'raw',
            'value' => function ($data) use ($hlOwnerData, $hlManagerData, $agManagerData, $agOwnerData) {
                $array = [];
                //print_r($hlOwnerData);die;
                $params = Yii::$app->request->get();
                //echo "<pre>";
                //print_r($params);die;
                if ($data->item_name == 'HLOwner') {
                    foreach ($data->user->hlUserOwners as $val) {
                        if ($val->hierarchy_type == 'BU') {
                            //$value = Url::to(['/user-management/user-permission/load-levels', 'id' => Html::encode($params['id']), 'hl_type' => Html::encode($val->fkHl->hierarchy_type), 'hl_id' => Html::encode($val->fk_hl_id), 'user_role' => Html::encode($data->item_name)]);
                            $value = Url::to(['/hierarchy-levels/view', 'id' => Html::encode($val->fk_hl_id)]);

                            array_push($array, webvimark\modules\UserManagement\components\GhostHtml::a($val->fkHl->name, $value, ['class' => 'show_hl', 'onclick' => 'load_user_data(' . Html::encode($data->item_name) . ',' . Html::encode($val->fkHl->hierarchy_type) . ')']));
                        }
                    }
                    if (count($hlOwnerData) > 0) {
                        $result = '<div class="hl_listing">';
                        $result .= implode(' | ', $array);
                        $result .= '</div><div class="hl_input"><div class="dropdown-mul-1" id="drop_down_' . $data->item_name . '" style="width:inherit;"><select multiple placeholder="Select" id="' . $data->item_name . '"> </select></div></div>'
                        . "<button class='save_button btn btn-success' id='save_" . $data->item_name . "' value=''>Save</button>";
                        $result .= "</td><td><a><span class='fa fa-pencil-square-o' id='edit_" . $data->item_name . "' style='float:right;margin-top:0px' value=''></span></a>";

                    } else {
                        $result = "User doesn't have access to business Hierarchy level</td><td>";
                    }
                    return $result;
                } else if ($data->item_name == 'HLManager') {
                    foreach ($data->user->hlUserOwners as $val) {
                        if ($val->hierarchy_type == 'TC') {
                            //$value = Url::to(['/user-management/user-permission/load-levels', 'id' => Html::encode($params['id']), 'hl_type' => Html::encode($val->fkHl->hierarchy_type), 'hl_id' => Html::encode($val->fk_hl_id), 'user_role' => Html::encode($data->item_name)]);
                            $value = Url::to(['/hierarchy-levels/view', 'id' => Html::encode($val->fk_hl_id)]);

                            array_push($array, webvimark\modules\UserManagement\components\GhostHtml::a($val->fkHl->name, $value, ['class' => 'show_hl', 'onclick' => 'load_user_data(' . Html::encode($data->item_name) . ',' . Html::encode($val->fkHl->hierarchy_type) . ')']));
                        }
                    }
                    if (count($hlManagerData) > 0) {
                        $result = '<div class="hl_listing">';
                        $result .= implode(' | ', $array);
                        $result .= '</div><div class="hl_input"><div class="dropdown-mul-1" id="drop_down_' . $data->item_name . '" style="width:inherit;"><select multiple placeholder="Select" id="' . $data->item_name . '"> </select></div></div>'
                        . "<button class='save_button btn btn-success' id='save_" . $data->item_name . "' value=''>Save</button>";

                        $result .= "</td><td><a><span class='fa fa-pencil-square-o' style='float:right;margin-top:0px' value=''></span></a>";
                    } else {
                        $result = "User dosen't have access to Technology Hierarchy Level</td><td>";
                    }

                    return $result;
                } else if ($data->item_name == 'AstGrpOwner') {

                    foreach ($data->user->agUserOwners as $val) {
                        if ($val->access_type == 'owner') {

                            $value = Url::to(['/assetgroup/view', 'id' => Html::encode($val->fk_ag_id)]);

                            array_push($array, webvimark\modules\UserManagement\components\GhostHtml::a($val->fkAg->asset_group_name, $value, ['class' => 'show_hl', 'onclick' => 'load_user_data(' . Html::encode($data->item_name) . ',' . Html::encode($val->fkAg->hierarchy_type) . ')']));
                        }
                    }
                    if (count($agManagerData) > 0) {
                        $result = '<div class="hl_listing">';

                        $result .= implode(' | ', $array);

                        $result .= '</div><div class="hl_input"><div class="dropdown-mul-1" id="drop_down_' . $data->item_name . '" style="width:inherit;"><select multiple placeholder="Select" id="' . $data->item_name . '"> </select></div></div>'
                        . "<button class='save_button btn btn-success' id='save_" . $data->item_name . "' value=''>Save</button>";

                        $result .= "</td><td><a><span class='fa fa-pencil-square-o' id='edit_" . $data->item_name . "' style='float:right;margin-top:0px' value=''></span></a>";

                    } else {
                        $result = "User dosen't have access to Asset Group</td><td>";
                    }
                    return $result;
                } else if ($data->item_name == 'AstGrpManager') {

                    //echo "<pre>";
                    //print_r($data->user->agUserOwners);die;
                    foreach ($data->user->agUserOwners as $val) {

                        if ($val->access_type == 'manager') {

                            $value = Url::to(['/assetgroup/view', 'id' => Html::encode($val->fk_ag_id)]);

                            array_push($array, webvimark\modules\UserManagement\components\GhostHtml::a($val->fkAg->asset_group_name, $value, ['class' => 'show_hl', 'onclick' => 'load_user_data(' . Html::encode($data->item_name) . ',' . Html::encode($val->fkAg->hierarchy_type) . ')']));
                        }
                    }
                    if (count($agOwnerData) > 0) {
                        $result = '<div class="hl_listing">';

                        $result .= implode(' | ', $array);

                        $result .= '</div><div class="hl_input"><div class="dropdown-mul-1" id="drop_down_' . $data->item_name . '" style="width:inherit;"><select multiple placeholder="Select" id="' . $data->item_name . '"> </select></div></div>'
                        . "<button class='save_button btn btn-success' id='save_" . $data->item_name . "' value=''>Save</button>";

                        $result .= "</td><td><a><span class='fa fa-pencil-square-o' style='float:right;margin-top:0px' value=''></span></a>";
                    } else {
                        $result = "User Doesn't have access to Asset Group</td><td>";
                    }

                    return $result;
                } else {
                    return "</td><td>";
                }
            },
        ],
        ['class' => 'yii\grid\ActionColumn',

            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    $url = Url::to(['/user-management/user-permission/revoke-role', 'id' => $key]);

                    return GhostHtml::a_raw('<span class="glyphicon glyphicon-trash"></span>', $url, [
                        'title' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('yii', 'Are you sure?'),
                        'encode' => false,
                    ]);
                },
            ],
        ],
    ],
]);
?>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="panel box">
            <div class="new-panel-heading">
                <?=UserManagementModule::t('back', 'Roles')?>
            </div>
            <div class="panel-body">
                <?=Html::beginForm(['set-roles', 'id' => $user->id])?>
                <?=
Html::checkboxList(
    'roles', ArrayHelper::map(Role::getUserRoles($user->id), 'name', 'name'), ArrayHelper::map(Role::getAvailableRoles(true), 'name', 'description'), [
        'item' => function ($index, $label, $name, $checked, $value) {
            $list = '<ul style="padding-left: 10px">';
            foreach (Role::getPermissionsByRole($value) as $permissionName => $permissionDescription) {
                $list .= $permissionDescription ? "<li>{$permissionDescription}</li>" : "<li>{$permissionName}</li>";
            }
            $list .= '</ul>';

            $helpIcon = Html::beginTag('span', [
                'title' => UserManagementModule::t('back', 'Ownership for role - "{role}"', [
                    'role' => $label,
                ]),
                'data-content' => $list,
                'data-html' => 'true',
                'role' => 'button',
                'style' => 'margin-bottom: 5px; padding: 0 5px',
                'class' => 'btn btn-sm btn-default role-help-btn',
            ]);
            $helpIcon .= '?';
            $helpIcon .= Html::endTag('span');

            $isChecked = $checked ? 'checked' : '';
            $checkbox = "<label><input type='checkbox' name='{$name}' value='{$value}' {$isChecked}> {$label}</label>";

            return $helpIcon . ' ' . $checkbox;
        },
        'separator' => '<br>',
        'multiple' => false,
        "on:change" => "function(){ $('#asset_selection').modal('show');}",
    ]
)
?>
                <br />

                <?php if (Yii::$app->user->isSuperadmin or Yii::$app->user->id != $user->id): ?>

                <?=
Html::submitButton(
    '<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'), ['class' => 'btn btn-primary btn-sm']
)
?>
                <?php else: ?>
                <div class="alert alert-warning well-sm text-center">
                    <?=UserManagementModule::t('back', 'You can not change own Roles')?>
                </div>
                <?php endif;?>


                <?=Html::endForm()?>
            </div>
        </div>
    </div>
</div>

<script>
var categoryData = $.parseJSON('<?php echo json_encode($hlOwnerData); ?>');
var managerData = $.parseJSON('<?php echo json_encode($hlManagerData); ?>');
var agManagerData = $.parseJSON('<?php echo json_encode($agManagerData); ?>');
var agOwnerData = $.parseJSON('<?php echo json_encode($agOwnerData); ?>');

$('#drop_down_HLManager').dropdown({
    data: managerData,
    limitCount: 40,
    multiple: true,
    multipleMode: 'label',
    input: '<input type="text" maxLength="20" placeholder="Search">',
    searchNoData: '<li style="color:#ddd">No Results</li>',
    choice: function() {
        console.log(arguments, this);
    }
});

$('#drop_down_HLOwner').dropdown({
    data: categoryData,
    limitCount: 40,
    multiple: true,
    multipleMode: 'label',
    input: '<input type="text" maxLength="20" placeholder="Search">',
    searchNoData: '<li style="color:#ddd">No Results</li>',
    choice: function() {
        console.log(arguments, this);
    }
});

$('#drop_down_AstGrpManager').dropdown({
    data: agManagerData,
    limitCount: 40,
    multiple: true,
    multipleMode: 'label',
    input: '<input type="text" maxLength="20" placeholder="Search">',
    searchNoData: '<li style="color:#ddd">No Results</li>',
    choice: function() {
        console.log(arguments, this);
    }
});

$('#drop_down_AstGrpOwner').dropdown({
    data: agOwnerData,
    limitCount: 40,
    multiple: true,
    multipleMode: 'label',
    input: '<input type="text" maxLength="20" placeholder="Search">',
    searchNoData: '<li style="color:#ddd">No Results</li>',
    choice: function() {
        console.log(arguments, this);
    }
});

$(document).ready(function() {

    $(".hl_input").hide();
    $(".save_button").hide();

    $(".fa-pencil-square-o").click(function() {


        $('.hl_input').hide();
        $(".save_button").hide();
        $(".fa-pencil-square-o").show();
        $('.hl_listing').show();
        $(this).parent().parent().prev().find('.hl_listing').hide();
        $(this).parent().parent().prev().find('.hl_input').show();
        $(this).hide();
        $(this).parent().parent().prev().find(".save_button").show();

        var edit_id = $(this).attr('id');

    });


    $(".save_button").click(function() {
        var id = $(this).attr('id');
        var url =
            '<?php echo Url::to(['/user-management/user-permission/save-hlowner', 'id' => $user->id]) ?>';
        if (id == 'save_HLManager') {
            var data = {
                hl_id: $("#HLManager").val(),
                hl_type: 'TC'
            };
        } else if (id == 'save_HLOwner') {
            var data = {
                hl_id: $("#HLOwner").val(),
                hl_type: 'BU'
            };
        }

        if (id == 'save_AstGrpManager') {
            var url =
                '<?php echo Url::to(['/user-management/user-permission/save-agowner', 'id' => $user->id]) ?>';
            var data = {
                hl_id: $("#AstGrpManager").val(),
                access_type: 'manager'
            };
        }

        if (id == 'save_AstGrpOwner') {
            var url =
                '<?php echo Url::to(['/user-management/user-permission/save-agowner', 'id' => $user->id]) ?>';
            var data = {
                hl_id: $("#AstGrpOwner").val(),
                access_type: 'owner'
            };
        }
        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            success: function(result) {
                //alert(result);
                location.reload();
            }
        });
    });
});

function edit_ownership() {
    alert(123);
}
</script>

<?php
/*
$this->registerJs(<<<JS

$('.role-help-btn').off('mouseover mouseleave')
.on('mouseover', function(){
var _t = $(this);
_t.popover('show');
}).on('mouseleave', function(){
var _t = $(this);
_t.popover('hide');
});
JS
);
 * *
 */
?>

<script src="js/jquery-3.6.0.min.js"></script>

<div style="display: none;" aria-hidden="true" aria-labelledby="contactLabel" role="dialog" tabindex="-1"
    id="asset_selection" class="modal fade">
    <div style="max-width: 600px;" class="modal-dialog">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h2 id="contactLabel" style="color:white">Select From Hierarchy</h2>
            </div>

            <div class="modal-body">
                <?php
echo "<table class='table table-bordered'>";
echo '<tr><th>Hierarchy</th><th>Tree</th><tr>';

echo '</table>';
?>

                <div class="row" style="padding-left: 10px">
                    <?php
echo Html::button('Select', ['class' => 'btn btn-primary', 'onclick' => 'alert(1)']);
?>
                </div>
            </div>
        </div>
    </div>
</div>