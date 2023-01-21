<?php

namespace webvimark\modules\UserManagement\models\forms;

use webvimark\helpers\LittleBigHelper;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use \webvimark\modules\UserManagement\models\UserVisitLog;
use app\config\ClientConfiguration;
use yii\base\Model;
use Yii;

class LoginForm extends Model {

    public $username;
    public $password;
    public $rememberMe = false;
    private $_user = false;
 //   public $salt;
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['username', 'password' ], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            ['username', 'validateIP'],
            ['username', 'match', 'pattern' => ClientConfiguration::getVal('ldap_username_validation')]
        ];
    }

    public function attributeLabels() {
        return [
            'username' => UserManagementModule::t('front', 'Login'),
            'password' => UserManagementModule::t('front', 'Password'),
            'rememberMe' => UserManagementModule::t('front', 'Remember me'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword() {
        if (!Yii::$app->getModule('user-management')->checkAttempts()) {
            $this->addError('password', UserManagementModule::t('front', '5 incorrect attempts. Try login after 30 mins'));
            return false;
        }

        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('password', UserManagementModule::t('front', 'Incorrect username or password..'));
            }
        }
    }

    /**
     * Check if user is binded to IP and compare it with his actual IP
     */
    public function validateIP() {
        $user = $this->getUser();

        if ($user AND $user->bind_to_ip) {
            $ips = explode(',', $user->bind_to_ip);

            $ips = array_map('trim', $ips);

            if (!in_array(LittleBigHelper::getRealIp(), $ips)) {
                $this->addError('password', UserManagementModule::t('front', "You could not login from this IP"));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login() {
        if ($this->validate() && $this->simultaneousLogin()) {
            $returnVal = Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
            if($returnVal == 1){
                $this->setDetails();
            }
            return $returnVal;            
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     * @return User|null
     */
    public function getUser() {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    public function simultaneousLogin() {
        return TRUE;
        /*        $user = $this->getUser();
          $return_bool = FALSE;
          $session_id = $user->flag;

          if ($session_id != NULL) {
          $session = Yii::$app->session;
          $session->setId($session_id);

          if ($session->getIsActive()) {
          $return_bool = FALSE;
          $this->addError('password', UserManagementModule::t('front', "User already logged-in from another browser"));
          } else {
          $return_bool = TRUE;
          }
          } else {
          $return_bool = TRUE;
          }
          return $return_bool;

         */
    }

    public function validateUsername() {

        $return_val = FALSE;
        $re = ClientConfiguration::getVal('ldap_username_validation');

        if (!preg_match($re, $this->username)) {
            $return_val = FALSE;
        } else {
            $return_val = TRUE;
        }

        return $return_val;
    }

    public function isPasswordBlank() {
        if ($this->password === NULL || $this->password == '') {
         //   $this->addError('password', UserManagementModule::t('front', 'Password cannot be blank'));
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Added by Paresh Jain
    public function setDetails() {
        $session = Yii::$app->session;
        $this->setUserFlag($session->getId());
        $user = \app\models\User::findOne(Yii::$app->user->id);
        $session->set('usermodel', $user);
        $session->set('user_lastlog', $this->getLastLogin());



       $issue_tracking_flag = (new \yii\db\Query)
                ->select('calendar_issue_tracking,test_type_issue_tracking')
                ->from('master_settings')
                ->one();
        if ($issue_tracking_flag != NULL) {
            $session->set('issue_tracking_flag', $issue_tracking_flag);
        }
        \webvimark\modules\UserManagement\models\UserVisitLog::newVisitor($user->id);
        \app\models\User::resetUserAccess($user->id, [], 'remove');
        \app\models\User::resetUserAccess($user->id, [], 'add');
    }



   // Added by Paresh Jain
    public function getLastLogin() {
        $prev_log = \webvimark\modules\UserManagement\models\UserVisitLog::find()
                ->andWhere(['user_id' => Yii::$app->user->id])
                ->orderBy('id DESC')
                ->one();



       if ($prev_log != NULL) {
            return Yii::$app->formatter->asDatetime($prev_log->visit_time, "medium");
        } else {
            return '';
        }
    }



   // added by Paresh Jain
    public function setUserFlag($id) {
        $userid = Yii::$app->user->id;



       $user = \webvimark\modules\UserManagement\models\User::findOne($userid);
        if ($user) {
            $user->flag = $id;
            $user->save(TRUE, ['flag']);
        }
    }
}