<?php

namespace webvimark\modules\UserManagement\models\forms;

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\base\Model;
use Yii;

class ChangeOwnPasswordForm extends Model {

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $current_password;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $repeat_password;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['password', 'repeat_password'], 'required'],
            [['password', 'repeat_password', 'current_password'], 'string', 'max' => 255],
            [['password', 'repeat_password', 'current_password'], 'trim'],
            ['repeat_password', 'compare', 'compareAttribute' => 'password'],
            ['current_password', 'required', 'except' => 'restoreViaEmail'],
            ['current_password', 'validateCurrentPassword', 'except' => 'restoreViaEmail'],
            ['password', 'string', 'max' => 30],
            ['password', 'match', 'pattern' => '/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/', 'message' => 'Password must be minimum 8 characters and contain atleast 1 Upper-case, 1 lower-case, 1-digit and 1-special character'],
            ['password', 'compare', 'compareAttribute' => 'current_password', 'operator' => '!=='],
        ];
    }

    public function attributeLabels() {
        return [
            'current_password' => UserManagementModule::t('back', 'Current password'),
            'password' => UserManagementModule::t('front', 'Password'),
            'repeat_password' => UserManagementModule::t('front', 'Repeat password'),
        ];
    }

    /**
     * Validates current password
     */
    public function validateCurrentPassword() {
        if (!Yii::$app->getModule('user-management')->checkAttempts()) {
            $this->addError('current_password', UserManagementModule::t('back', 'Too many attempts'));

            return false;
        }
        $security = new \yii\base\Security();
        if (!$security->validatePassword($this->current_password, $this->user->password_hash)) {
            $this->addError('current_password', UserManagementModule::t('back', "Wrong password"));
        }
    }

    /**
     * @param bool $performValidation
     *
     * @return bool
     */
    public function changePassword($performValidation = true) {
        if ($performValidation AND ! $this->validate()) {
            return false;
        }

        $this->user->password = $this->password;
        $this->user->removeConfirmationToken();
        $return_val = $this->user->save(TRUE, ['password', 'password_hash']);
        return $return_val;
    }

}
