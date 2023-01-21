<?php

namespace webvimark\modules\UserManagement\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * UserSearch represents the model behind the search form about `webvimark\modules\UserManagement\models\User`.
 */
class UserSearch extends User {

    public function rules() {
        return [
            [['id', 'superadmin', 'status', 'created_at', 'updated_at', 'email_confirmed', 'manager_id'], 'integer'],
            [['username', 'gridRoleSearch', 'registration_ip', 'email', 'fullname', 'designation'], 'string'],
                //  ['managerName', 'safe'],
        ];
    }

    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params) {
        $query = User::find();

        $query->with(['roles']);

        if (isset($params['id']) && is_array($params['id'])) {
            $query->where(['id' => $params['id']]);
        } 
        else if (isset($params['not_in_id']) && is_array($params['not_in_id'])) {
            $query->where(['NOT IN','id', $params['not_in_id']]);
        } 
        else {
            if (Yii::$app->user->isSuperadmin) {
                $query->all();
            } else if (User::hasPermission('AdminPer')) {
                $query->where(['superadmin' => 0]);
            } else {
                $query->where(['manager_id' => Yii::$app->user->id]);
            }
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->cookies->getValue('_grid_page_size', 20),
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ],
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ($this->gridRoleSearch) {
            $query->joinWith(['roles']);
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'superadmin' => $this->superadmin,
            'status' => $this->status,
            Yii::$app->getModule('user-management')->auth_item_table . '.name' => $this->gridRoleSearch,
            'registration_ip' => $this->registration_ip,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'email_confirmed' => $this->email_confirmed,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
                ->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'fullname', $this->fullname]);

        return $dataProvider;
    }

    protected function addCondition($query, $attribute, $partialMatch = false) {
        if (($pos = strrpos($attribute, '.')) !== false) {
            $modelAttribute = substr($attribute, $pos + 1);
        } else {
            $modelAttribute = $attribute;
        }

        $value = $this->$modelAttribute;
        if (trim($value) === '') {
            return;
        }

        /*
         * The following line is additionally added for right aliasing
         * of columns so filtering happen correctly in the self join
         */
        $attribute = "tbl_person.$attribute";

        if ($partialMatch) {
            $query->andWhere(['like', $attribute, $value]);
        } else {
            $query->andWhere([$attribute => $value]);
        }
    }

}
