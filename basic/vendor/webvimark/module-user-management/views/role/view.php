<?php
/**
 * @var yii\widgets\ActiveForm $form
 * @var array $childRoles
 * @var array $allRoles
 * @var array $routes
 * @var array $currentRoutes
 * @var array $permissionsByGroup
 * @var array $currentPermissions
 * @var yii\rbac\Role $role
 */

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = UserManagementModule::t('back', 'Permissions for role:') . ' ' . $role->description;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="viewPg">
    <div class="row ">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h5 class="pageHeading">
                <?=Html::encode($this->title)?> </h5>
            <p>
                <?=GhostHtml::a(UserManagementModule::t('back', 'Edit'), ['update', 'id' => $role->name], ['class' => 'btn btn-sm btn-success'])?>
                <?=GhostHtml::a(UserManagementModule::t('back', 'Create'), ['create'], ['class' => 'btn btn-sm btn-success'])?>
            </p>
        </div>
    </div>


    <div class="row">
        <div class="col-sm-4">
            <div class="panel box">
                <div class="new-panel-heading">
                    <div>
                        <span class="glyphicon glyphicon-th"></span> <?=UserManagementModule::t('back', 'Child roles')?>

                    </div>
                </div>
                <div class="panel-body">
                    <?=Html::beginForm(['set-child-roles', 'id' => $role->name])?>

                    <?=Html::checkboxList(
    'child_roles',
    ArrayHelper::map($childRoles, 'name', 'name'),
    ArrayHelper::map($allRoles, 'name', 'description'),
    [
        'item' => function ($index, $label, $name, $checked, $value) {
            $list = '<ul style="padding-left: 10px">';
            foreach (Role::getPermissionsByRole($value) as $permissionName => $permissionDescription) {
                $list .= $permissionDescription ? "<li>{$permissionDescription}</li>" : "<li>{$permissionName}</li>";
            }
            $list .= '</ul>';

            $helpIcon = Html::beginTag('span', [
                'title' => UserManagementModule::t('back', 'Permissions for role - "{role}"', [
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
            $checkbox = "<label class='info'><input type='checkbox' name='{$name}' value='{$value}' {$isChecked}> {$label}</label>";

            return $helpIcon . ' ' . $checkbox;
        },
        'separator' => '<br>',
    ]
)?>

                    <hr />
                    <div class="" align="right">
                        <?=Html::submitButton(
    '<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'),
    ['class' => 'btn btn-success btn-sm']
)?>
                    </div>
                    <?=Html::endForm()?>
                </div>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="panel box">
                <div class="new-panel-heading">
                    <div>
                        <span class="glyphicon glyphicon-th"></span> <?=UserManagementModule::t('back', 'Permissions')?>

                    </div>
                </div>
                <div class="panel-body">
                    <?=Html::beginForm(['set-child-permissions', 'id' => $role->name])?>

                    <div class="row">
                        <?php foreach ($permissionsByGroup as $groupName => $permissions): ?>
                        <div class="col-sm-6">
                            <fieldset>
                                <legend class=""><?=$groupName?></legend>

                                <?=Html::checkboxList(
    'child_permissions',
    ArrayHelper::map($currentPermissions, 'name', 'name'),
    ArrayHelper::map($permissions, 'name', 'description'),
    ['separator' => '<br>',
        'itemOptions' => [
            'labelOptions' => [
                'class' => 'info',
            ],
        ]]
)?>
                            </fieldset>
                            <br />
                        </div>


                        <?php endforeach?>
                    </div>

                    <hr />
                    <div align="right">
                        <?=Html::submitButton(
    '<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'),
    ['class' => 'btn btn-success btn-sm']
)?>
                    </div>
                    <?=Html::endForm()?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php
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
?>