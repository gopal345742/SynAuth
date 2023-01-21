<?php
/**
 * @var $this yii\web\View
 * @var $user webvimark\modules\UserManagement\models\User
 */
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div style="border: 1px solid;padding: 20px;">
Hello <?= Html::encode($user->fullname) ?>,

<br/><br/>Your account has been created on <?php echo "<a href=". Yii::$app->urlManager->createAbsoluteUrl(['user-management/auth/login']) .">SynVM</a>" ?>.
<br /><br />
To login, please use your Domain credentials:<br/>
Username is : <?= Html::encode($user->username) ?>

<br><br>
Note: This is an auto-generated email. Please do not reply to this email.
</div>