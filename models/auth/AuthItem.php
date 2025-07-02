<?php
namespace app\models\auth;

use Yii;
use app\models\user\User;
use app\models\auth\AuthItemGroup;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property int $type
 * @property string $description Descripción
 * @property int $grupo_id Grupo
 * @property string $subgrupo Sub grupo
 * @property string $accion Acción
 * @property int $orden Orden
 * @property string $rule_name
 * @property string $data
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property AuthAssignment[] $authAssignments
 * @property User[] $users
 * @property User $createdBy
 * @property AuthItemGroup $grupo
 * @property User $updatedBy
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $parents
 * @property AuthItem[] $children
 */
class AuthItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'grupo_id', 'orden', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 1024],
            [['subgrupo'], 'string', 'max' => 32],
            [['accion'], 'string', 'max' => 20],
            [['name'], 'unique'],
            [['created_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::className(),
                'targetAttribute' => ['created_by' => 'id'],
                'when' => function ($model) {
                    return $model->created_by;
                },
            ],
            [['grupo_id'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItemGroup::className(), 'targetAttribute' => ['grupo_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Type',
            'description' => 'Descripción',
            'grupo_id' => 'Grupo',
            'subgrupo' => 'Sub grupo',
            'accion' => 'Acción',
            'orden' => 'Orden',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'updated_at' => 'Modificado',
            'updated_by' => 'Modificado por',
        ];
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//
    /*
        public function getAuthAssignments()
        {
            return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
        }

        public function getUsers()
        {
            return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('auth_assignment', ['item_name' => 'name']);
        }

        public function getGrupo()
        {
            return $this->hasOne(AuthItemGroup::className(), ['id' => 'grupo_id']);
        }

        public function getAuthItemChildren()
        {
            return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
        }

        public function getAuthItemChildren0()
        {
            return $this->hasMany(AuthItemChild::className(), ['parent' => 'name']);
        }

        public function getParents()
        {
            return $this->hasMany(AuthItem::className(), ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
        }

        public function getChildren()
        {
            return $this->hasMany(AuthItem::className(), ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
        }
    */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }


//------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//
    public static function getRoles()
    {
        // if user is not 'theCreator' ( You ), we do not want to show him users with that role
        if (!Yii::$app->user->can('theCreator')) {
            return static::find()->select('name')->where(['type' => 1])->andWhere(['!=', 'name', 'theCreator'])->all();
        }

        // this is You or some other super admin, so show everything 
        return static::find()->select('name')->where(['type' => 1])->all(); 
    } 

    /**
     * Returns roles.
     * NOTE: used in user/index and user/update.
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getItems($params = [])
    {
        $params['type'] = array_key_exists('type', $params)? $params['type']: false;

        $AuthItem = static::find()
                ->select('name')
                ->orderBy('name');

        if(strlen($params['type']))
            $AuthItem->andWhere(['type' => $params['type']]);

        // if user is not 'theCreator' ( You ), we do not want to show him users with that role
        /*
        if(!Yii::$app->user->can('theCreator'))
            $AuthItem->andWhere(['!=', 'name', 'theCreator']);
        */

        //echo $AuthItem->createCommand()->rawSql;

        return $AuthItem->all();
    } 


//------------------------------------------------------------------------------------------------//
// ACTIVE RECORD
//------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)){
            if($insert){
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }else{
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }

            return true;
        }
    }

}
