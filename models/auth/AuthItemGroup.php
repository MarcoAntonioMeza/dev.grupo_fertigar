<?php
namespace app\models\auth;

use Yii;

/**
 * This is the model class for table "auth_item_group".
 *
 * @property int $id
 * @property string $area Área
 * @property string $grupo Grupo
 * @property int $orden Orden
 * @property string $icon Icono
 *
 * @property AuthItem[] $authItems
 */
class AuthItemGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['area', 'grupo', 'orden'], 'required'],
            [['orden'], 'integer'],
            [['area'], 'string', 'max' => 50],
            [['grupo'], 'string', 'max' => 30],
            [['icon'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'area' => 'Área',
            'grupo' => 'Grupo',
            'orden' => 'Orden',
            'icon' => 'Icono',
        ];
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::className(), ['grupo_id' => 'id']);
    }

}
