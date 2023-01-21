<?php

/**
 * @var $this yii\web\View
 */

namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class GhostHtml
 *
 * Show elements only to those, who can access to them
 *
 * @package webvimark\modules\UserManagement\components
 */
class GhostHtml extends Html {

    /**
     * Hide link if user hasn't access to it
     *
     * @inheritdoc
     */
    public static function a($text, $url = null, $options = []) {
        
        
        $text = Html::encode($text);
        if (in_array($url, [null, '', '#','javascript:void();'])) {
            return parent::a($text, $url, $options);
        }

    
        //   $options['data-pjax'] = "0";
        //   $model::Allowedaction($action_id);

        return User::canRoute($url) ? parent::a($text, $url, $options): '' ;
    }

    public static function button($content = 'Button', $options = []) {
        //$content = Html::encode($content);
        if (!isset($options['type'])) {
            $options['type'] = 'button';
        }

        $url1 = (array) $options['value'];
        //  $url1[0] = urldecode($url1[0]);
        // echo implode('<br/><br/><br/><br/>GhostHTMLButton - ',$url1) .'<br/>';

        
        return User::canRoute($url1) ? static::tag('button', $content, $options) : '';
    }

    
    public static function a_raw($text, $url = null, $options = []) {
        $text = \yii\helpers\HtmlPurifier::process($text);
        if (in_array($url, [null, '', '#'])) {
            return parent::a($text, $url, $options);
        }
        
        return User::canRoute($url) ? parent::a($text, $url, $options) : '';
    }


  public static function submitButton($content = 'Submit', $options = [])
    {
        $options['type'] = 'submit';
        return static::button($content, $options);
    }    

}
