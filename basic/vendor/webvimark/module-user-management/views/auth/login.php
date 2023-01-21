<?php

/**
 * @var $this yii\web\View
 * @var $model webvimark\modules\UserManagement\models\forms\LoginForm
 */
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
?>
<meta http-equiv="X-FRAME-OPTIONS" content="DENY">
<script src="js/jquery-3.6.0.min.js"></script>
<script src="js/bootstrap.min_v4.js"></script>
<!--

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
</script> -->
<!-- msal.min.js can be used in the place of msal.js; included msal.js to make debug easy -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
</script> -->
<!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
    integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
</script> -->
<script type="text/javascript" src="https://alcdn.msauth.net/lib/1.4.4/js/msal.js"
    integrity="sha384-fTmwCjhRA6zShZq8Ow5ZkbWwmgp8En46qW6yWpNEkp37MkV50I/V2wjzlEkQ8eWD" crossorigin="anonymous">
</script>

<!-- msal.js with a fallback to backup CDN -->
<script type="text/javascript">
if (typeof Msal === 'undefined') document.write(unescape(
    "%3Cscript src='https://alcdn.msftauth.net/lib/1.4.4/js/msal.js' type='text/javascript' %3E%3C/script%3E"));
</script>



<style>
.container {
    width: 100%;
    height: 100%;
}

.main-container {
    background: linear-gradient(111.08deg, #DEB887 -2.84%, #FFF8F8 67.5%);
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    position: absolute;
    top: 0px;
    right: 0;
    left: 0;
    height: 100vh;
    overflow: hidden;
}

.left-block {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    width: 100%;
}

.login-block {
    width: 480px;
    background: white;
    padding: 2em;
    border-radius: 5px;
    margin: 0em 2em;
    box-shadow: gainsboro 0px 0px 10px 1px;
}

.login-block h1 {
    margin: 0;
    margin-bottom: 25px;
    font-size: 28px;
    font-weight: bold;
}



.right-block h5 {
    color: #008C5F;
    font-weight: bold;
    margin-bottom: 2em;
}

.login-block h5 {
    color: #008C5F;
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 2em;
}

.form-group {
    display: flex;
    align-items: center;
}

label {
    font-size: 14px;
    font-weight: normal;
    min-width: 80px;
    text-align: left;
}

.form-group>input {
    margin-left: 20px;
    height: 32px;
    font-size: 14px;
}

form {
    text-align: center;
}

.form h5 {
    color: black !important;
}

.btn-login {
    background: #008C5F;
    color: white;
    font-weight: bold;
    width: 100px
}

img {
    object-fit: fill !important;
}

.right-block {
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    align-items: center;
    background: rgba(251, 251, 251, 0.18);
    border: 1px solid #FFFFFF;
    backdrop-filter: blur(31px);
    width: 75%;
    height: 93%;
    /* Note: backdrop-filter has minimal browser support */

    border-radius: 5px;
    margin: 2em;


}

#myCarousel {
    width: 350px;
    border-radius: 5px;
    overflow: hidden;
    text-align: center;
}

.circle-1 {
    position: absolute;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    top: -20%;
    right: 0px;
    background: linear-gradient(180deg, #0DAA77 0%, rgba(0, 140, 95, 0.07) 100%);
}

.circle-2 {
    position: absolute;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    bottom: -20%;
    left: 0px;

    background: linear-gradient(180deg, #0DAA77 0%, rgba(0, 140, 95, 0.07) 100%);
    transform: rotate(50.95deg);
}

.carousel-control {
    background-image: none !important;
}

.hr {
    background-color: #008C5F;
    height: 1px;
    width: 100%;
    margin-bottom: 1em;
}

.field-loginform-salt {
    margin: 0px;
    padding: 0px;
}

.carousel-label {
    background: white;
    padding: .5em;
    border-radius: 5px;
    text-transform: capitalize;
    font-family: fantasy !important;
    width: 100%;
    color: black;
    text-align: center;
    position: absolute;
    bottom: 0
}

.seperator {
    width: 1px;
    height: 90%;
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto;
    background: #DEB887;
    box-shadow: #DEB887 0px 0px 50px 1px;

}

.microsoftbtn {
    padding-right: 1em;
    border: 1px solid gainsboro;
    display: flex;
    justify-content: space-around;
    align-items: center;
    width: fit-content;
    box-shadow: gainsboro 0px 0px 10px 1px;
    background: white;
    margin: auto;
    margin-bottom: 1em;
}

.microsoftbtn>span {
    font-weight: 600;
    /* text-transform: capitalize; */
}

.authblock {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.carousel-item {
    text-align: center
}

.carousel-item>h5 {
    color: #000;
    font-weight: bold;
    margin-bottom: 2em;
    background: white;
    padding: 0.5em;
    text-transform: capitalize;
    font-family: fantasy
}
</style>

<script>
<?=$this->render("../../../../../web/js/microsoftOAuth.js")?>
</script>

<div class="main-container " id="login-wrapper">

    <div class="left-block ">
        <div class="login-block ">
            <?=Yii::$app->getSession()->getFlash('success');?>

            <h1>Log in for Registered Users</h1>
            <!-- <h5>Welcome to SynRadar. please put your login credentials below to start using the app</h5> -->

            <?php
$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['autocomplete' => 'off'],
    'validateOnBlur' => false,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}",
    ],
])
?>
            <!--<label class="control-label">User name</label>-->
            <?=
$form->field($model, 'username')
->textInput(['placeholder' => $model->getAttributeLabel('username'), 'autocomplete' => 'off'])->label('Username')
?>

            <!--<label class="control-label">Password</label>-->
            <?=
$form->field($model, 'password')
->passwordInput(['placeholder' => $model->getAttributeLabel('password'), 'autocomplete' => "off"])
?>

        <?php if (Yii::$app->params['msLogin'] == 1): ?>
            <button class="microsoftbtn" type="button" id="signIn" class=" btn" onclick="signIn()">
                <img src="images/microsoft.png" width="40px" />
                <span>Login with Microsoft</span>
            </button>
        <?php endif;?>
            
            <?php //$form->field($model, 'salt')->hiddenInput(['value' => dechex(rand())])->label(false)?>
            <div class="hr"></div>

            <div class="authblock">
                <?php if (Yii::$app->params['msLogin'] == 1){
                    echo Html::a('Create account',['/user-management/auth2/register'],['style'=>'font-weight:600']);
                    }
                ?>    
                
                <?= Html::submitButton('Log in', ['class' => 'btn btn-sm  btn-login ']);?>
            </div>
             
            <?php ActiveForm::end()?>


        </div>
    </div>
    <div class="seperator"></div>
    <div class=" "
        style="position:relative;height:100%;width:100%;display:flex;align-items:center;justify-content:center">
        <div class="circle-1"></div>

        <div class="circle-2"></div>


        <div class=' right-block'>
            <img alt="logo" src="images/client-logo.png" width="150px" />
            <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                </ol>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="d-block w-100" src="images/intelligence.png" alt="First slide"
                            style="width:340px;height:300px;">
                        <h5>intelligence</h5>

                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="images/automation.png" alt="Second slide"
                            style="width:340px;height:300px;">
                        <h5>automation</h5>

                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="images/visibility.png" alt="Third slide"
                            style="width:340px;height:300px;">
                        <h5>visibility</h5>
                    </div>
                </div>
                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>

            <h5 style="margin:0em 1em;text-align:center;font-size:14px">Stay Ahead Of The Hackers With The Next-Gen
                Vulnerability
                Risk Management.
            </h5>

        </div>
    </div>

    <script>
    $(".login-block").on("click", "#signIn", function() {
        signIn();
    });
    </script>

    <script src="js/crypto-js.min.js"></script>