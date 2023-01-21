<?php
/**
 * @var $this yii\web\View
 * @var $user webvimark\modules\UserManagement\models\User
 */
use yii\helpers\Html;
use yii\helpers\Url;

?>

Hello <?= Html::encode($user->fullname) ?>,

<br/><br/>Your authentication mode has been updated on <?php echo "<a href=". Yii::$app->urlManager->createAbsoluteUrl(['user-management/auth/login']) .">SynVM</a>" ?>.
<br /><br />
You may now use your Domain credentials to login:<br/>
Username is : <?= Html::encode($user->username) ?>

<br><br>
Note: This is an auto-generated email. Please do not reply to this email.