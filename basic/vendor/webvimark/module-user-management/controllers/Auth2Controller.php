<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\modules\UserManagement\UserManagementModule;
use webvimark\modules\UserManagement\controllers\UserController;
use webvimark\modules\UserManagement\models\forms;
use app\models\User;
use yii\web\Controller;
use Yii;

class Auth2Controller extends Controller {

     public $freeAccessActions = ['auto-login', 'register'];
    
    public function actionAutologin($userDetails) {
        //displayName, Gopal Kumar
        //mail, gopal.kumar@synradar.com
        //givenName, Gopal
        //mobilePhone, --, //officeLocation, --, //preferredLanguage, --
        //id, random
        //surname, Kumar
        //jobTitle null
        //userPrincipalName gopal.kumar@synradar.com
        $userinfo = json_decode($userDetails);

        $user = User::find()->where(['email' => $userinfo->mail])->one();
        if ($user != null) {

            Yii::$app->user->login($user, 0);
            return $this->goHome();
        } else {

            $file_path = \Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'configData' . DIRECTORY_SEPARATOR . 'param.json';
            $jsonFileContents = \app\components\utility\FileHandler::readFile($file_path);
            $data = json_decode($jsonFileContents, true);
            $username = $data['ms_search_params']['username'];
            $fullname = $data['ms_search_params']['fullname'];
            $email = $data['ms_search_params']['email'];
            $designation = $data['ms_search_params']['designation'];

            Yii::$app->session->setFlash('error', 'We could not find any matching account with your email address. Kindly sign-up to create a new account.');

            return $this->redirect(['/user-management/auth2/register',
                        'username' => $userinfo->$username,
                        'fullname' => $userinfo->$fullname,
                        'email' => $userinfo->$email,
                        'designation' => $userinfo->$designation
            ]);
        }
    }

    public function actionRegister($username = null, $fullname = Null, $email = Null, $designation = Null) {

        $model = new forms\RegisterForm();
        $model->username = $username;
        $model->fullname = $fullname;
        $model->email = $email;
        $model->designation = $designation;
        $model->captcha = rand(11111, 99999);
        
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {

                if ($model->user_type == 'Business') {

                    $user = new User();
                    $user->attributes = $model->attributes;
                    $user->role_name = ['BusinessUser'];
                    $user->hl_access = $this->getSupportHLs();
                    $user->mode_of_authentication = 'database';
                    $user->status = 1;
                    $user->uploadSetAttributes();
                    if ($user->save()) {
                        $mail_content = $this->renderPartial('/mail/emailConfirmationMail', ['user' => $user]);
                        $user->sendUserCreationEmail($user, $mail_content);
                    }

                    $user->postSave();

                    Yii::$app->session->setFlash('success', 'Your account is successfully created. You can now sign-in to continue and access the application.');

                    return $this->redirect(['/user-management/auth/login']);
                } else {
                    $user = new User();
                    $user->username = $model->username;
                    $user->fullname = $model->fullname;
                    $user->email = $model->email;
                    $user->designation = $model->designation;
                    $user->mode_of_authentication = 'database';
                    $user->status = 0;
                    $user->uploadSetAttributes();
                    if ($user->save()) {
                        $mail_content = $this->renderPartial('/mail/emailConfirmationMail', ['user' => $user]);
                        $user->sendUserCreationEmail($user, $mail_content);
                    }

                    Yii::$app->session->setFlash('success', 'Your accounrt is successfully created, but it is currently under review by Admin. We will notify you once it is activated.');

                    return $this->redirect(['/user-management/auth/login']);
                }
            }
        }

        return $this->render('register', [
                    'model' => $model
        ]);
    }

    public function actionProfile() {
        $this->layout = '@app/views/layouts/main_scm';
        $model = User::findOne(Yii::$app->user->id);

        if ($model->load(Yii::$app->request->post())) {

            if ($model->manager_id == '-1') {
                $model->manager_id = Null;
            }

            if ($model->validate()) {
                if (!is_array($model->hl_access)) {
                    $model->hl_access = explode(',', $model->hl_access);
                }

                $model->profile_submit = 1;
                $model->save();
                $model->postSave();
                $session = Yii::$app->session;
                $session->set('usermodel', $model);

                Yii::$app->session->setFlash('success', 'Your profile is successfully submitted.');

                return $this->goHome();
            }
        }

        return $this->render('profile', [
                    'model' => $model
        ]);
    }

    public function actionAllHierarchyLevels() {

        return \app\helpers\HierarchyLevel_helper::getBusinessHierarchy();
    }

    public function actionManagerList($hl_ids) {

        $managerList = [];
        $hl_ids = explode(',', $hl_ids);

        $condition_params = [
            'hl_id' => $hl_ids,
            'hierarchy_type' => 'BU',
        ];

        $managers = \app\models\hierarchy\HLUserAccess::getUserAccessList($condition_params);
        $i = 0;

        foreach ($managers as $manager) {
            $managerList[$i]['id'] = $manager->id;
            $managerList[$i]['name'] = $manager->fullname;
            $i++;
        }

        return json_encode($managerList);
    }
    
    public function getSupportHLs() {
        $hl_ids = array();
        $support_hl = \app\models\hierarchy\HierarchyLevels::findAll(['support_hierarchy' => 1]);
        
        foreach($support_hl as $hl) {
            array_push($hl_ids, $hl->id);
        }
        
        return $hl_ids;
    }

}
