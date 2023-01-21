<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\UserVisitLog $model
 */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Visit log'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-visit-log-view viewPg">
    <div class="row">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h5 class="pageHeading">
                <?=Html::encode($this->title)?> </h5>
        </div>
    </div>

    <div class="panel view_container">
        <div class="panel-body">

            <?=DetailView::widget([
    'options' => ["class" => "custom_table"],
    'model' => $model,
    'attributes' => [
        [
            'attribute' => 'user_id',
            'value' => @$model->user->username,
        ],
        'ip',
        'language',
        'os',
        'browser',
        'user_agent',

        'visit_time:datetime',
    ],
])?>

        </div>
    </div>
</div>