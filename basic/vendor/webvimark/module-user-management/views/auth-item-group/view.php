<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\rbacDB\AuthItemGroup $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Permission groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-lg-12 d-flex justify-content-between align-items-center">
        <h5 class="pageHeading">
            <?=Html::encode($this->title)?> </h5>
        <p>
            <?=Html::a(UserManagementModule::t('back', 'Edit'), ['update', 'id' => $model->code], ['class' => 'btn btn-sm btn-success'])?>
            <?=Html::a(UserManagementModule::t('back', 'Create'), ['create'], ['class' => 'btn btn-sm btn-success'])?>
            <?=Html::a(Yii::t('yii', 'Delete'), ['delete', 'id' => $model->code], [
    'class' => 'btn btn-sm btn-danger pull-right ml-1',
    'data' => [
        'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
        'method' => 'post',
    ],
])?>
        </p>

    </div>
</div>
<div class="auth-item-group-view viewPg">

    <div class="col-lg-6">
        <div class="panel view_container">
            <div class="panel-body">


                <?=DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'custom_table'],
    'attributes' => [
        'name',
        'code',
        'created_at:datetime',
        'updated_at:datetime',
    ],
])?>

            </div>
        </div>
    </div>
</div>