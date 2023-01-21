<?php

use webvimark\extensions\GridPageSize\GridPageSize;
use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\rbacDB\AuthItemGroup;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var webvimark\modules\UserManagement\models\rbacDB\search\PermissionSearch $searchModel
 * @var yii\web\View $this
 */
$this->title = UserManagementModule::t('back', 'Permissions');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="idxPg">
    <div class="row">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h5 class="pageHeading">
                <?=Html::encode($this->title)?> </h5>
            <?=
GhostHtml::a_raw(
    '<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'), ['create'], ['class' => 'btn btn-success']
)
?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
        </div>

        <div class="col-sm-6 text-right text-black">
            <?=GridPageSize::widget(['pjaxId' => 'permission-grid-pjax'])?>
        </div>
    </div>

    <?php
Pjax::begin([
    'id' => 'permission-grid-pjax',
])
?>

    <?=
GridView::widget([
    'id' => 'permission-grid',
    'dataProvider' => $dataProvider,
    'pager' => [
        'options' => ['class' => 'pagination pagination-sm'],
        'hideOnSinglePage' => true,
        'lastPageLabel' => '>>',
        'firstPageLabel' => '<<',
    ],
    'filterModel' => $searchModel,
    'layout' => '{items}<div class="row"><div class="col-sm-8">{pager}</div><div class="col-sm-4 text-right">{summary}</div></div>',
    'columns' => [
        ['class' => 'yii\grid\SerialColumn', 'options' => ['style' => 'width:10px']],
        [
            'attribute' => 'description',
            'value' => function ($model) {
                if ($model->name == Yii::$app->getModule('user-management')->commonPermissionName) {
                    return GhostHtml::a(
                        $model->description, ['view', 'id' => $model->name], ['data-pjax' => 0, 'class' => 'label label-primary']
                    );
                } else {
                    return GhostHtml::a($model->description, ['view', 'id' => $model->name], ['data-pjax' => 0]);
                }
            },
            'format' => 'raw',
        ],
        'name',
        [
            'attribute' => 'group_code',
            'filter' => ArrayHelper::map(AuthItemGroup::find()->asArray()->all(), 'code', 'name'),
            'value' => function (Permission $model) {
                return $model->group_code ? Html::encode($model->group->name) : '';
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => ' text-align:center;'],
        ],
    ],
]);
?>
</div>

<?php Pjax::end()?>