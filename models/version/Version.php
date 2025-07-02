<?php
namespace app\models\version;

use Yii;
use app\models\Esys;
use app\models\user\User;

/**
 * This is the model class for table "version".
 *
 * @property int $id ID
 * @property int $fecha_registro Fecha registro
 * @property string $version Version
 * @property string $descripcion Descripcion
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 */
class Version extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'version';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_registro'], 'required'],
            [['created_at', 'created_by'], 'integer'],
            [['descripcion'], 'string'],
            [['version'], 'string', 'max' => 50],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha_registro' => 'Fecha Registro',
            'version' => 'Version',
            'descripcion' => 'Descripcion',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

     public static function getItems()
    {
        $model = self::find()->orderBy('fecha_registro DESC')->all();

        return $model;
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            $this->fecha_registro = Esys::stringToTimeUnix($this->fecha_registro);
            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }else{
                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }
            return true;

        } else
            return false;
    }

}
