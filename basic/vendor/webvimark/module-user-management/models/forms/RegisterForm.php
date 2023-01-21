<?php

namespace webvimark\modules\UserManagement\models\forms;

use webvimark\modules\UserManagement\UserManagementModule;
use yii\base\Model;
use Yii;
use yii\helpers\Html;

class RegisterForm extends Model {

    public $username;
    public $fullname;
    public $email;
    public $designation;
    public $user_type;
    public $captcha;
    public $recaptcha;

    /**
     * @inheritdoc
     */
    public function rules() {
        $rules = [
            [['username', 'fullname', 'email', 'user_type', 'captcha', 'recaptcha'], 'required'],
            [['username', 'email'], 'trim'],
            [['email'], 'email'],
            [['email'], 'checkDomain'],
            ['recaptcha', 'compare', 'compareAttribute' => 'captcha', 'operator' => '=='],
            ['username', 'unique',
                'targetClass' => 'webvimark\modules\UserManagement\models\User',
                'targetAttribute' => 'username',
            ],
            ['email', 'unique',
                'targetClass' => 'webvimark\modules\UserManagement\models\User',
                'targetAttribute' => 'email',
            ],
            ['username', 'purgeXSS'],
            [['fullname', 'designation', 'user_type'], 'string', 'max' => 100],
        ];

        return $rules;
    }

    /**
     * Remove possible XSS stuff
     *
     * @param $attribute
     */
    public function purgeXSS($attribute) {
        $this->$attribute = Html::encode($this->$attribute);
    }

    public function checkDomain($attribute) {
        $check_email = explode('@', $this->email);
        $valid_email = 'gopal@synradar.com';
        $valid_domain = explode('@', $valid_email);

        if ($valid_domain[1] != $check_email[1]) {
            $this->addError($attribute, 'This domain is not allowed.');
        }
    }

    /**
     * @return array
     */
    public function attributeLabels() {
        return [
            'username' => UserManagementModule::t('front', 'Username'),
            'fullname' => UserManagementModule::t('front', 'Full Name'),
            'email' => UserManagementModule::t('front', 'Email'),
            'designation' => UserManagementModule::t('front', 'Designation'),
            'user_type' => UserManagementModule::t('front', 'User Type'),
        ];
    }

}
