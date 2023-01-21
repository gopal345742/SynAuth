<?php

use webvimark\extensions\GridPageSize\GridPageSize;
use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var webvimark\modules\UserManagement\models\rbacDB\search\RoleSearch $searchModel
 * @var yii\web\View $this
 */
$this->title = UserManagementModule::t('back', 'Roles');
$this->params['breadcrumbs'][] = $this->title;
?>
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
<div class="col-lg-12 idxPg">
    <div class="float-right text-black">
        <?=GridPageSize::widget(['pjaxId' => 'role-grid-pjax'])?>
    </div>


    <?php
Pjax::begin([
    'id' => 'role-grid-pjax',
])
?>

    <?=
GridView::widget([
    'id' => 'role-grid',
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
            'value' => function (Role $model) {
                return GhostHtml::a($model->description, ['view', 'id' => $model->name], ['data-pjax' => 0]);
            },
            'format' => 'raw',
        ],
        'name',
        [
            'class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'text-align:center;'],
        ],
    ],
]);
?>

    <?php Pjax::end()?>
</div>