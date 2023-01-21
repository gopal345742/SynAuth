<?php

use webvimark\extensions\GridBulkActions\GridBulkActions;
use webvimark\extensions\GridPageSize\GridPageSize;
use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var webvimark\modules\UserManagement\models\search\UserSearch $searchModel
 */
$this->title = UserManagementModule::t('back', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index idxPg">
    <div class="row">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h5 class="pageHeading">
                <?= Html::encode($this->title) ?> </h5>
            <?=
            GhostHtml::a_raw(
                    '<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'), ['create'], ['class' => 'btn btn-success btn-sm']
            )
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 text-right text-black float-right">
            <?= GridPageSize::widget(['pjaxId' => 'user-grid-pjax']) ?>
        </div>
    </div>


    <?php
    Pjax::begin([
        'id' => 'user-grid-pjax',
    ])
    ?>

    <?=
    GridView::widget([
        'id' => 'user-grid',
        'dataProvider' => $dataProvider,
        'pager' => [
            'options' => ['class' => 'pagination pagination-sm vmap-table'],
            'hideOnSinglePage' => true,
            'lastPageLabel' => '>>',
            'firstPageLabel' => '<<',
        ],
        'filterModel' => $searchModel,
        'layout' => '{items}<div class="row"><div class="col-sm-8">{pager}</div><div class="col-sm-4 text-right">{summary}' . GridBulkActions::widget(['gridId' => 'user-grid']) . '</div></div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn', 'options' => ['style' => 'width:10px']],
            [
                'attribute' => 'fullname',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'value' => function (User $model) {
                    return GhostHtml::a($model->fullname . '(' . $model->designation . ')', ['view', 'id' => $model->id], ['data-pjax' => 0]);
                },
                'format' => 'html',
            ],
            'username',
            [
                'attribute' => 'email',
                'format' => 'email',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'visible' => User::hasPermission('viewUserEmail'),
                'value' => function ($data) {
                    return Html::encode($data->email);
                },
            ],
            [
                'attribute' => 'gridRoleSearch',
                'filter' => ArrayHelper::map(Role::find()->all(), 'name', 'description'),
                'value' => function (User $model) {
                    return implode(', ', ArrayHelper::map($model->roles, 'name', 'description'));
                },
                'format' => 'html',
                'visible' => User::hasPermission('viewUserRoles'),
            ],
            [
                'attribute' => 'manager.fullname',
                'label' => 'Manager'
            ],
            [
                'class' => 'webvimark\components\StatusColumn',
                'attribute' => 'status',
                'optionsArray' => [
                    [User::STATUS_ACTIVE, UserManagementModule::t('back', 'Active'), 'success'],
                    [User::STATUS_INACTIVE, UserManagementModule::t('back', 'Inactive'), 'warning'],
                //[User::STATUS_BANNED, UserManagementModule::t('back', 'Banned'), 'danger'],
                ],
            ],
//                    [
//                        'attribute'=>'mode_of_authentication',
//                        'format' => 'raw',
//                    ],
            /*                  [
              'value' => function(User $model) {
              return GhostHtml::a(
              UserManagementModule::t('back', 'Roles and permissions'), ['/user-management/user-permission/set', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary', 'data-pjax' => 0]);
              },
              'format' => 'raw',
              'visible' => User::canRoute('/user-management/user-permission/set'),
              'options' => [
              'width' => '10px',
              ],
              ], */
            [
                'value' => function (User $model) {
                    return GhostHtml::a(
                                    UserManagementModule::t('back', 'Change password'), ['change-password', 'id' => $model->id], ['class' => 'btn btn-sm btn-info text-white', 'data-pjax' => 0]);
                },
                'format' => 'raw',
                'options' => [
                    'width' => '10px',
                ],
            ],
            [
                'class' => 'yii\grid\ActionColumn', 'template' => '{update}{status}',
                'contentOptions' => ['style' => 'width:70px; text-align:center;'],
                'buttons' => [
                    'update' => function ($url, $model) {
                        return GhostHtml::a_raw('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                                    'title' => Yii::t('app', 'Update'),
                        ]);
                    },
                    /* 'delete' => function ($url, $model) {

                      if($model->hasRole(['Admin'])) {

                      return GhostHtml::a_raw('<span class="glyphicon glyphicon-trash"></span>', $url, [
                      'title' => Yii::t('yii', 'Delete'),
                      'data-confirm' => Yii::t('yii', 'Are you sure?'),
                      'encode' => false,
                      ]);
                      }
                      }, */
                    'status' => function ($url, $model) {

                        if ($model->status == 1) {
                            $url = Url::to(['user/inactive', 'id' => $model->id]);
                            return GhostHtml::a_raw('<span class="fa fa-user-times"></span>', $url, [
                                        'title' => Yii::t('yii', 'Mark Inactive'),
                                        'data-confirm' => Yii::t('yii', 'Are you sure you want to inactive this user?'),
                                        'encode' => false,
                            ]);
                        } else {
                            $url = Url::to(['user/active', 'id' => $model->id]);
                            return GhostHtml::a_raw('<span class="fa fa-user-plus"></span>', $url, [
                                        'title' => Yii::t('yii', 'Mark Active'),
                                        'data-confirm' => Yii::t('yii', 'Are you sure you want to mark this user as Active?'),
                                        'encode' => false,
                            ]);
                        }
                    },
                ],
            ],
        ],
    ]);
    ?>

    <?php Pjax::end() ?>


</div>