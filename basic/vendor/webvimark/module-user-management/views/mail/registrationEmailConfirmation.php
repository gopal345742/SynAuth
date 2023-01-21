<?php
/**
 * @var $this yii\web\View
 * @var $user webvimark\modules\UserManagement\models\User
 */
use yii\helpers\Html;

?>
<?php
$confirmLink = Yii::$app->urlManager->createAbsoluteUrl(['/user-management/auth/confirm-registration-email', 'token' => $user->confirmation_token]);
?>

Hello <?= Html::encode($user->username) ?>,

<br/><br/>You have been added on SynVM, <?= Yii::$app->urlManager->hostInfo ?>.

<br/><br/>

Follow this link to confirm your E-mail and activate account:<br/>

<?= Html::a('Click here to activate', $confirmLink) ?>

<br/><br/>
To login, please use below pair of credentials:<br/>
Username is : <?= Html::encode($user->username) ?>
<br/>
Temporary Password is : <?= Html::encode($user->password) ?>

<br><br>
Note: This is an auto-generated email. Please do not reply to this email.