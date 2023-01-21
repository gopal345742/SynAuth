<?php

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 */
$this->title = 'User Profile';
if (User::canRoute(['user-management/user/index'])) {
    $this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['index']];
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view viewPg col-lg-7">
    <div class="row">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h5 class="pageHeading">
                <?=Html::encode($this->title)?> </h5>
        </div>
    </div>


    <p>
        <?=GhostHtml::a(UserManagementModule::t('back', 'Hierarchy Access'), ['/user-management/user-permission/user-access', 'id' => $model->id], ['class' => 'btn btn-sm btn-default'])?>
        <?=
GhostHtml::a(
    UserManagementModule::t('back', 'Roles and Ownership'), ['/user-management/user-permission/set', 'id' => $model->id], ['class' => 'btn btn-sm btn-default']
)
?>
        <?=GhostHtml::a(UserManagementModule::t('back', 'Change password'), ['change-password', 'id' => $model->id], ['class' => 'btn btn-sm btn-default'])?>
        <?=
GhostHtml::a(
    UserManagementModule::t('back', 'Update'), ['/user-management/user/update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary pull-right', 'style' => 'margin-right:5px']
)
?>

        <?php
/*echo GhostHtml::a(UserManagementModule::t('back', 'Delete'), ['delete', 'id' => $model->id], [
'class' => 'btn btn-sm btn-danger pull-right',
'style' => 'margin-right:5px',
'data' => [
'confirm' => UserManagementModule::t('back', 'Are you sure you want to delete this user?'),
'method' => 'post',
],
])*/
if ($model->status == 1) {
    echo GhostHtml::a(UserManagementModule::t('back', 'Make Inactive'), ['inactive', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-danger pull-right',
        'style' => 'margin-right:5px',
        'data' => [
            'confirm' => UserManagementModule::t('back', 'Are you sure you want to inactive this user?'),
            'encode' => false,
        ],
    ]);
} else {
    echo GhostHtml::a(UserManagementModule::t('back', 'Make Active'), ['active', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-success pull-right',
        'style' => 'margin-right:5px',
        'data' => [
            'confirm' => UserManagementModule::t('back', 'Are you sure you want to mark this user as Active?'),
            'encode' => false,
        ],
    ]);
}
?>

    </p>
    <div class="view_container">
        <?=
DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'vmap_summary custom_table'],
    'attributes' => [
        'fullname',
        [
            'attribute' => 'status',
            'contentOptions' => $model->status == 1 ? ['style' => 'color:green;font-weight:bold'] : ['style' => 'color:red;font-weight:bold'],
            'value' => User::getStatusValue($model->status),
        ],
        [
            'attribute' => 'designation',
            'value' => $model->designation,
        ],
        [
            'attribute' => 'username',
            'value' => $model->username,
            'visible' => User::hasRole('Admin'),
        ],
        [
            'attribute' => 'email',
            'value' => $model->email,
            'format' => 'email',
            'visible' => User::hasPermission('viewUserEmail'),
        ],
        [
            'attribute' => 'email_confirmed',
            'value' => $model->email_confirmed,
            'format' => 'boolean',
            //'visible' => User::hasPermission('viewUserEmail'),
            'visible' => User::hasRole('Admin'),
        ],
        [
            'label' => UserManagementModule::t('back', 'Roles'),
            'value' => implode('<br>', ArrayHelper::getColumn($model->roles, 'description')),
            'visible' => User::hasPermission('viewUserRoles'),
            'format' => 'html',
        ],
        [
            'attribute' => 'bind_to_ip',
            'visible' => User::hasPermission('bindUserToIp'),
        ],
        array(
            'attribute' => 'registration_ip',
            // 'value' => GhostHtml::a($model->registration_ip, $model->registration_ip, ["target" => "_blank"]),
            // 'format' => 'raw',
            'visible' => User::hasPermission('viewRegistrationIp'),
        ),
        [
            'attribute' => 'vendor_id',
            'value' => call_user_func(function ($data) {
                if ($data->vendor_id != null) {
                    return $data->vendor->vendor_company_name . ' (' . app\models\VendorCompanies::$vtype[$data->vendor->vendor_type] . ')';
                }
            }, $model),
        ],
        [
            'attribute' => 'manager_id',
            'value' => call_user_func(function ($data) {
                if ($data->manager_id != null) {
                    return $data->manager->fullname;
                    //return GhostHtml::tag('div', $data->manager->fullname, ['class' => str_replace(' ', '', $data->request_status)]);
                }
            }, $model),
        ],
        [
            'attribute' => 'jira_id',
            'visible' => \app\config\ClientConfiguration::getVal('jiraIntegration'),
            'value' => $model->jira_id,
        ],
    ],
])
?>

    </div>

</div>