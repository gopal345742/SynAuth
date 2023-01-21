<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\BaseController;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use app\models\hierarchy\HierarchyLevels;
use yii\web\NotFoundHttpException;
use app\models\hierarchy\HLUserroleMapping;
use app\models\AuthAssignment;
use yii\helpers\Url;
use Yii;
use \app\models\hierarchy\HLUserAccess;
use app\models\hierarchy\HierarchyLevelsLinking;
use \app\helpers\GetLists_helper;
use \app\models\hierarchy\HLUserOwner;
use app\models\AssetGroup;
use app\models\hierarchy\AGUserOwner;
use app\helpers\CustomException;

class UserPermissionController extends BaseController {

    /**
     * @param int $id User ID
     *
     * @throws \yii\web\NotFoundHttpException
     * @return string
     */
    public function actionSet($id) {
        $user = User::findOne($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $permissionsByGroup = [];
        $permissions = Permission::find()
                ->andWhere([
                    Yii::$app->getModule('user-management')->auth_item_table . '.name' => array_keys(Permission::getUserPermissions($user->id))
                ])
                ->joinWith('group')
                ->all();

        foreach ($permissions as $permission) 
        {
            $permissionsByGroup[@$permission->group->name][] = $permission;
        }

        $condition_params['user_id'] = $id;
        $condition_params['hierarchy_type'] = 'BU';
        $hl_bu_acesslist = HLUserAccess::getHLAccessList($condition_params, 'id_name_array');
        $hl_ownerList = HLUserOwner::getHLList($condition_params, 'id_array',$hl_bu_acesslist);
        
        $hlOwnerData = GetLists_helper::includeSelection($hl_ownerList, $hl_bu_acesslist);

        $condition_params['hierarchy_type'] = 'TC';
        $hl_tc_acesslist = HLUserAccess::getHLAccessList($condition_params, 'id_name_array');
        $hl_managerList = HLUserOwner::getHLList($condition_params, 'id_array');
        $hlManagerData = GetLists_helper::includeSelection($hl_managerList, $hl_tc_acesslist);

        $condition_params['hierarchy_type'] = 'TC';
        $condition_params['access_type'] = 'manager';
        $ag_acesslist = AssetGroup::getAGList($condition_params, 'id_name_array');
        $ag_managerlist = AGUserOwner::getAGList($condition_params, 'id_array');
        $agManagerData = GetLists_helper::includeSelection($ag_managerlist, $ag_acesslist);

        $condition_params['access_type'] = 'owner';
        $ag_ownerlist = AGUserOwner::getAGList($condition_params, 'id_array');
        $agOwnerData = GetLists_helper::includeSelection($ag_ownerlist, $ag_acesslist);     
        
        HLUserOwner::deleteOwnership($id);
        //$hlManagerData = GetLists_helper::includeSelection($hl_managerList, $hl_tc_acesslist);
         \app\models\User::resetUserAccess($id);
        
        return $this->render('set', compact('user', 'permissionsByGroup', 'hlOwnerData', 'hlManagerData', 'agManagerData', 'agOwnerData'));
    }

    public function actionSaveHlowner($id) {
        $params = Yii::$app->request->post();
        if (isset($params['hl_id']) && !empty($params['hl_id'])) {
            
            foreach ($params['hl_id'] as $hl_ids) {
                $check_owner = HLUserOwner::find()->where(['hierarchy_type' => $params['hl_type'], 'fk_user_id' => $id, 'fk_hl_id' => $hl_ids])->asArray()->all();
  
                if($params['hl_type'] == 'BU')
                {
                    $condition_params['user_id'] = $id;
                    $condition_params['hierarchy_type'] = 'BU';
                    $hl_bu_acesslist = HLUserAccess::getHLAccessList($condition_params, 'id_array');
                
                }
                else
                {
                    $condition_params['user_id'] = $id;
                    $condition_params['hierarchy_type'] = 'TC';
                    $hl_bu_acesslist = HLUserAccess::getHLAccessList($condition_params, 'id_array');    
                }
                
                //$hl_ids = 10;
                
                if(in_array($hl_ids, $hl_bu_acesslist))
                {
                    if (empty($check_owner)) {
                        $model = new HLUserOwner();
                        $model->fk_hl_id = $hl_ids;
                        $model->fk_user_id = $id;
                        $model->hierarchy_type = $params['hl_type'];
                        $model->updated_by = Yii::$app->user->id;
                        $model->updated_on = date('Y-m-d H:i:s');
                        $model->save();
                    } else {
                        $existing_records = HLUserOwner::find()->where(['fk_user_id' => $id, 'hierarchy_type' => $params['hl_type']])->asArray()->all();
                        $hl_list = [];
                        foreach ($existing_records as $er) {
                            array_push($hl_list, $er['fk_hl_id']);
                        }

                        $diff = array_diff($hl_list, $params['hl_id']);

                        if (!empty($diff)) {
                            HLUserOwner::deleteAll(['fk_user_id' => $id, 'fk_hl_id' => $diff]);
                        }
                    }
                }
                else
                {
                    Yii::$app->session->setFlash('error', 'Invalid Hierarchy Level');
                }
                
            }
        }
        else if(isset($params['hl_id']) && empty($params['hl_id']))
        {
            
            HLUserOwner::deleteAll(['fk_user_id' => $id, 'hierarchy_type' => $params['hl_type']]); 
            Yii::$app->session->setFlash('success', 'Saved Successfully');
        }
        \app\models\User::resetUserAccess($id);
    }


    public function actionSaveAgowner($id) {
        $params = Yii::$app->request->post();
        if (isset($params['hl_id']) && !empty($params['hl_id']) && isset($params['access_type'])) {
            foreach ($params['hl_id'] as $hl_ids) {

                $check_owner = AGUserOwner::find()->where(['access_type' => $params['access_type'], 'fk_user_id' => $id, 'fk_ag_id' => $hl_ids])->asArray()->all();
                if($params['access_type'] == 'manager')
                {
                    $condition_params['user_id'] = $id;
                    $condition_params['hierarchy_type'] = 'TC';
                    $condition_params['access_type'] = 'manager';
                    $ag_acesslist = AssetGroup::getAGList($condition_params, 'id_array');
                
                }
                else
                {
                    $condition_params['user_id'] = $id;
                    $condition_params['hierarchy_type'] = 'TC';
                    $condition_params['access_type'] = 'owner';
                    $ag_acesslist = AssetGroup::getAGList($condition_params, 'id_array');    
                }
                
                //$hl_ids = 1000;
                
                if(in_array($hl_ids, $ag_acesslist))
                {
                    if (empty($check_owner)) 
                    {
                        $model = new AGUserOwner();
                        $model->fk_ag_id = $hl_ids;
                        $model->fk_user_id = $id;
                        $model->access_type = $params['access_type'];
                        $model->updated_by = Yii::$app->user->id;
                        $model->updated_on = date('Y-m-d H:i:s');
                        if($model->validate()) 
                        {
                            $model->save();
                        } 
                        else 
                        {
                            throw new CustomException('Error while saving');
                        }
                    } 
                    else 
                    {
                        $existing_records = AGUserOwner::find()->where(['fk_user_id' => $id, 'access_type' => $params['access_type']])->asArray()->all();

                        $hl_list = [];

                        foreach($existing_records as $er) 
                        {
                            array_push($hl_list, $er['fk_ag_id']);
                        }

                        $diff = array_diff($hl_list, $params['hl_id']);

                        if(!empty($diff))
                        {
                            AGUserOwner::deleteAll(['fk_user_id' => $id, 'fk_ag_id' => $diff]);
                        }
                    }
                }
                else
                {
                    Yii::$app->session->setFlash('error', 'Invalid Asset Group');
                }
                
                //print_r($check_owner);die;
                
            }
        }
        else if(isset($params['hl_id']) && empty($params['hl_id']))
        {
            AGUserOwner::deleteAll(['fk_user_id' => $id, 'access_type' => $params['access_type']]);
        }
        \app\models\User::resetUserAccess($id);
    }

    /**
     * @param int $id - User ID
     * Updated by Paresh Jain
     *
     * @return \yii\web\Response
     */
    public function actionSetRoles($id) {
        if (!Yii::$app->user->isSuperadmin AND Yii::$app->user->id == $id) {
            Yii::$app->session->setFlash('error', UserManagementModule::t('back', 'You can not change own permissions'));
            return $this->redirect(['set', 'id' => $id]);
        }

        $oldAssignments = array_keys(Role::getUserRoles($id));

        // To be sure that user didn't attempt to assign himself some unavailable roles
        if (Yii::$app->user->isSuperAdmin OR User::hasRole('Admin')) {
            $newAssignments = array_intersect(Role::getAvailableRoles(true, true), Yii::$app->request->post('roles', []));
        } else {
            $newAssignments = array_intersect(Role::getAvailableRoles(false, true), Yii::$app->request->post('roles', []));
        }

        $toAssign = array_diff($newAssignments, $oldAssignments);
        $toRevoke = array_diff($oldAssignments, $newAssignments);

        foreach ($toAssign as $role) {
            User::assignRole($id, $role);
        }
        /*
          echo "<pre>";
          print_r($toRevoke);
          print_r($toAssign);die;
         * *
         */

        if (count($toAssign) > 0 || count($toRevoke) > 0) {
            foreach ($toRevoke as $role) {
                //User::revokeRole($id, $role);
                
                $user_role = AuthAssignment::find()->where(['user_id'=>$id,'item_name'=>$role])->asArray()->one();
                
                if(count($user_role) > 0)
                {
                    $this->actionRevokeRole($user_role['userrole_id'], true);
                }
                
            }
            Yii::$app->session->setFlash('success', UserManagementModule::t('back', 'Saved'));
        } else {
            Yii::$app->session->setFlash('error', UserManagementModule::t('back', 'Please assign valid Role to the User'));
        }
        \app\models\User::resetUserAccess($id);
        return $this->redirect(['set', 'id' => $id]);
    }

    public function actionLoadLevels($id) {

        $params = Yii::$app->request->get();

        $records = HierarchyLevels::getHierarchy(['hierarchy_type' => $params['hl_type']]);
        $result = '';
        if ($params['hl_id'] == 'all') {
            $result = \app\helpers\HierarchyLevel_helper::getUserHierarchyLevels($records, $params);
        } else {
            foreach ($records as $val) {

                if ($val['id'] == $params['hl_id']) {
                    $result = \app\helpers\HierarchyLevel_helper::getUserHierarchyLevels($val, $params);
                }
            }
        }

        echo $result;
    }

    public function actionSaveRoles($id) {
        $params = Yii::$app->request->post();

        $errors = [];

        $hl_type = 'BU';

        if (isset($params['HL']) && !empty($params['HL'])) {

            $child_hl = [];

            foreach ($params['HL'] as $val) {
                //echo $val;die;
                if ($val != 'all_tc' && $val != 'all_bu') {
                    $check_child = HierarchyLevels::getChildLevels('id_array', $val);

                    if (!empty($check_child) && is_array($check_child)) {
                        foreach ($check_child as $each_child) {
                            $child_hl[] = $each_child['id'];
                        }
                    }
                }
            }
            //print_r($child_hl);die;
            $diff = array_diff($params['HL'], $child_hl);

            /*
            echo "<pre>";
            print_r($params['HL']);
            print_r($child_hl);
            print_r($diff);die;
             * 
             */
            
            $result = HLUserAccess::saveAccess($diff, $id); //die;
            \app\models\User::resetUserAccess($id);

            if ($result == 1) {
                Yii::$app->session->setFlash('success', UserManagementModule::t('back', 'Access Granted Successfully'));

                $this->redirect(Url::to(['user-permission/set', 'id' => $id]));
            } else {
                Yii::$app->session->setFlash('error', UserManagementModule::t('back', 'Access Not Saved'));

                return $this->redirect(['user-permission/user-access', 'id' => $id]);
            }
        } else {
            HLUserAccess::deleteAll(['fk_user_id' => $id]);

            Yii::$app->session->setFlash('success', UserManagementModule::t('back', 'No Access Given'));

            return $this->redirect(['user-permission/set', 'id' => $id]);
        }
    }

    public function actionSetPermission($id) {
        $user = User::findOne($id);

        //print_r($user);die;

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $permissionsByGroup = [];
        $permissions = Permission::find()
                ->andWhere([
                    Yii::$app->getModule('user-management')->auth_item_table . '.name' => array_keys(Permission::getUserPermissions($user->id))
                ])
                ->joinWith('group')
                ->all();

        foreach ($permissions as $permission) {
            $permissionsByGroup[@$permission->group->name][] = $permission;
        }

        //$user_model = \app\models\User::findModel($id);

        return $this->render('setpermission', compact('user', 'permissionsByGroup', 'user'));
    }



    public function assign() {
        $tree = \app\helpers\HierarchyLevel_helper::getHierarchyLevels1();
    }

    public function actionRevokeRole($id, $revoke_multiple = false) 
    {
        $model = $this->findModel($id);
        
        if($model->item_name == 'AstGrpOwner')
        {
            $access_type = 'owner';
        }
        else if($model->item_name == 'AstGrpManager')
        {
            $access_type = 'manager';
        }
        else if($model->item_name == 'HLOwner')
        {
            $access_type = 'BU';
        }
        else if($model->item_name == 'HLManager')
        {
            $access_type = 'TC';
        }
        
        $user_id = $model->user_id;
        
        if ($model->delete()) 
        {
            if(isset($access_type))
            {
                if($access_type == 'owner' || $access_type == 'manager')
                {
                    AGUserOwner::deleteAll(['fk_user_id' => $user_id, 'access_type' => $access_type]);
                }
 
                if($access_type == 'BU' || $access_type == 'TC')
                {
                    HLUserOwner::deleteAll(['fk_user_id' => $user_id, 'hierarchy_type' => $access_type]);
                }
            }
            \app\models\User::resetUserAccess($user_id);
            Yii::$app->session->setFlash('success', UserManagementModule::t('back', 'Role Deleted Successfully'));  
        } 
        else 
        {
            Yii::$app->session->setFlash('error', UserManagementModule::t('back', 'Role not Deleted'));
            
        }
        
        if($revoke_multiple == false)
        {
            return $this->redirect(['/user-management/user-permission/set', 'id' => $user_id]);
        }
        
    }

    protected function findModel($id) {
        if (($model = AuthAssignment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionUserAccess($id) {
        $helper = new \app\helpers\GetLists_helper();
        $user_model = User::findOne($id);

        if ($user_model) {
            $data = Yii::$app->request->post();

            if (!empty($data)) {
                
            }

            $params['hl_type'] = 'TC';

            $tc_records = HierarchyLevels::getHierarchy(['hierarchy_type' => 'TC']);

            $bu_records = HierarchyLevels::getHierarchy(['hierarchy_type' => 'BU']);

            /*
              if($params['hl_id'] == 'all')
              {
              $result = \app\helpers\HierarchyLevel_helper::getUserHierarchyLevels($records, $params);
              }
             */
            return $this->render('user-access', [
                        'user' => $user_model,
                        'tc_records' => $tc_records,
                        'bu_records' => $bu_records,
            ]);
        } else {
            Yii::$app->session->setFlash('error', UserManagementModule::t('front', 'Invalid User'));
        }
    }

}