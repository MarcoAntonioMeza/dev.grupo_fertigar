<?php

namespace app\models\producto;

use Yii;
use app\models\user\User;
use app\helpers\AppHelper;
/**
 * This is the model class for table "unidadsat".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string $clave
 * @property string $nombre
 * @property int|null $created_by_id
 * @property int|null $updated_by_id
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Unidadsat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unidadsat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'created_by_id', 'updated_by_id'], 'integer'],
            [['clave', 'nombre'], 'required'],
            [['clave'], 'string', 'max' => 10],
            [['nombre'], 'string', 'max' => 100],
            [['clave'], 'unique'],
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
            'clave' => 'Clave',
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

    public static function get_unudades_sat(){
        $unidades = self::find()
            ->select(['id','concat(clave, " - ", nombre) as nombre'])
            ->asArray()
            ->all();

        $result = [];
        foreach ($unidades as $unidad) {
            $result[$unidad['id']] = $unidad['nombre'];
        }

        return $result;
    }


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by_id = Yii::$app->user->identity ? Yii::$app->user->identity->id : $this->created_by;
            } else {

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by_id = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            }

            if ($this->imageFile && $this->upload()) {
                $this->avatar = $this->clave . '.' . $this->imageFile->extension;
            }

            return true;
        } else
            return false;
    }
}
