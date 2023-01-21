<?php

/**
 * @var yii\web\View $this
 * @var array $permissionsByGroup
 * @var webvimark\modules\UserManagement\models\User $user
 */
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\helpers\ArrayHelper;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use yii\helpers\Html;
use webvimark\modules\UserManagement\components\GhostHtml;
use \app\models\hierarchy\HierarchyLevels;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use \app\models\User;
use yii\widgets\ListView;
use \app\models\AuthAssignment;
use yii\helpers\Url;


$dataProvider = new ActiveDataProvider([
    'query' => AuthAssignment::find()->where(['user_id' => $user->id])->with(['hLUserroleMappings.fkHl']),
    'pagination' => false,
        ]);

BootstrapPluginAsset::register($this);
$this->title = UserManagementModule::t('back', 'Roles and permissions for user:') . ' ' . $user->fullname;

$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['/user-management/user/index']];
$this->params['breadcrumbs'][] = ['label' => $user->fullname, 'url' => ['/user-management/user/view', 'id' => $user->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::csrfMetaTags() ?>
<h2 class="lte-hide-title"><?= Html::encode($this->title) ?></h2>


<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span> <?= UserManagementModule::t('back', 'Roles') ?>
                </strong>
            </div>
            <div class="panel-body">

                <?= Html::beginForm(['set-roles', 'id' => $user->id]) ?>

                <div class="form-group">
                    <label class="control-label" for="select-tag">User Roles</label>
                    <?php
                    //echo "<pre>";
                    $res = ArrayHelper::map(Role::getUserRoles($user->id), 'name', 'name');
                    $avail_roles = ArrayHelper::map(Role::getAvailableRoles(true), 'name', 'description');

                    echo Html::dropDownList('user_role', [1, 3, 5], $avail_roles, ['class' => 'form-control', 'prompt' => 'Select Role', 'id' => 'user_role']);
                    ?>
                </div>

                <div class="form-group">
                    <label class="control-label" for="select-tag">Business Units</label>
                    <?php
                    $all_bu = ArrayHelper::map(HierarchyLevels::getHLbu(false), 'id', 'name');

                    echo Html::dropDownList('hl_bu', NULL, ['all' => 'All'] + $all_bu, ['class' => 'form-control', 'id' => 'hl_bu', 'prompt' => 'Select Business Level']);
                    ?>
                </div>

                <div class="form-group">
                    <label class="control-label" for="select-tag">Technology Units</label>
                    <?php
                    $all_tc = ArrayHelper::map(HierarchyLevels::getHLtc(false), 'id', 'name');

                    echo Html::dropDownList('hl_tc', NULL, ['all' => 'All'] + $all_tc, ['class' => 'form-control', 'id' => 'hl_tc', 'prompt' => 'Select Technology Level']);
                    ?>
                </div>

                <br/>




<?= Html::endForm() ?>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="panel panel-primary" id="panel_display">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span> <?= UserManagementModule::t('back', 'Permissions') ?>
                </strong>
            </div>

            <div class="panel-body">
<?= Html::beginForm(['save-roles', 'id' => $user->id]) ?>
                <?= Html::hiddenInput('user_role_id', '', ['id' => 'user_role_id']) ?>
                <?= Html::hiddenInput('hl_type', '', ['id' => 'hl_type']) ?>
                <?= GhostHtml::submitButton('Save', ['value' => 'Save', 'class' => 'btn btn-success save_values', 'style' => 'float:right;']); ?>
                <br>
                <div id="load_levels">

                </div>
                <br>
<?= GhostHtml::submitButton('Save', ['value' => 'Save', 'class' => 'btn btn-success save_values', 'style' => 'float:right;']); ?>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span> <?= $this->title ?>
                </strong>
            </div>
            <div class="panel-body">
<?php
//echo "<pre>";
//print_r($dataProvider->getModels());die;
?>


                <?=
                GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => ['class' => 'vmap_summary'],
                    'columns' => [
                        [
                            'header' => 'User Role',
                            'attribute' => 'item_name',
                            'format' => 'html',
                            'value' => function ($data) {
                                return '<label><strong>' . Html::encode($data->item_name) . '</strong></label>';
                            }
                        ],
                        [
                            'header' => 'User Permissions',
                            'format' => 'html',
                            'value' => function ($data) {
                                $array = [];
                                if(isset($data->hLUserroleMappings) && !empty($data->hLUserroleMappings))
                                {
                                    
                                    $params = Yii::$app->request->get();
                                    //print_r($params);die;
                                    
                                    foreach($data->hLUserroleMappings as $val)
                                    {
                                        $value = Url::to(['/user-management/user-permission/load-levels', 'id' => Html::encode($params['id']),'hl_type'=>Html::encode($val->fkHl->hierarchy_type),'hl_id'=>Html::encode($val->fk_hl_id),'user_role'=>Html::encode($data->item_name)]);
                                    
                                        //echo "<pre>";
                                        array_push($array, webvimark\modules\UserManagement\components\GhostHtml::a_raw(Html::encode($val->fkHl->name),$value, ['class' => 'show_hl','onclick'=>'load_user_data('.Html::encode($data->item_name).','.Html::encode($val->fkHl->hierarchy_type).')']));
                                        //print_r($val);die;
                                        
                                    }
                                    return implode(' | ', $array);
                                }
                                
                            }
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
                            ]
                        ],
                    ]
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
                <?php ?>

<script src="js/jquery-3.6.0.min.js"></script>

<div style="display: none;" aria-hidden="true" aria-labelledby="contactLabel" role="dialog" tabindex="-1" id="asset_selection" class="modal fade">
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

<script>

    $(".show_hl").click(function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        //alert(url);
        $.ajax({
            url: url,
            method: 'GET',
            success: function (result) {
                $("#panel_display").show();
                $('#load_levels').html(result);
            
            }
        });
    });

    $("#panel_display").hide();

    $("#user_role").change(function () {
        var user_role = $("#user_role").val();
        $("#user_role_id").val(user_role);
        $("#hl_tc").val('');
        $("#hl_bu").val('');
        $("#panel_display").hide();
        if (user_role == "")
        {
            $("#panel_display").hide();
        }
    });

    $("#hl_tc").change(function ()
    {
        if ($("#user_role").val() != "")
        {
            var hl_type = 'TC';
            var hl_id = $("#hl_tc").val();
            var user_role = $("#user_role").val();
            loadlevels(hl_type, hl_id, user_role);
            $("#hl_bu").val('');
            $("#hl_type").val(hl_type);
        } else
        {
            $("#hl_tc").val('');
            alert('Please select User Role');
        }

    });

    $("#hl_bu").change(function () {
        if ($("#user_role").val() != "")
        {
            var hl_type = 'BU';
            var hl_id = $("#hl_bu").val();
            var user_role = $("#user_role").val();
            loadlevels(hl_type, hl_id, user_role);
            $("#hl_tc").val('');
            $("#hl_type").val(hl_type);
        } else
        {
            $("#hl_bu").val('');
            alert('Please select User Role');
        }

    });

    function loadlevels(hl_type, hl_id, user_role)
    {
        var url = '?r=user-management/user-permission/load-levels&id=<?php echo Html::encode($_GET['id']) ?>';
     
        $.ajax({
            url: url,
            method: 'GET',
            data: {hl_type: hl_type, hl_id: hl_id, user_role: user_role},
            success: function (result) {
                $("#panel_display").show();
                $('#load_levels').html(result);
            
            }
        });
    }

    function check_all(obj)
    {

        if (obj.prop("checked") == true)
        {
            $('#load_levels').find('input[data-check="' + obj.val() + '"]').prop("checked", true);
            $('#load_levels').find('input[data-check="' + obj.val() + '"]').each(function () {
                //alert($(this).attr('id'));
                check_all($(this));
            });
        } else
        {
            //alert(obj.attr('data-check'));
            $('#load_levels').find('input[data-check="0"]').prop("checked", false);
            $('#load_levels').find('input[value="' + obj.attr('data-check') + '"]').prop("checked", false);
            $('#load_levels').find('input[data-check="' + obj.val() + '"]').prop("checked", false);
            $('#load_levels').find('input[value="' + obj.attr('data-check') + '"]');

            $('#load_levels').find('input[data-check="' + obj.val() + '"]').each(function () {
                check_all($(this));
            });
        }
        //checkboxes.prop("checked", checkboxes.prop("checked"));
    }

</script>

