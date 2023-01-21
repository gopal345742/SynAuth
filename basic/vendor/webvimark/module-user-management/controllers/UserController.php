<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\AdminDefaultController;
use Yii;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\models\search\UserSearch;
use yii\web\NotFoundHttpException;
use webvimark\modules\UserManagement\components\UserAuthEvent;
use yii\web\Response;
use yii\bootstrap4\ActiveForm;
use webvimark\modules\UserManagement\UserManagementModule;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AdminDefaultController {

    /**
     * @var Userc
     */
    public $modelClass = 'webvimark\modules\UserManagement\models\User';

    /**
     * @var UserSearch
     */
    public $modelSearchClass = 'webvimark\modules\UserManagement\models\search\UserSearch';

    /**
     * @return mixed|string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new User(['scenario' => 'newUser']);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            //print_r(Yii::$app->request->post());die;
            if ($model->mode_of_authentication != 'ad') {
                $model->created_at = date('Y-m-d');
                $model->superadmin = 0;
                $model->password = $model->repeat_password = uniqid('', false);
                if ($model->save(false)) {
                    $mail_content = $this->renderPartial('/mail/emailConfirmationMail', ['user' => $model]);
                    $model->sendUserCreationEmail($model,$mail_content);
                    return $this->redirect(['user-permission/user-access', 'id' => $model->id, 'redirect_from' => 'create']);
                }
            } else {
                $model->created_at = date('Y-m-d');
                $model->superadmin = 0;
                $model->password_hash = '';
                $model->email_confirmed = 1;
                if ($model->save()) {
                    $mail_content = $this->renderPartial('/mail/confirmationMail_AD', ['user' => $model]);

                    $model->sendUserCreationEmail($model,$mail_content);
                    return $this->redirect(['user-permission/user-access', 'id' => $model->id, 'redirect_from' => 'create']);
                }
            }
        }

        return $this->renderIsAjax('create', compact('model'));
    }

    public function actionUserRole($id) {
        echo \yii\helpers\Html::encode($id);
    }

    public function actionMode() {

        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $mode_of_auth = $data['id'];
            $email = $data['email'];

            $ldaphost = Yii::$app->params['ad_host'];
            $ldapconn = ldap_connect($ldaphost)
                    or die("Could not connect to $ldaphost");

            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

            $ldap_user = Yii::$app->params['ad_user'];

            $ldap_pass = trim(decryptstring($GLOBALS["ad_password"]));

            $ldap_pass = str_replace("\0", '', $ldap_pass);

            $r = ldap_bind($ldapconn, $ldap_user, $ldap_pass);

            if ($r) {

                $sr = ldap_search($ldapconn, Yii::$app->params['ad_basedn'], "(mail=" . $email . ")");

                $info = ldap_get_entries($ldapconn, $sr);

                if (!empty($info[0])) {
                    $designation = (isset($info[0]['title'])) ? $info[0]['title'][0] : "";
                    $full_name = (isset($info[0]['cn'])) ? $info[0]['cn'][0] : "";
                    $manager_column = (isset($info[0]['manager'])) ? $info[0]['manager'][0] : "";
                    $username = (isset($info[0]['samaccountname'])) ? $info[0]['samaccountname'][0] : "";

                    $manager_id = "";
                    if ($manager_column != "") {
                        preg_match("/={0}(\w+)\s(\w+),{0}/", $manager_column, $match);

                        if (count($match) > 0) {
                            //following code used for manager name to manager id:
                            $manager = \app\models\User::getManagerId($match[0]);
                            $manager_id = (isset($manager)) ? $manager->id : "";
                        }
                    }

                    echo $data = 'designation=' . $designation . '&fullname=' . $full_name . '&manager_id=' . $manager_id . '&email=' . $username;
                }
            }
        }
    }

    /**
     * @param int $id User ID
     *
     * @throws \yii\web\NotFoundHttpException
     * @return string
     */
    public function actionChangePassword($id) {
        $user = User::findOne($id);


        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        $user->scenario = 'changePassword';

        if ($user->id == 1) {

            if (Yii::$app->user->isSuperAdmin) {
                if ($user->mode_of_authentication == '' || $user->mode_of_authentication != 'ad') {
                    if ($user->load(Yii::$app->request->isPost)) {
                        if ($user->load(Yii::$app->request->post()) AND $user->save(FALSE)) {
                            if ($this->triggerModuleEvent(UserAuthEvent::BEFORE_EMAIL_CHANGE_USER_PASSWORD, ['user' => $user])) {

                                $mail_content = $this->renderPartial('/mail/changePasswordMail', ['user' => $user]);

                                if ($user->sendChangePasswordEmail($user, $mail_content)) {
                                    if ($this->triggerModuleEvent(UserAuthEvent::AFTER_EMAIL_CHANGE_USER_PASSWORD, ['user' => $user])) {

                                        $user->email_confirmed = 0;
                                        $user->updated_at = date('Y-m-d');
                                        $user->save(FALSE);
                                        Yii::$app->session->setFlash('success', UserManagementModule::t('front', "Password Changed Successfully"));
                                        return $this->redirect(['view', 'id' => $user->id]);
                                    }
                                } else {
                                    Yii::$app->session->setFlash('error', UserManagementModule::t('front', "Could not connect to email server. Please try after some time"));
                                }
                            }
                        } else {
                            Yii::$app->session->setFlash('error', UserManagementModule::t('front', "Invalid Password. Password must be minimum 8 digits and should contain one uppercase, one lowercase, and one digit or special character"));
                        }
                    }
                } else {
                    Yii::$app->session->setFlash('error', UserManagementModule::t('front', "Not allowed for Active Directory users"));
                }
                return $this->renderIsAjax('changePassword', compact('user'));
            }
        } else {
            if ($user->mode_of_authentication == '' || $user->mode_of_authentication != 'ad') {
                if ($user->load(Yii::$app->request->post()) AND $user->save(FALSE)) {
                    if ($this->triggerModuleEvent(UserAuthEvent::BEFORE_EMAIL_CHANGE_USER_PASSWORD, ['user' => $user])) {


                        $mail_content = $this->renderPartial('/mail/changePasswordMail', ['user' => $user]);

                        if ($user->sendChangePasswordEmail($user, $mail_content)) {
                            if ($this->triggerModuleEvent(UserAuthEvent::AFTER_EMAIL_CHANGE_USER_PASSWORD, ['user' => $user])) {

                                $user->email_confirmed = 0;
                                $user->updated_at = date('Y-m-d');
                                $user->superadmin = 0;
                                $user->save(FALSE);
                                Yii::$app->session->setFlash('success', UserManagementModule::t('front', "Password Changed Successfully"));
                                return $this->redirect(['view', 'id' => $user->id]);
                            }
                        } else {
                            Yii::$app->session->setFlash('error', UserManagementModule::t('front', "Could not connect to email server. Please try after some time"));
                        }
                    }
                } else {
                    //Yii::$app->session->setFlash('error', UserManagementModule::t('front', "Invalid Password. Password must be minimum 8 digits and should contain one uppercase, one lowercase, and one digit or special character"));
                }
            } else {
                Yii::$app->session->setFlash('error', UserManagementModule::t('front', "Not allowed for Active Directory users"));
            }
            return $this->renderIsAjax('changePassword', compact('user'));
        }
    }

    public function actionUpdate($id) {
        $model = $this->findModel($id);
        if ($model->id == 1) {
            if (Yii::$app->user->isSuperadmin) {
                if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($model);
                }
                $data = Yii::$app->request->post();
                if (!empty($data)) {
                    $changemode = $data['User']['mode_of_authentication'];
                }
                if (!empty($changemode)) {
                    if ($model->mode_of_authentication == 'database' && $changemode == 'ad') {
                        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
                            $model->password_hash = '';
                            $model->updated_at = date('Y-m-d');
                            $model->superadmin = 1;
                            $model->email_confirmed = 1;
                            $model->save();
                            $model->sendUserNotificationEmail($model);
                            return $this->redirect($this->getRedirectPage('update', $model));
                        }
                    } elseif ($model->mode_of_authentication == 'ad' && $changemode == 'database') {
                        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
                            $model->updated_at = date('Y-m-d');
                            $model->superadmin = 1;
                            $model->password = $model->repeat_password = uniqid('', false);
                            $model->email_confirmed = 0;
                            $model->flag = 1;
                            $model->save();
                            $model->sendUserUpdateNotificationEmail($model);
                            return $this->redirect($this->getRedirectPage('update', $model));
                        }
                    } else {
                        if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
                            $model->updated_at = date('Y-m-d');
                            $model->superadmin = 1;
                            $model->save();
                            return $this->redirect($this->getRedirectPage('update', $model));
                        }
                    }
                }
                return $this->renderIsAjax('update', compact('model'));
            }
        } else {
            if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            $data = Yii::$app->request->post();
            if (!empty($data)) {
                $changemode = $data['User']['mode_of_authentication'];
            }
            if (!empty($changemode)) {
                if ($model->mode_of_authentication == 'database' && $changemode == 'ad') {
                    if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
                        $model->password_hash = '';
                        $model->updated_at = date('Y-m-d');
                        $model->superadmin = 0;
                        $model->email_confirmed = 1;
                        $model->save();
                        $model->sendUserNotificationEmail($model);
                        return $this->redirect($this->getRedirectPage('update', $model));
                    }
                } elseif ($model->mode_of_authentication == 'ad' && $changemode == 'database') {
                    if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
                        $model->updated_at = date('Y-m-d');
                        $model->superadmin = 0;
                        $model->password = $model->repeat_password = uniqid('', false);
                        $model->email_confirmed = 0;
                        $model->flag = 1;
                        $model->save();
                        $model->sendUserUpdateNotificationEmail($model);
                        return $this->redirect($this->getRedirectPage('update', $model));
                    }
                } else {
                    if ($model->load(Yii::$app->request->post()) AND $model->validate()) {
                        $model->updated_at = date('Y-m-d');
                        $model->superadmin = 0;
                        $model->save();
                        return $this->redirect($this->getRedirectPage('update', $model));
                    }
                }
            }

            return $this->renderIsAjax('update', compact('model'));
        }
    }

    protected function triggerModuleEvent($eventName, $data = []) {
        $event = new UserAuthEvent($data);

        $this->module->trigger($eventName, $event);

        return $event->isValid;
    }

    //It truncates user_asset_access table and adds all user entries again
    public function actionResetUserAccess($uid = NULL) {
        try {
            if ($uid != NULL) {
                $model = $this->findModel($uid);
            }

            \app\models\User::resetUserAccess($uid);
            Yii::$app->session->setFlash('success', UserManagementModule::t('front', 'User access has been set successfully'));
        } catch (\yii\db\Exception $e) {
            Yii::error($e, 'db_error');
            throw new \app\helpers\CustomException($e->errorInfo);
        } catch (\Exception $e) {
            Yii::error($e, 'app_error');
            throw new \app\helpers\CustomException($e->getMessage());
        } catch (NotFoundHttpException $e) {
            throw new \app\helpers\CustomException($e->getMessage());
        }
        return $this->redirect(Yii::$app->request->referrer, 302);
    }

    //action soft delete
    /* public function actionSoftdelete($id) {

      $model = $this->findModel($id);
      $model->delete();

      $redirect = $this->getRedirectPage('delete', $model);

      return $redirect === false ? '' : $this->redirect($redirect);

      } */

    //action active
    public function actionActive($id) {

        $model = $this->findModel($id);
        $model->status = 1;
        $model->updateAttributes(['status']);


        Yii::$app->session->setFlash('success', UserManagementModule::t('front', $model->fullname . ' marked as Active'));
        return $this->redirect(['user/view', 'id' => $id]);

        // return $this->redirect(['index']);

        /* $searchModel  = $this->modelSearchClass ? new $this->modelSearchClass : null;

          if ( $searchModel )
          {
          $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
          }
          else
          {
          $modelClass = $this->modelClass;
          $dataProvider = new ActiveDataProvider([
          'query' => $modelClass::find(),
          ]);
          }

          return $this->renderIsAjax('index', compact('dataProvider', 'searchModel')); */
    }

    //action inactive
    public function actionInactive($id) {


        $model = $this->findModel($id);

        if ($model->superadmin !== 1) {

            $model->status = 0;
            $model->updateAttributes(['status']);

            Yii::$app->session->setFlash('success', UserManagementModule::t('front', $model->fullname . ' marked as Inactive'));

            //return $this->redirect(['index']);

            /* $searchModel  = $this->modelSearchClass ? new $this->modelSearchClass : null;

              if ( $searchModel )
              {
              $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
              }
              else
              {
              $modelClass = $this->modelClass;
              $dataProvider = new ActiveDataProvider([
              'query' => $modelClass::find(),
              ]);
              }

              return $this->renderIsAjax('index', compact('dataProvider', 'searchModel')); */
        } else {

            Yii::$app->session->setFlash('error', UserManagementModule::t('front', 'Superadmin cannot be marked as Inactive'));
        }

        return $this->redirect(['user/view', 'id' => $id]);
    }
    
    public function actionView($id, $render = false) {
        if ($render == false) {
            return $this->renderIsAjax('view', [
                        'model' => $this->findModel($id),
            ]);
        } else {
            return $this->render('view', [
                        'model' => $this->findModel($id),
            ]);
        }
    }

}
