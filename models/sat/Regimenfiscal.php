<?php

namespace app\models\sat;

use Yii;
use app\models\user\User;

use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "regimenfiscal".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string $codigo
 * @property string $nombre
 * @property int|null $created_by_id
 * @property int|null $updated_by_id
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Regimenfiscal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'regimenfiscal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'created_by_id', 'updated_by_id'], 'integer'],
            [['codigo', 'nombre'], 'required'],
            [['codigo'], 'string', 'max' => 20],
            [['nombre'], 'string', 'max' => 200],
            [['codigo'], 'unique'],
            [['created_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by_id' => 'id']],
            [['updated_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'codigo' => 'Codigo',
            'nombre' => 'Nombre',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by_id']);
    }


    public static function getArrayRegimenFiscal()
    {
        $regimenes = self::find()->all();
        return ArrayHelper::map($regimenes, 'id', 'nombre');
    }

    public function getNombreCompleto()
    {
        return $this->codigo . ' - ' . $this->nombre;
    }
}
