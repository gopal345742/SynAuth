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
    public function actionLogin($error = Null) {
        if ($error != Null) {
            Yii::$app->session->setFlash('error', $error);
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
        $res = $this::giveSubDomainUrl($data);

        if ($res['status'] == 1) {
            $path = $res['result'];
        } else {
            $error = 'Domain is not listed for login.';
            return $this->redirect(['/site/login', 'error' => $error]);
        }

        $url = $path . 'r=user-management/auth2/authlogin';

        $jwt_headers = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];
        $jwt_token = JWTToken::generate_jwt($jwt_headers, $data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_NOPROXY, 'localhost');

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "content-type: application/json; charset=utf-8",
            "Authorization: " . $jwt_token
        ));

        $result = curl_exec($curl);
        $ch_error = curl_error($curl);

        //print_r(curl_getinfo($curl));die;
        curl_close($curl);

        if ($ch_error) {
            return $this->redirect(['/site/login', 'error' => $ch_error]);
        }

        $result = json_decode($result);

        if ($result->status == 0) {
            $error = 'There is some problem in domain.';
            return $this->redirect(['/site/login', 'error' => $error]);
        } else {
            $token = $result->token;
            $this->redirect($path . 'r=user-management/auth/login&token=' . $token);
        }
    }

    public function giveSubDomainUrl($data) {
        $email = $data['username'];
        $domain = explode('@', $email);

        $ret = [
            'status' => 0,
            'result' => ''
        ];

        $file_path = \Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'sub_domain_mapping.json';
        $json_data = file_get_contents($file_path);
        $json_data = json_decode($json_data, true);

        if (isset($json_data[$domain[1]])) {
            $ret['status'] = 1;
            $ret['result'] = $json_data[$domain[1]];
        }

        return $ret;
    }

    public function actionSubDomainMSLogin($MStoken, $userName) {
//        $res = $this::FetchDetailFromMS($MStoken);
//        if ($res['status'] == 0) {
//            return $this->redirect(['/site/login', 'error' => $res['result']]);
//        } else {
//            $userinfo = $res['result'];
//        }

        //go to autologin of subdomain as per given subdomain
        $res = $this::giveMSSubDomainUrl($userName);

        if ($res['status'] == 1) {
            $path = $res['result'];
        } else {
            $error = 'Domain is not listed for login.';
            return redirect(['/site/login', 'error' => $error]);
        }

        $url = $path . 'r=user-management/auth2/m-s-authlogin&token=' . $MStoken;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_NOPROXY, 'localhost');

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
        
        //print_r(curl_getinfo($curl));die;
        curl_close($curl);

        if ($ch_error) {
            return $this->redirect(['/site/login', 'error' => $ch_error]);
        }

        $result = json_decode($result);

        if ($result->status == 0) {
            $error = 'There is some problem in domain';
            return $this->redirect(['/site/login', 'error' => $error]);
        } else {
            $token = $result->token;
            $this->redirect($path . 'r=user-management/auth/login&token=' . $token);
        }
    }

    public function giveMSSubDomainUrl($email) {
        $domain = explode('@', $email);

        $ret = [
            'status' => 0,
            'result' => ''
        ];

        $file_path = \Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'sub_domain_mapping.json';
        $json_data = file_get_contents($file_path);
        $json_data = json_decode($json_data, true);

        if (isset($json_data[$domain[1]])) {
            $ret['status'] = 1;
            $ret['result'] = $json_data[$domain[1]];
        }

        return $ret;
    }

    protected static function FetchDetailFromMS($token) {
        $token = "Bearer" . " " . json_decode($token);
        $url = 'https://graph.microsoft.com/v1.0/me';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_NOPROXY, 'localhost');

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
        
        //print_r(curl_getinfo($curl));die;
        curl_close($curl);

        $final_result = [];
        if ($ch_error) {
            $this->redirect(['/site/login', 'error' => $ch_error]);
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
