<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>

<script type="text/javascript" src="https://alcdn.msauth.net/lib/1.4.4/js/msal.js" integrity="sha384-fTmwCjhRA6zShZq8Ow5ZkbWwmgp8En46qW6yWpNEkp37MkV50I/V2wjzlEkQ8eWD" crossorigin="anonymous">
</script>
<script src="https://code.jquery.com/jquery-3.6.3.js" integrity="sha256-nQLuAZGRRcILA+6dMBOvcRh5Pe310sBpanc6+QBmyVM=" crossorigin="anonymous"></script>
<!-- msal.js with a fallback to backup CDN -->
<script type="text/javascript">
    if (typeof Msal === 'undefined') document.write(unescape(
        "%3Cscript src='https://alcdn.msftauth.net/lib/1.4.4/js/msal.js' type='text/javascript' %3E%3C/script%3E"));
</script>


<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to login:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'col-lg-1 col-form-label mr-lg-3'],
            'inputOptions' => ['class' => 'col-lg-3 form-control'],
            'errorOptions' => ['class' => 'col-lg-7 invalid-feedback'],
        ],
    ]); ?>

    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'rememberMe')->checkbox([
        'template' => "<div class=\"offset-lg-1 col-lg-3 custom-control custom-checkbox\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
    ]) ?>

    <div class="form-group">
        <div class="offset-lg-1 col-lg-11">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>
    <button class="microsoftbtn" type="button" id="signIn" class=" btn" onclick="signIn()">
        <!-- <img src="images/microsoft.png" width="40px" /> -->
        <span>Login with Microsoft</span>
    </button>

    <?php ActiveForm::end(); ?>

    <div class="offset-lg-1" style="color:#999;">
        You may login with <strong>admin/admin</strong> or <strong>demo/demo</strong>.<br>
        To modify the username/password, please check out the code <code>app\models\User::$users</code>.
    </div>
</div>



<script>
    var dynamicUrl = `${window.location.origin}/${
  window.location.pathname.split("/")[1]
}/${window.location.pathname.split("/")[2]}/basic/web/authLogin.php`;

    var localhostUrl = `${window.location.origin}/${
  window.location.pathname.split("/")[1]
}/basic/web/authLogin.php`;

    var origin = window.location.origin;

    const msalConfig = {
        auth: {
            clientId: "c89d429a-f009-4fd7-bf29-fb8773c53b57",
            authority: "https://login.microsoftonline.com/common/",
            redirectUri: origin === "http://localhost" ? localhostUrl : dynamicUrl,
        },
        cache: {
            cacheLocation: "sessionStorage", // This configures where your cache will be stored
            storeAuthStateInCookie: false, // Set this to "true" if you are having issues on IE11 or Edge
        },
    };

    var loginRequest = {
        scopes: ["openid", "profile", "User.Read"],
    };

    var tokenRequest = {
        scopes: ["User.Read"],
    };

    // Create the main myMSALObj instance
    // configuration parameters are located at authConfig.js
    const myMSALObj = new Msal.UserAgentApplication(msalConfig);

    let accessToken;

    // Register Callbacks for Redirect flow
    // myMSALObj.handleRedirectCallback(authPopupCallBack);

    // Helper function to call MS Graph API endpoint
    // using authorization bearer token scheme
    function callMSGraph(endpoint, token, callback) {
        const headers = new Headers();
        const bearer = `Bearer ${token}`;

        headers.append("Authorization", bearer);

        const options = {
            method: "GET",
            headers: headers,
        };

        console.log("request made to Graph API at: " + new Date().toString());

        fetch(endpoint, options)
            .then((response) => response.json())
            .then((response) => callback(response, endpoint))
            .catch((error) => console.log(error));
    }


    $('#signIn').on('click', async function signIn() {
        myMSALObj
            .loginPopup(loginRequest)
            .then(async (loginResponse) => {
                console.log("id_token acquired at: " + new Date().toString());
                console.log(loginResponse);

                if (myMSALObj.getAccount()) {
                    let token = await getTokenPopup(loginRequest);
                    console.log("token", token);
                    callMSGraph(
                        "https://graph.microsoft.com/v1.0/me",
                        token.accessToken,
                        updateUI
                    );
                }
            })
            .catch((error) => {
                console.log(error);
            });
    })



    function signOut() {
        myMSALObj.logout();
    }

    // This function can be removed if you do not need to support IE
    function getTokenPopup(request) {
        return myMSALObj.acquireTokenSilent(request).catch((error) => {
            console.log(error);
            console.log("silent token acquisition fails. acquiring token using popup");

            // fallback to interaction when silent call fails
            return myMSALObj
                .acquireTokenPopup(request)
                .then((tokenResponse) => {
                    return tokenResponse;
                })
                .catch((error) => {
                    console.log(error);
                });
        });
    }

    async function updateUI(data, endpoint) {
        console.log("data", data);
        console.log("Graph API responded at: " + new Date().toString());
        const userDetails = await JSON.stringify(data);
        if (endpoint === "https://graph.microsoft.com/v1.0/me") {
            $.ajax({
                url: `<?php echo yii\helpers\Url::toRoute('/user-management/auth2/autologin'); ?>`,
                data: {
                    userDetails
                },
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function(result) {
                    console.log(result);
                },
            });
        }
    }
</script>