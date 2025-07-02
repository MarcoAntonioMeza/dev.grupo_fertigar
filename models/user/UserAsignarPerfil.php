<?php
namespace app\models\user;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\auth\AuthItem;

/**
 * This is the model class for table "user_asignar_perfil".
 *
 * @property int $user_id Creado por
 * @property string $perfil Perfil
 *
 * @property AuthItem $perfil0
 * @property User $user
 */
class UserAsignarPerfil extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_asignar_perfil';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'perfil'], 'required'],

            [['user_id'], 'integer'],
            [['perfil'], 'string', 'max' => 32],
            [['user_id', 'perfil'], 'unique', 'targetAttribute' => ['user_id', 'perfil']],

            [['perfil'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['perfil' => 'name']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Usuario',
            'perfil' => 'Perfil',
        ];
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//
    /*    
    public function getPerfil0()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'perfil']);
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    */


//------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//
    public static function getItems($params = [])
    {
        $params['user_id']           = array_key_exists('user_id', $params)? $params['user_id']: Yii::$app->user->identity->id;
        $params['withTheCreator']    = array_key_exists('withTheCreator', $params)? $params['withTheCreator']: false;
        $params['with@uthenticated'] = array_key_exists('with@uthenticated', $params)? $params['with@uthenticated']: false;

        // Si es Super Adminstrador
        if (Yii::$app->user->can('admin')) {
            $query = AuthItem::find()
                ->andwhere(['in', 'type', [0, 1]])
                ->select('name')
                ->orderBy('type desc, name');

            if(!$params['withTheCreator'] || !Yii::$app->user->can('theCreator'))
                $query->andWhere(['!=', 'name', 'theCreator']);

            if(!$params['with@uthenticated'])
                $query->andWhere(['!=', 'name', '@uthenticated']);

            return ArrayHelper::map($query->all(), 'name', function($value){
                return $value['name'] . ($value['name'] == 'admin' || $value['name'] == 'theCreator'? ' [Super administrador]': '');
            });
        }        


        $query = UserAsignarPerfil::find()
            ->where(['user_id' => $params['user_id']])
            ->orderBy('perfil');

        return ($params['with@uthenticated']? ["@uthenticated" => "@uthenticated"]: []) + ArrayHelper::map($query->all(), 'perfil', 'perfil');
    }    

}
