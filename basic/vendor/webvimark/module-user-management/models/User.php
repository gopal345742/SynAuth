<?php

namespace webvimark\modules\UserManagement\models;

use webvimark\helpers\LittleBigHelper;
use webvimark\helpers\Singleton;
use webvimark\modules\UserManagement\components\AuthHelper;
use webvimark\modules\UserManagement\components\UserIdentity;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\rbacDB\Route;
use webvimark\modules\UserManagement\UserManagementModule;
use Yii;
use yii\behaviors\TimestampBehavior;
use \yii\web\NotFoundHttpException;
use \yii\helpers\ArrayHelper;
use \yii\web\ForbiddenHttpException;
use yii\helpers\Url;
use app\models\BusinessUnits;
use app\models\Credentials;
use webvimark\modules\UserManagement\components\GhostHtml;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property integer $email_confirmed
 * @property string $auth_key
 * @property string $password_hash
 * @property string $confirmation_token
 * @property string $bind_to_ip
 * @property string $registration_ip
 * @property integer $status
 * @property integer $superadmin
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends UserIdentity {

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_BANNED = -1;

    /**
     * @var string
     */
    public $gridRoleSearch;

    /**
     * @var string
     */
    public $password;
    public $asset_list;
    public $role_name; // added for bulk upload
    public $hl_access; // added for bulk upload
    public $repeat_password;
    protected $user;

    /**
     * Store result in singleton to prevent multiple db requests with multiple calls
     *
     * @param bool $fromSingleton
     *
     * @return static
     */
    public static function getCurrentUser($fromSingleton = true) {
        if (!$fromSingleton) {
            return static::findOne(Yii::$app->user->id);
        }

        $user = Singleton::getData('__currentUser');

        if (!$user) {
            $user = static::findOne(Yii::$app->user->id);

            Singleton::setData('__currentUser', $user);
        }

        return $user;
    }

    /**
     * Finds all users by assignment role
     *
     * @param  \yii\rbac\Role $role
     * @return static|null
     */
//written by PJ
    public static function findActiveUsersByRole($role) {

        if (gettype($role) == "array") {
            return static::find()
                            ->join('LEFT JOIN', 'auth_assignment', 'auth_assignment.user_id = id')
                            ->where(['IN', 'auth_assignment.item_name', $role])
                            ->andWhere(['status' => '1'])
                            ->orderBy('fullname')
                            ->all();
        } else {
            return static::find()
                            ->join('LEFT JOIN', 'auth_assignment', 'auth_assignment.user_id = id')
                            ->where(['auth_assignment.item_name' => $role])
                            ->andWhere(['status' => '1'])
                            ->orderBy('fullname')
                            ->all();
        }
    }

    /**
     * Assign role to user
     *
     * @param int  $userId
     * @param string $roleName
     *
     * @return bool
     */
    public static function assignRole($userId, $roleName) {
        try {
            Yii::$app->db->createCommand()
                    ->insert(Yii::$app->getModule('user-management')->auth_assignment_table, [
                        'user_id' => $userId,
                        'item_name' => $roleName,
                        'created_at' => time(),
                    ])->execute();

            AuthHelper::invalidatePermissions();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Revoke role from user
     *
     * @param int    $userId
     * @param string $roleName
     *
     * @return bool
     */
    public static function revokeRole($userId, $roleName) {
        $result = Yii::$app->db->createCommand()
                        ->delete(Yii::$app->getModule('user-management')->auth_assignment_table, ['user_id' => $userId, 'item_name' => $roleName])
                        ->execute() > 0;

        if ($result) {
            AuthHelper::invalidatePermissions();
        }

        return $result;
    }

    /**
     * @param string|array $roles
     * @param bool         $superAdminAllowed
     *
     * @return bool
     */
    public static function hasRole($roles, $superAdminAllowed = true) {
        if ($superAdminAllowed AND Yii::$app->user->isSuperadmin) {
            return true;
        }
        $roles = (array) $roles;

        AuthHelper::ensurePermissionsUpToDate();

        return array_intersect($roles, Yii::$app->session->get(AuthHelper::SESSION_PREFIX_ROLES, [])) !== [];
    }

    /**
     * @param string $permission
     * @param bool   $superAdminAllowed
     *
     * @return bool
     */
    public static function hasPermission($permission, $superAdminAllowed = true) {
        if ($superAdminAllowed AND Yii::$app->user->isSuperadmin) {
            return true;
        }

        AuthHelper::ensurePermissionsUpToDate();

        return in_array($permission, Yii::$app->session->get(AuthHelper::SESSION_PREFIX_PERMISSIONS, []));
    }

    /**
     * Useful for Menu widget
     *
     * <example>
     * 	...
     * 		[ 'label'=>'Some label', 'url'=>['/site/index'], 'visible'=>User::canRoute(['/site/index']) ]
     * 	...
     * </example>
     *
     * @param string|array $route
     * @param bool         $superAdminAllowed
     *
     * @return bool
     */
    public static function canRoute($route, $superAdminAllowed = true) {

        if ($superAdminAllowed AND Yii::$app->user->isSuperadmin) {
            return true;
        }



        $baseRoute = AuthHelper::unifyRoute($route);
// echo 'BaseRoute:'.$baseRoute[0].'<br>';

        if (Route::isFreeAccess($baseRoute[0])) {

            return true;
        }

        AuthHelper::ensurePermissionsUpToDate();

        $user_routes = Yii::$app->session->get(AuthHelper::SESSION_PREFIX_ROUTES);

        if ($user_routes) {
            foreach ($user_routes as &$user_route) {
                $user_route = Url::toRoute($user_route);
            }
            if (in_array($baseRoute[0], $user_routes)) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * getStatusList
     * @return array
     */
    public static function getStatusList() {
        return array(
            self::STATUS_ACTIVE => UserManagementModule::t('back', 'Active'),
            self::STATUS_INACTIVE => UserManagementModule::t('back', 'Inactive'),
            self::STATUS_BANNED => UserManagementModule::t('back', 'Banned'),
        );
    }

    /**
     * getStatusValue
     *
     * @param string $val
     *
     * @return string
     */
    public static function getStatusValue($val) {
        $ar = self::getStatusList();

        return isset($ar[$val]) ? $ar[$val] : $val;
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return Yii::$app->getModule('user-management')->user_table;
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }

    //returns roles of a User
    public static function getRolenameOfCurrentUser($user_id = NULL) {
        $rolenames = [];
        if ($user_id == NULL) {
            $user_id = Yii::$app->user->id;
        }
        $user_roles = Role::getUserRoles($user_id);

        if (count($user_roles) > 0) {
            $rolenames = ArrayHelper::getColumn($user_roles, 'name');
        }
        return $rolenames;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['username', 'fullname', 'email', 'mode_of_authentication'], 'required'],
            ['username', 'trim'],
            ['username', 'unique'],
            //['fullname', 'match', 'pattern' => '/^[a-zA-Z\s]+$/', 'message' => 'Only alphabets allowed'],
            ['email', 'email'],
            ['email', 'unique'],
            [['password', 'role_name', 'hl_access'], 'safe'],
            [['fullname', 'designation', 'username', 'mode_of_authentication'], 'string', 'max' => 100],
            [['mode_of_authentication'], 'in', 'range' => ['ad', 'database'], 'strict' => TRUE],
            [['status', 'email_confirmed', 'vendor_id'], 'integer', 'message' => 'Invalid value in {attribute}'],
            [['flag'], 'string', 'max' => 200],
            //[['registration_ip'], 'ip', 'ipv6' => FALSE],
            [['manager_id'], 'integer', 'message' => 'Invalid Manager selected'],
            [['designation', 'username'], 'match', 'pattern' => '/^[^<>\"\'=&]+$/', 'message' => 'Special characters like <>"\'=& are not allowed'],
            ['password', 'required', 'on' => ['changePassword']],
            ['password', 'trim', 'on' => ['newUser', 'changePassword', 'bulk_upload']],
            [['password', 'password_hash'], 'customMandatory', 'on' => 'bulk_upload'],
            ['password', 'match', 'pattern' => '/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/', 'message' => 'Password must be minimum 8 characters and contain atleast 1 Upper-case, 1 lower-case, 1-digit and 1-special character', 'on' => 'changePassword'],
            [['manager_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => User::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => \app\models\VendorCompanies::className(), 'targetAttribute' => ['vendor_id' => 'vendor_id']],
            [['fullname', 'designation'], \app\components\validators\SecurityValidator::className(), 'validation' => 'blackList1'],
            [['username'], \app\components\validators\SecurityValidator::className(), 'validation' => 'blackList'],
            [['status'], \app\components\validators\SecurityValidator::className()],
            [['fullname', 'designation'], \app\components\validators\SecurityValidator::className(), 'validation' => 'blackList1'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'username' => UserManagementModule::t('back', 'Username'),
            'superadmin' => UserManagementModule::t('back', 'Superadmin'),
            'confirmation_token' => 'Confirmation Token',
            'registration_ip' => UserManagementModule::t('back', 'Registration IP'),
            'bind_to_ip' => UserManagementModule::t('back', 'Bind to IP'),
            'status' => UserManagementModule::t('back', 'Status'),
            'gridRoleSearch' => UserManagementModule::t('back', 'Roles'),
            'created_at' => UserManagementModule::t('back', 'Created'),
            'updated_at' => UserManagementModule::t('back', 'Updated'),
            'password' => UserManagementModule::t('back', 'Password'),
            'repeat_password' => UserManagementModule::t('back', 'Repeat password'),
            'email_confirmed' => UserManagementModule::t('back', 'E-mail confirmed'),
            'email' => 'E-mail',
            'mode_of_authentication' => UserManagementModule::t('back', 'Mode of authentication'),
            'manager_id' => UserManagementModule::t('back', 'Manager'),
            'designation' => UserManagementModule::t('back', 'Designation'),
            'vendor_id' => UserManagementModule::t('back', 'Vendor'),
            'fullname' => UserManagementModule::t('back', 'Fullname'),
            'role_name' => UserManagementModule::t('back', 'Rolenames'),
            'hl_access' => UserManagementModule::t('back', 'Hierarchy Access'),
        ];
    }

    public function customMandatory() {
        if ($this->mode_of_authentication != 'ad') {
            $pattern = '/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/';
            if ($this->password == '') {
                $this->addError('password', 'Password cannot be blank');
            } else if (preg_match($pattern, $this->password) != 1) {
                $this->addError('password', 'Password must be minimum 8 characters and contain atleast 1 Upper-case, 1 lower-case, 1-digit and 1-special character');
            }
        }
    }

// get list of managers for a User
    public static function getUserManager() {
        $sql = 'SELECT * FROM user u INNER JOIN auth_assignment a on u.id=a.user_id WHERE (a.item_name!="IssueMgr" AND a.item_name!="Infcon" AND a.item_name!="Admin") and u.status!=-1 order by fullname';
        return User::findBySql($sql)->all();
    }

    public static function getManagerId($manager_name) {
        $user = User::find()->select('id')->where('fullname=:fullname', [':fullname' => $manager_name])->one();
        return $user;
    }

    public static function getConsultantNames($lead_id) {
        $user = User::find()->where('id=:id', [':id' => $lead_id])->one();

        if ($user) {
            if (User::hasPermission('AdminPer') or Yii::$app->user->isSuperadmin) {
                $sql = 'SELECT * FROM user u INNER JOIN auth_assignment a on u.id=a.user_id WHERE a.item_name="Infcon" and u.manager_id=' . $lead_id . ' and u.status=1';
            } else {
                $sql = 'SELECT * FROM user u INNER JOIN auth_assignment a on u.id=a.user_id WHERE a.item_name="Infcon" and u.manager_id=' . Yii::$app->user->id . ' and u.status=1';
            }
            if ($sql) {
                return User::findBySql($sql)->all();
            }
        }
        throw new NotFoundHttpException('No such user exists');
    }

    /**
     * Validate bind_to_ip attr to be in correct format
     */
    public function validateBindToIp() {
        if ($this->bind_to_ip) {
            $ips = explode(',', $this->bind_to_ip);

            foreach ($ips as $ip) {
                if (!filter_var(trim($ip), FILTER_VALIDATE_IP)) {
                    $this->addError('bind_to_ip', UserManagementModule::t('back', "Wrong format. Enter valid IPs separated by comma"));
                }
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles() {
        return $this->hasMany(Role::className(), ['name' => 'item_name'])
                        ->viaTable(Yii::$app->getModule('user-management')->auth_assignment_table, ['user_id' => 'id']);
    }

    public function getManager() {
        return $this->hasOne(self::classname(), ['id' => 'manager_id'])->
                        from(self::tableName() . ' AS manager');
    }

    public function getAssetPrimaryOwner() {
        return $this->hasOne(\app\models\Asset::className(), ['fk_asset_primary_owner_id' => 'id']);
    }

    public function getVendor() {
        return $this->hasOne(\app\models\VendorCompanies::className(), ['vendor_id' => 'vendor_id']);
    }

    public function getAssetIssueMgr() {
        return $this->hasOne(\app\models\Asset::className(), ['fk_asset_issue_mgr_id' => 'id']);
    }

    /**
     * Make sure user will not deactivate himself and superadmin could not demote himself
     * Also don't let non-superadmin edit superadmin
     *
     * @inheritdoc
     */
    public function beforeSave($insert) {

        if ($insert) {
            if (php_sapi_name() != 'cli') {
                $this->registration_ip = LittleBigHelper::getRealIp();
                $license_status = \app\components\utility\LicenseHandler::checkUserCount();
                if ($license_status == true) {

                    Yii::$app->session->setFlash('success', UserManagementModule::t('front', "User limit has been exceeded. Please contact Admin.."));
                    return false;
                }
            }
            $this->generateAuthKey();
        } else {
// Console doesn't have Yii::$app->user, so we skip it for console
            if (php_sapi_name() != 'cli') {
                if (Yii::$app->user->id == $this->id) {
// Make sure user will not deactivate himself
                    $this->status = static::STATUS_ACTIVE;

// Superadmin could not demote himself
                    if (Yii::$app->user->isSuperadmin AND $this->superadmin != 1) {
                        $this->superadmin = 1;
                    }
                }

// Don't let non-superadmin edit superadmin
                if (!Yii::$app->user->isSuperadmin AND $this->oldAttributes['superadmin'] == 1) {
                    return false;
                }
            }
        }

// If password has been set, than create password hash
        if ($this->password) {
            $this->setPassword($this->password);
        }

        return parent::beforeSave($insert);
    }

    /**
     * Don't let delete yourself and don't let non-superadmin delete superadmin
     *
     * @inheritdoc
     */
    public function beforeDelete() {
// Console doesn't have Yii::$app->user, so we skip it for console
        if (php_sapi_name() != 'cli') {
// Don't let delete yourself
            if (Yii::$app->user->id == $this->id) {
                return false;
            }

// Don't let non-superadmin delete superadmin
            if (!Yii::$app->user->isSuperadmin AND $this->superadmin == 1) {
                return false;
            }
        }

        return parent::beforeDelete();
    }

    public static function getUserFullnameAndEmailArrayFromUserIDs(array $user_ids) {
        $user_array = User::find()->where(['IN', 'id', $user_ids])->andWhere(['status' => '1'])->asArray()->all();
        $return_user_array = [];
        foreach ($user_array as $u) {
            $return_user_array[$u['email']] = $u['fullname'];
        }
        return $return_user_array;
    }

    public static function getUserFullnameLabelFromUserIDs(array $user_ids) {
        $user_array = User::find()->where(['IN', 'id', $user_ids])->andWhere(['status' => '1'])->asArray()->all();
        $return_user_label = '';
        foreach ($user_array as $u) {
            //$return_user_array[$u['email']] = $u['fullname'];
            $return_user_label .= '<div id="" class="col-md-12 tags" style="float:left"><span>' . $u['fullname'] . '</span></div>';
        }
        return $return_user_label;
    }

    public static function getUserFullnameFromUserID($user_id = NULL) {
        if ($user_id == NULL) {
            $user_id = Yii::$app->user->id;
        }
        $user = User::findOne($user_id);
        return $user->fullname;
    }

    public function getUserUrl() {
        return GhostHtml::a($this->fullname, yii\helpers\Url::toRoute(['/user-management/user/view', 'id' => $this->id]), ['target' => '_blank']);
    }

    public function sendChangePasswordEmail($user, $mail_content) {
//    return true;
        $send_email = new \app\models\VmapSendEmails();

        try {

            $to = $user->email;
            $subject = UserManagementModule::t('front', 'Your Password has been changed - ') . ' ' . Yii::$app->name;
            $body = $mail_content;
            $cc = '';

            $send_email->sendEmailFromVMAP($to, $cc, $subject, $body);

            return 'Password changed successfully';
        } catch (phpmailerException $e) {
            return Yii::warning("An error occurred. {$e->errorMessage()}");
        } catch (Exception $e) {
            return Yii::warning("An error occurred. {$send_email->ErrorInfo}");
        }
    }

    public function sendUserCreationEmail($user, $mail_content) {
//        return true;
        $send_email = new \app\models\VmapSendEmails();
        try {

            $to = $user->email;
            $subject = UserManagementModule::t('front', 'User Registration - ') . ' ' . Yii::$app->name;
            $body = $mail_content;
            $cc = '';

            $send_email->sendEmailFromVMAP($to, $cc, $subject, $body);

            return 'User added successfully';

            /*
              $val = Yii::$app->mailer->compose(Yii::$app->getModule('user-management')->mailerOptions[$viewFile], ['user' => $user])
              ->setFrom(Yii::$app->getModule('user-management')->mailerOptions['from'])
              ->setTo($user->email)
              ->setSubject(UserManagementModule::t('front', 'User Registration - ') . ' ' . Yii::$app->name)
              ->send();


              } catch (\Swift_TransportException $exception) {
              return Yii::warning('Email server not working. Emails could not be sent');
              }
             */
        } catch (phpmailerException $e) {
            return Yii::warning("An error occurred. {$e->errorMessage()}");
        } catch (Exception $e) {
            return Yii::warning("An error occurred. {$send_email->ErrorInfo}");
        }
    }

    public function sendUserNotificationEmail($user) {
        try {
            $viewFile = 'notificationEmailFormViewFile';

            $val = Yii::$app->mailer->compose(Yii::$app->getModule('user-management')->mailerOptions[$viewFile], ['user' => $user]);

            $to = $user->email;
            $subject = UserManagementModule::t('front', 'User Registration - ') . ' ' . Yii::$app->name;
            $body = $val;
            $cc = '';

            $send_email = new \app\models\VmapSendEmails();

            $send_email->sendEmailFromVMAP($to, $cc, $subject, $body);

            return 'User updated successfully';

            /*
              $transport = Credentials::getMailerCredentials();
              Yii::$app->mailer->setTransport($transport);

              $val = Yii::$app->mailer->compose(Yii::$app->getModule('user-management')->mailerOptions['notificationEmailFormViewFile'], ['user' => $user])
              ->setFrom(Yii::$app->getModule('user-management')->mailerOptions['from'])
              ->setTo($user->email)
              ->setSubject(UserManagementModule::t('front', 'User Update - ') . ' ' . Yii::$app->name)
              ->send();
              return $val;
             */
        } catch (\Swift_TransportException $exception) {
            return Yii::warning('Email server not working. Emails could not be sent');
        }
    }

    public function sendUserUpdateNotificationEmail($user) {
        try {
            $viewFile = 'updateEmailFormViewFile';

            $val = Yii::$app->mailer->compose(Yii::$app->getModule('user-management')->mailerOptions[$viewFile], ['user' => $user]);

            $to = $user->email;
            $subject = UserManagementModule::t('front', 'User Registration - ') . ' ' . Yii::$app->name;
            $body = $val;
            $cc = '';

            $send_email = new \app\models\VmapSendEmails();

            $send_email->sendEmailFromVMAP($to, $cc, $subject, $body);

            return 'User updated successfully';

            /*
              $transport = Credentials::getMailerCredentials();
              Yii::$app->mailer->setTransport($transport);

              $val = Yii::$app->mailer->compose(Yii::$app->getModule('user-management')->mailerOptions['updateEmailFormViewFile'], ['user' => $user])
              ->setFrom(Yii::$app->getModule('user-management')->mailerOptions['from'])
              ->setTo($user->email)
              ->setSubject(UserManagementModule::t('front', 'User Update - ') . ' ' . Yii::$app->name)
              ->send();
              return $val;
             */
        } catch (\Swift_TransportException $exception) {
            return Yii::warning('Email server not working. Emails could not be sent');
        }
    }

    public function hasAccess() {
        if (count(User::getRolenameOfCurrentUser($this->id)) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*    public function afterSave($insert, $changedAttributes) {
      parent::afterSave($insert, $changedAttributes);
      if (Yii::$app->controller->action->id != 'login' && Yii::$app->controller->action->id != 'logout') {
      \app\models\User::resetUserAccess($this->id);
      }
      }

      public function afterDelete() {
      if (Yii::$app->controller->action->id != 'login' && Yii::$app->controller->action->id != 'logout') {
      \app\models\User::resetUserAccess($this->id, [], 'remove');
      }
      parent::afterDelete();
      }
     */

    public function hasProfileSubmitted() {
        if ($this->profile_submit == 0) {
            return False;
        } else {
            return True;
        }
    }

}
