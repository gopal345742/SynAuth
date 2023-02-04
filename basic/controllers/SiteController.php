<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\JWTToken;

class SiteController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex() {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {

            $this->actionSubDomainLogin($model->username, $model->password);
        }

        return $this->render('login', [
                    'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout() {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact() {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
                    'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout() {
        return $this->render('about');
    }

    public function actionSubDomainLogin($username, $pass) {
        $data = [
            'username' => $username,
            'pass' => $pass,
            'exp' => (time() + 60)
        ];
        
        //go to autologin of subdomain as per given subdomain
        $path = $this::giveSubDomainUrl($data);
        $url = $path . 'r=user-management/auth2/authlogin';

        $jwt_headers = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];
        $jwt_token = JWTToken::generate_jwt($jwt_headers, $data);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_NOPROXY, 'localhost');

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "content-type: application/json; charset=utf-8",
            "Authorization: ".$jwt_token
        ));

        $result = curl_exec($curl);
        $ch_error = curl_error($curl);

        curl_close($curl);

        if ($ch_error) {
            return $ch_error;
        }

        $result = json_decode($result);
        if ($result->status == 0) {
            
            return 'Error';
        } else {
            $token = $result->token;
            $this->redirect($path . 'r=user-management/auth/login&token=' . $token);
        }
    }

    public function giveSubDomainUrl($data) {
        $path1 = 'http://localhost/synvm/basic/web/index.php?';
        $path = 'http://172.105.33.91/scm_upgrade/synvm/basic/web/index.php?';

        return $path1;
    }
    
    public function actionSubDomainMSLogin($MStoken) {
        $res = $this::FetchDetailFromMS($MStoken);
        if ($res['status'] == 0) {
            $this->redirect(['/site/login']);
        } else {
            $userinfo = $res['result'];
        }
        
        //go to autologin of subdomain as per given subdomain
        $path = $this::giveMSSubDomainUrl($userinfo);
        $url = $path . 'r=user-management/auth2/m-s-authlogin&token=' . $MStoken;
       
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_NOPROXY, 'localhost');

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "content-type: application/json; charset=utf-8"
        ));

        $result = curl_exec($curl);
        $ch_error = curl_error($curl);

        curl_close($curl);

        if ($ch_error) {
            return $ch_error;
        }

        $result = json_decode($result);

        if ($result->status == 0) {
            return 'Error';
        } else {
            $token = $result->token;
            $this->redirect($path . 'r=user-management/auth/login&token=' . $token);
        }
    }

    public function giveMSSubDomainUrl($data) {
        $mail = $data->mail;
        $domain = explode('@', $mail);
        
        if ($domain[1] == 'synradar.com') {
            $path = 'http://172.105.33.91/scm_upgrade/synvm/basic/web/index.php?';
        } else {
            $path = 'http://localhost/synvm/basic/web/index.php?';
        }
        
        return $path;
    }
    
    protected static function FetchDetailFromMS($token) {
        $token = "Bearer" . " " . json_decode($token);
        $url = 'https://graph.microsoft.com/v1.0/me';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_NOPROXY, 'localhost');

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $token
        ));

        $result = curl_exec($curl);
        $ch_error = curl_error($curl);
        curl_close($curl);

        $final_result = [];
        if ($ch_error) {
            $final_result['status'] = 0;
            $final_result['result'] = $ch_error;
        }

        $result = json_decode($result);
        if (isset($result->error)) {
            $final_result['status'] = 0;
            $final_result['result'] = $result->error->code;
        } else {
            $final_result['status'] = 1;
            $final_result['result'] = $result;
        }

        return $final_result;
    }
    
}
