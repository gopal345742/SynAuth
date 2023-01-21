<?php

/**
 * @var $this yii\web\View
 * @var $user webvimark\modules\UserManagement\models\User
 */
use yii\helpers\Html;
?>

Hello <?= Html::encode($user->fullname) ?>,

<br/><br/>Your authentication mode has been updated on <?php echo "<a href=" . Yii::$app->urlManager->createAbsoluteUrl(['user-management/auth/login']) . ">SynVM</a>" ?>.

<br/>
To login, please use below pair of credentials:<br/>
Username is : <?= Html::encode($user->username) ?>
<br/>
Temporary Password is : <?= Html::encode($user->password) ?>

<br><br>
Note: This is an auto-generated email. Please do not reply to this email.
