<?php

namespace app\models\esys;

use Yii;
use app\models\user\User;
/**
 * This is the model class for table "esys_setting".
 *
 * @property int $cliente_id Cliente
 * @property string $clave Clave
 * @property string $valor Valor
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class EsysSetting extends \yii\db\ActiveRecord
{

    const CORTE_CAJA_MONTO      = "CORTE_CAJA_MONTO";
    const NOMBRE_SITIO          = "NOMBRE_SITIO";
    const EMAIL_SITIO           = "EMAIL_SITIO";
    const NOTIFICACION_CIERRE_REPARTO  = "NOTIFICACION_CIERRE_REPARTO";
    const CORREOS_NOTIFICACION_RETIRO  = "CORREOS_NOTIFICACION_RETIRO";

    public $esysSetting_list = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'esys_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],

            [['clave'], 'string', 'max' => 40],
            [['param1','param2'], 'string', 'max' => 20],
            [['valor'], 'string', 'max' => 250],
            [['clave'], 'unique'],
            [['esysSetting_list'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cliente_id' => 'Cliente ID',
            'clave' => 'Clave',
            'valor' => 'Valor',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public  function getConfiguracionAll()
    {
        return  EsysSetting::find()->orderBy('orden asc')->all();
    }

    public function saveConfiguracion($esysSetting_list)
    {
        foreach ($esysSetting_list["esysSetting_list"] as $key => $item) {
            $EsysSetting = EsysSetting::findOne(["clave" => $key]);
            $EsysSetting->valor = $item;
            $EsysSetting->update();
        }
    }


    public static function getCorteCaja(){
        return EsysSetting::findOne(["clave" => 'CORTE_CAJA_MONTO'])->valor;
    }

    public static function getNotificacionEmail(){
        return EsysSetting::findOne(["clave" => self::NOTIFICACION_CIERRE_REPARTO ])->valor;
    }

    public static function getCorreoNotificacionRetiro(){
        return EsysSetting::findOne(["clave" => self::CORREOS_NOTIFICACION_RETIRO ])->valor;
    }



    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

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
