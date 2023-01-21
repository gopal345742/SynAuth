<?php
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\BootstrapAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

$this->title = UserManagementModule::t('front', 'Login');
BootstrapAsset::register($this);
?>
<?php $this->beginPage()?>
<!DOCTYPE html>
<html lang="<?=Html::encode(Yii::$app->language)?>">

<head>
    <meta charset="<?=Html::encode(Yii::$app->charset)?>" />
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-FRAME-OPTIONS" content="DENY">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?=Html::csrfMetaTags()?>
    <script type="text/javascript">
    if (top !== self) top.location.replace(self.location.href);
    </script>
    <title><?=Html::encode($this->title)?></title>
    <?php $this->head()?>
    <link rel="shortcut icon" href=dashboard/dist/img/SynVM_logo40-50.png type=image/x-icon>
</head>

<body style="background:transparent">

    <?php $this->beginBody()?>
    <!-- <div class="wrap"> -->
    <?php
// NavBar::begin([
//     'brandLabel' => 'SynVM',
//     'brandUrl' => Yii::$app->homeUrl,
//     'options' => [
//         'class' => 'navbar-inverse navbar-kotak navbar-fixed-top',
//     ],
// ]);

// echo Nav::widget([
//     'encodeLabels' => false,
//     'activateParents' => true,
//     'options' => ['class' => 'navbar-nav navbar-right'],
//     'items' => [

//         ['label' => 'Login', 'url' => ['/user-management/auth/login']],
//     ],
// ]);

// NavBar::end();
?>


    <div class="container">
        <br><br>
        <?php if (Yii::$app->session->hasFlash('Error')): ?>
        <div class="alert alert-danger text-center">
            <?php echo Html::encode(Yii::$app->session->getFlash('Error')) ?>
        </div>
        <?php endif;?>

        <br><br><br><br>
        <?=$content?>

    </div>
    <!-- </div> -->

    <?php $this->endBody()?>
</body>

</html>
<?php $this->endPage()?>