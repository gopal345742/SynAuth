<?php

use webvimark\extensions\GridPageSize\GridPageSize;
use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var webvimark\modules\UserManagement\models\rbacDB\search\AuthItemGroupSearch $searchModel
 */

$this->title = UserManagementModule::t('back', 'Permission groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-group-index idxPg">
    <div class="row">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h5 class="pageHeading">
                <?=Html::encode($this->title)?> </h5>
            <?=GhostHtml::a_raw(
    '<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
    ['create'],
    ['class' => 'btn btn-success']
)?>
        </div>
    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>



    <div class="panel-body">
        <div class="row">
            <div class="col-sm-6">
            </div>

            <div class="col-sm-6 text-right text-black">
                <?=GridPageSize::widget(['pjaxId' => 'auth-item-group-grid-pjax'])?>
            </div>
        </div>


        <?php Pjax::begin([
    'id' => 'auth-item-group-grid-pjax',
])?>

        <?=GridView::widget([
    'id' => 'auth-item-group-grid',
    'dataProvider' => $dataProvider,
    'pager' => [
        'options' => ['class' => 'pagination pagination-sm'],
        'hideOnSinglePage' => true,
        'lastPageLabel' => '>>',
        'firstPageLabel' => '<<',
    ],
    'layout' => '{items}<div class="row"><div class="col-sm-8">{pager}</div><div class="col-sm-4 text-right">{summary}</div></div>',
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn', 'options' => ['style' => 'width:10px']],

        [
            'attribute' => 'name',
            'value' => function ($model) {
                return GhostHtml::a($model->name, ['update', 'id' => $model->code], ['data-pjax' => 0]);
            },
            'format' => 'html',
        ],
        'code',

        ['class' => 'yii\grid\CheckboxColumn', 'options' => ['style' => 'width:10px']],
        [
            'class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'text-align:center;'],
        ],
    ],
]);?>

        <?php Pjax::end()?>
    </div>
</div>
</div>