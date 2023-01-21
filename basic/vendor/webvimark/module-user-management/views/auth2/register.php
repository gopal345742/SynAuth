<?php

/**
 * @var $this yii\web\View
 * @var $model webvimark\modules\UserManagement\models\forms\LoginForm
 */

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
?>


<script src="js/bootstrap.min_v4.js"></script>

<style>
    .container {
        width: 100%;
        height: 100%;
    }

    .main-container {
        background: linear-gradient(111.08deg, #FFF8F8 67.5%, #DEB887 -2.84%);
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        overflow: hidden;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
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
        height: 30px;
        font-size: 13px;
    }

    .form-group>input#registerform-email::placeholder {
        text-transform: lowercase;
    }

    .form-group>input#registerform-email {
        text-transform: lowercase;
    }


    form {
        text-align: center;
    }

    .form h5 {
        color: black !important;
    }

    .btn-register {
        background: #008C5F;
        color: white;
        font-weight: bold;
        width: 100px;
        font-size: 14px;
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


    .captcha {
        background-image: repeating-linear-gradient(36deg, #999, #f4f4f4 1px, #999 0px, #eee 2px);
        background-size: 12px;
        float: left;
        border: 1px solid rgba(255, 255, 255, 0.25);
        font-size: 16px;
        padding: 0px 10px;
        width: fit-content;
        height: 100%;
    }

    .captcha .form-group {
        margin: 0px;
    }
</style>



<div class="main-container " id="login-wrapper">

    <div class=" " style="position:relative;height:100%;width:100%;display:flex;align-items:center;justify-content:center">
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
                        <img class="d-block w-100" src="images/intelligence.png" alt="First slide" style="width:340px;height:300px;">
                        <h5>intelligence</h5>

                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="images/automation.png" alt="Second slide" style="width:340px;height:300px;">
                        <h5>automation</h5>

                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="images/visibility.png" alt="Third slide" style="width:340px;height:300px;">
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
    <div class="seperator"></div>
    <div class="left-block ">
        <div class="login-block ">
            <?= Yii::$app->getSession()->getFlash('success'); ?>

            <h1>Register</h1>
            <h5>Welcome to SynRadar. please register below to start using the app</h5>

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
            <?= Yii::$app->getSession()->getFlash('error'); ?>

            <div>
                <?=
                $form->field($model, 'username')
                    ->textInput(['placeholder' => $model->getAttributeLabel('username'), 'autocomplete' => 'off'])->label('Username')
                ?>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?=
                    $form->field($model, 'fullname')
                        ->textInput(['placeholder' => $model->getAttributeLabel('gopal kumar'), 'autocomplete' => 'off'])->label('Fullname')
                    ?>
                </div>
                <div class="col-lg-12">
                    <?=
                    $form->field($model, 'email')
                        ->textInput(['placeholder' => $model->getAttributeLabel('xyz@gmail.com'), 'autocomplete' => 'off'])->label('Email')
                    ?> </div>
            </div>
            <div>
                <?=
                $form->field($model, 'designation')
                    ->textInput(['placeholder' => $model->getAttributeLabel('designation'), 'autocomplete' => 'off'])->label('Designation')
                ?>
            </div>

            <div style="margin-top:1.5em;display:flex;align-items:center;justify-items-between">
                <label for="userType " class="col-lg-3" style="padding:0">User type : </label>
                <div class="radio-btns col-lg-9">
                    <div class="form-check col-lg-6">
                        <input class="form-check-input " type="radio" name="RegisterForm[user_type]" id="businessUser" value="Business" checked>
                        <label class="form-check-label ml-20" for="businessUser">
                            Business user
                        </label>
                    </div>
                    <div class="form-check col-lg-6 ">
                        <input class="form-check-input" type="radio" name="RegisterForm[user_type]" id="itUser" value="IT">
                        <label class="form-check-label ml-20" for="itUser">
                            IT user
                        </label>
                    </div>
                </div>
            </div>

            <div style="margin-top:1.5em;display:flex;">
                <label for="userType " class="" style="padding:0">Captcha : </label>
                <div class=""  style="
    width: -webkit-fill-available;
">
                       <div>
                       <div class="form-group captcha m-0">
                            <?= $model->captcha ?>
                        </div>
                       </div>
                    <div class=" " style="
    width: -webkit-fill-available;
">
                        <?= $form->field($model, 'recaptcha')->textInput(['placeholder' => 'Enter Captcha'])->label(false) ?>

                    </div>
                </div>
            </div>
            <?= $form->field($model, 'captcha')->hiddenInput()->label(false) ?>

            <div>


            </div>


            <div class="hr"></div>

            <div class="text-center">
                <button type="submit" class="btn btn-register">Register</button>
                <p class="font-size_2vh mt-3">Already have an account?. <span>
                        <?= '<a href="?r=user-management/auth/login">Sign in</a>'; ?>
                        <span></p>
            </div>
            <?php ActiveForm::end() ?>


        </div>
    </div>