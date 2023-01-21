<?php

/**
 * @var $this yii\web\View
 * @var $user webvimark\modules\UserManagement\models\User
 */
use yii\helpers\Html;
use yii\helpers\Url;
?>

Hello <?= Html::encode($user->fullname) ?>,

<br/><br/>Your password has been reset on <?php echo "<a href=" . Yii::$app->urlManager->createAbsoluteUrl(['user-management/auth/login']) . ">SynVM</a>" ?>.

<br/><br/>
To login, please use below mentioned credentials:<br/>
Username is : <?= Html::encode($user->username) ?>
<br/>
Temporary Password is : <?= Html::encode($user->password) ?>
<br/><br/>
Kindly note: You will be asked to change your password post login. Use this temporary password to set a new password.

<br><br>
Note: This is an auto-generated email. Please do not reply to this email.