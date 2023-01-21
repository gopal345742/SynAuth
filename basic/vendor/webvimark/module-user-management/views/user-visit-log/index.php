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
 * @var webvimark\modules\UserManagement\models\search\UserVisitLogSearch $searchModel
 */

$this->title = UserManagementModule::t('back', 'Visit log');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-visit-log-index idxPg">
    <div class="row">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h5 class="pageHeading">
                <?=Html::encode($this->title)?> </h5>
        </div>
    </div>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>



    <div class="row">
        <div class="col-sm-12 text-right text-black">
            <?=GridPageSize::widget(['pjaxId' => 'user-visit-log-grid-pjax'])?>
        </div>
    </div>

    <?php Pjax::begin([
    'id' => 'user-visit-log-grid-pjax',
])?>

    <?=GridView::widget([
    'id' => 'user-visit-log-grid',
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
            'attribute' => 'user_id',
            'value' => function ($model) {
                return GhostHtml::a(@$model->user->username, ['view', 'id' => $model->id], ['data-pjax' => 0]);
            },
            'format' => 'raw',
        ],
        'language',
        'os',
        'browser',
        'ip',

        'visit_time:datetime',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'contentOptions' => ['style' => 'width:70px; text-align:center;'],
        ],
    ],
]);?>

    <?php Pjax::end()?>
</div>


<? \kartik\daterange\DateRangePicker::widget([
    'model' => $searchModel,
    'attribute' => 'visit_time',
])?>