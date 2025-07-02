<?php
namespace app\models\auth;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\user\User;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property int $user_id user
 * @property int $created_at
 *
 * @property AuthItem $itemName
 * @property User $user
 */
class AuthAssignment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_assignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required'],
            [['user_id', 'created_at'], 'integer'],
            [['item_name'], 'string', 'max' => 64],
            [['item_name', 'user_id'], 'unique', 'targetAttribute' => ['item_name', 'user_id']],
            [['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['item_name' => 'name']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'item_name' => 'Item Name',
            'user_id' => 'user',
            'created_at' => 'Created At',
        ];
    }


    public static function removeAssignment($item_name)
    {
        $query      = self::find()->andWhere(["item_name" => $item_name])->all();
        $response   = [];
        foreach ($query as $key => $item_query) {
            array_push($response, $item_query->user_id);
            $item_query->delete();
        }

        return $response;
    }

    public static function perfilAssignment($user_assignment,$item_name)
    {

        foreach ($user_assignment as $key => $item_assignment) {
            $AuthAssignment = new AuthAssignment();
            $AuthAssignment->item_name  = $item_name;
            $AuthAssignment->user_id    = $item_assignment;
            $AuthAssignment->save();
        }

        Perfil::deleteCacheFile();
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//
    public function getItemName()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


//------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//
    public static function getItemsAssignments()
    {
        $AuthAssignment = self::find()->select('item_name')->orderBy('item_name')->distinct();

        return ArrayHelper::map($AuthAssignment->all(), 'item_name', 'item_name');
    }

}
