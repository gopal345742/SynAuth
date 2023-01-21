Grid page size extension for yii 2 gridview
=====

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist webvimark/grid-page-size "*"
```

or add

```
"webvimark/grid-page-size": "*"
```

to the require section of your `composer.json` file.

Configuration
-------------

If input in GridView

```php

<?=  webvimark\extensions\GridPageSize\GridPageSize::widget([
        'pjaxId'=>'role-grid-pjax',
]) ?>

<?php yii\widgets\Pjax::begin([
        'id'=>'role-grid-pjax',
]) ?>

<?= yii\grid\GridView::widget([
	'id'=>'role-grid',
	'dataProvider' => $dataProvider,
	'pager'=>[
		'options'=>['class'=>'pagination pagination-sm'],
		'hideOnSinglePage'=>true,
		'lastPageLabel'=>'>>',
		'firstPageLabel'=>'<<',
	],
	'filterModel' => $searchModel,
	'layout'=>'{items}<div class="row"><div class="col-sm-8">{pager}</div><div class="col-sm-4 text-right">{summary}'.webvimark\extensions\GridBulkActions\GridBulkActions::widget([
				'gridId'=>'role-grid',
				'actions'=>[ yii\helpers\Url::to(['bulk-delete'])=>webvimark\extensions\GridBulkActions\GridBulkActions::t('app', 'Delete'),],
			]).'</div></div>',
	'columns' => [
		['class' => 'yii\grid\SerialColumn', 'options'=>['style'=>'width:10px'] ],
		[
			'attribute'=>'description',
			'value'=>function(Role $model){
					return yii\helpers\Html::a($model->description, ['view', 'id'=>$model->name], ['data-pjax'=>0]);
				},
			'format'=>'raw',
		],
		'name',
		['class' => 'yii\grid\CheckboxColumn', 'options'=>['style'=>'width:10px'] ],
		[
			'class' => 'yii\grid\ActionColumn',
			'contentOptions'=>['style'=>'width:70px; text-align:center;'],
		],
	],
]); ?>

<?php yii\widgets\Pjax::end() ?>

```

