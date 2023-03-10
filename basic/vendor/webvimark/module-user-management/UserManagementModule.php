<?php

namespace webvimark\modules\UserManagement;

use Yii;
use yii\helpers\ArrayHelper;

class UserManagementModule extends \yii\base\Module {

    const SESSION_LAST_ATTEMPT = '_um_last_attempt';
    const SESSION_ATTEMPT_COUNT = '_um_attempt_count';

    /**
     * If set true, then on registration username will be validated as email
     *
     * @var bool
     */
    public $useEmailAsLogin = true;

    /**
     * Works only if $useEmailAsLogin = true
     *
     * If set true, then on after registration message with activation code will be sent
     * to user email and after confirmation user status will be "active"
     *
     * @var bool
     * @see $useEmailAsLogin
     */
    public $emailConfirmationRequired = true;

    /**
     * Params for mailer
     * They will be merged with $_defaultMailerOptions
     *
     * @var array
     * @see $_defaultMailerOptions
     */
    public $mailerOptions = [];

    /**
     * Default options for mailer
     *
     * @var array
     */
    protected $_defaultMailerOptions = [
        'from' => '', // If empty it will be - [Yii::$app->params['adminEmail'] => Yii::$app->name . ' robot']
        'registrationFormViewFile' => '/mail/registrationEmailConfirmation',
        'passwordRecoveryFormViewFile' => '/mail/passwordRecoveryMail',
        'confirmEmailFormViewFile' => '/mail/emailConfirmationMail',
        'changePasswordFile' => '/mail/changePasswordMail',
        'notificationEmailFormViewFile' => '/mail/updateNotificationMail',
        'updateEmailFormViewFile' => '/mail/updateUserNotificationMail',
        'ADUserRegistrationFile' => '/mail/confirmationMail_AD',
    ];

    /**
     * Permission that will be assigned automatically for everyone, so you can assign
     * routes like "site/index" to this permission and those routes will be available for everyone
     *
     * Basically it's permission for guests (and of course for everyone else)
     *
     * @var string
     */
    public $commonPermissionName = 'commonPermission';

    /**
     * You can define your own registration form class here.
     * Together with theming registration view file you can ultimately customize registration process.
     *
     * See AuthController::actionRegistration() and RegistrationForm how they work together
     *
     * @var string
     */
    public $registrationFormClass = 'webvimark\modules\UserManagement\models\forms\RegistrationForm';

    /**
     * After how many seconds confirmation token will be invalid
     *
     * @var int
     */
    public $confirmationTokenExpire = 3600; // 1 hour

    /**
     * Roles that will be assigned to user registered via user-management/auth/registration
     *
     * @var array
     */
    public $rolesAfterRegistration = [];

    /**
     * Pattern that will be applied for names on registration.
     * Default pattern allows user enter only numbers and letters.
     *
     * This will not be used if $useEmailAsLogin set as true !
     *
     * @var string
     */
    public $registrationRegexp = '/^(\w|\d)+$/';

    /**
     * Pattern that will be applied for names on registration. It contain regexp that should NOT be in username
     * Default pattern doesn't allow anything having "admin"
     *
     * This will not be used if $useEmailAsLogin set as true !
     *
     * @var string
     */
    public $registrationBlackRegexp = '/^(.)*admin(.)*$/i';

    /**
     * How much attempts user can made to login or recover password in $attemptsTimeout seconds interval
     *
     * @var int
     */
    public $maxAttempts = 5;

    /**
     * Number of seconds after attempt counter to login or recover password will reset
     *
     * @var int
     */
    public $attemptsTimeout = 60;

    /**
     * Options for registration and password recovery captcha
     *
     * @var array
     */
    public $captchaOptions = [
        'class' => 'yii\captcha\CaptchaAction',
        'minLength' => 5,
        'maxLength' => 7,
        'offset' => 5
    ];

    /**
     * Table aliases
     *
     * @var string
     */
    public $user_table = '{{%user}}';
    public $user_visit_log_table = '{{%user_visit_log}}';
    public $auth_item_table = '{{%auth_item}}';
    public $auth_item_child_table = '{{%auth_item_child}}';
    public $auth_item_group_table = '{{%auth_item_group}}';
    public $auth_assignment_table = '{{%auth_assignment}}';
    public $auth_rule_table = '{{%auth_rule}}';
    public $controllerNamespace = 'webvimark\modules\UserManagement\controllers';

    /**
     * @p
     */
    public function init() {
        parent::init();

        $this->prepareMailerOptions();
    }

    /**
     * For Menu
     *
     * @return array
     */
    public static function menuItems() {
        return [
            ['label' => '<i class="fa fa-angle-double-right"></i> ' . UserManagementModule::t('back', 'Users'), 'url' => ['/user-management/user/index']],
            ['label' => '<i class="fa fa-angle-double-right"></i> ' . UserManagementModule::t('back', 'Roles'), 'url' => ['/user-management/role/index']],
            ['label' => '<i class="fa fa-angle-double-right"></i> ' . UserManagementModule::t('back', 'Permissions'), 'url' => ['/user-management/permission/index']],
            ['label' => '<i class="fa fa-angle-double-right"></i> ' . UserManagementModule::t('back', 'Permission groups'), 'url' => ['/user-management/auth-item-group/index']],
            ['label' => '<i class="fa fa-angle-double-right"></i> ' . UserManagementModule::t('back', 'Visit log'), 'url' => ['/user-management/user-visit-log/index']],
        ];
    }

    /**
     * I18N helper
     *
     * @param string      $category
     * @param string      $message
     * @param array       $params
     * @param null|string $language
     *
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null) {
        if (!isset(Yii::$app->i18n->translations['modules/user-management/*'])) {
            Yii::$app->i18n->translations['modules/user-management/*'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@vendor/webvimark/module-user-management/messages',
                'fileMap' => [
                    'modules/user-management/back' => 'back.php',
                    'modules/user-management/front' => 'front.php',
                ],
            ];
        }

        $return_val = Yii::t('modules/user-management/' . $category, $message, $params, $language);
        
        return $return_val;
    }

    /**
     * Check how much attempts user has been made in X seconds
     *
     * @return bool
     */
    public function checkAttempts() {
        $lastAttempt = Yii::$app->session->get(static::SESSION_LAST_ATTEMPT);

        if ($lastAttempt) {
            $attemptsCount = Yii::$app->session->get(static::SESSION_ATTEMPT_COUNT, 0);

            Yii::$app->session->set(static::SESSION_ATTEMPT_COUNT, ++$attemptsCount);

            // If last attempt was made more than X seconds ago then reset counters
            if (( $lastAttempt + $this->attemptsTimeout ) < time()) {
                Yii::$app->session->set(static::SESSION_LAST_ATTEMPT, time());
                Yii::$app->session->set(static::SESSION_ATTEMPT_COUNT, 1);

                return true;
            } elseif ($attemptsCount > $this->maxAttempts) {
                return false;
            }

            return true;
        }

        Yii::$app->session->set(static::SESSION_LAST_ATTEMPT, time());
        Yii::$app->session->set(static::SESSION_ATTEMPT_COUNT, 1);

        return true;
    }

    /**
     * Merge given mailer options with default
     */
    protected function prepareMailerOptions() {
        $transport = \app\models\Credentials::find()->where(['name' => 'username'])->one();
  
        $from = ($transport['value'] != NULL) ? $transport['value'] : Yii::$app->params['adminEmail'];

        if (!isset($this->mailerOptions['from'])) {
            $name = \app\models\Credentials::find()->where(['name' => 'name'])->one();
            $this->mailerOptions['from'] = [$from => $name['value']];
        }

        $this->mailerOptions = ArrayHelper::merge($this->_defaultMailerOptions, $this->mailerOptions);
    }
}