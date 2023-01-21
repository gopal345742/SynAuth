<?php
namespace webvimark\extensions\GridPageSize;

use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;

class GridPageSize extends Widget
{
	/**
	 * Event listeners will be delegated via 'body', so this plugin will
	 * work even after grid separately loaded via AJAX.
	 *
	 * You can specify some closer container to improve performance
	 *
	 * @var string
	 */
	public $domContainer = 'body';

	/**
	 * You can render different views for different places
	 *
	 * @var string
	 */
	public $viewFile = 'index';

	/**
	 * @var string
	 */
	public $pjaxId;

	/**
	 * Default - Url::to(['grid-page-size'])
	 *
	 * @var string
	 */
	public $url;

	/**
	 * @var array
	 */
	public $dropDownOptions;

	/**
	 * Text "Records per page"
	 *
	 * @var string
	 */
	public $text;

	/**
	 * Show or not "Clear filters" button when grid filters are changed
	 *
	 * @var bool
	 */
	public $enableClearFilters = true;

	/**
	 * Optional. Used only for "Clear filters" button.
	 * If not set, then it will be guessed via $pjaxId
	 *
	 * @var string
	 */
	public $gridId;

	/**
	 * @var array additional options to be passed to the pjax JS plugin. Please refer to the
	 * [pjax project page](https://github.com/yiisoft/jquery-pjax) for available options.
	 */
	public $clientOptions;

	/**
	 * Multilingual support
	 */
	public function init()
	{
		parent::init();

		$this->text = $this->text ? $this->text : GridPageSize::t('app', 'Records per page');
	}

	/**
	 * @param string $category
	 * @param string $message
	 * @param array  $params
	 * @param null   $language
	 *
	 * @return string
	 */
	public static function t($category, $message, $params = [], $language = null)
	{
		if ( !isset(Yii::$app->i18n->translations['widgets/GridPageSize/*']) )
		{
			Yii::$app->i18n->translations['widgets/GridPageSize/*'] = [
				'class' => 'yii\i18n\PhpMessageSource',
				'sourceLanguage' => 'en-US',
				'basePath' => __DIR__ . '/messages',
				'fileMap' => [
					'widgets/GridPageSize/app' => 'app.php',
				],
			];
		}

		return Yii::t('widgets/GridPageSize/' . $category, $message, $params, $language);
	}

	/**
	 * @throws \yii\base\InvalidConfigException
	 * @return string
	 */
	public function run()
	{
		if ( ! $this->pjaxId )
		{
			throw new InvalidConfigException('Missing pjaxId param');
		}

		$this->setDefaultOptions();

		$this->view->registerJs($this->js());

		return $this->render($this->viewFile);
	}

	/**
	 * Set default options
	 */
	protected function setDefaultOptions()
	{
		$this->pjaxId = '#' . ltrim($this->pjaxId, '#');

		if ( !$this->gridId )
		{
			// Remove "-pjax" from the end
			$this->gridId = substr($this->pjaxId, 0, -5);
		}

		$this->gridId = '#' . ltrim($this->gridId, '#');

		if ( ! $this->dropDownOptions )
		{
			$this->dropDownOptions = [5=>5, 10=>10, 20=>20, 50=>50, 100=>100, 200=>200];
		}

		if ( ! $this->url )
		{
			$this->url = Url::to(['grid-page-size']);
		}
	}

	protected function guessGridId()
	{
		$this->gridId = '';
	}

	/**
	 * @return string
	 */
	protected function js()
	{
		$options = ['container' => $this->pjaxId];
		if( $this->clientOptions ){
			$options = ArrayHelper::merge($options, $this->clientOptions);
		}
		$options = json_encode($options);
		$js = <<<JS
			$('$this->domContainer').off('change', '[name="grid-page-size"]').on('change', '[name="grid-page-size"]', function () {
				var _t = $(this);
				$.post('$this->url', { 'grid-page-size': _t.val() })
					.done(function(){
						$.pjax.reload($options);
					});
			});
JS;

		return $this->enableClearFilters ? $this->jsWithClearFilters() . $js : $js;

	}
	/**
	 * @return string
	 */
	protected function jsWithClearFilters()
	{
		$filterSelectors = $this->gridId . ' .filters input[type="text"], ' . $this->gridId . ' .filters select';
		$clearBtnId = $this->gridId . '-clear-filters-btn';

		$js = <<<JS
			var clearFiltersBtn = $('$clearBtnId');
			var domContainer = $('$this->domContainer');

			function showOrHideClearFiltersBtn() {
				var showClearFiltersButton = false;

				$('$filterSelectors').each(function(){
					var _t = $(this);

					if ( _t.val() )
					{
						showClearFiltersButton = true;
					}
				});

				if ( showClearFiltersButton )
				{
					clearFiltersBtn.show();
				}
				else
				{
					clearFiltersBtn.hide();
				}
			}

			showOrHideClearFiltersBtn();

			// Show button if filters not empty and hide it if they are empty
			domContainer.off('change', '$filterSelectors').on('change', '$filterSelectors', function () {
				showOrHideClearFiltersBtn();
			});

			// Clear filters on button click
			domContainer.off('click', '$clearBtnId').on('click', '$clearBtnId', function () {
				var filter;

				$('$filterSelectors').each(function(){
					filter = $(this);
					filter.val('');
				});

				filter.trigger('change');
			});

JS;

		return $js;

	}
} 
