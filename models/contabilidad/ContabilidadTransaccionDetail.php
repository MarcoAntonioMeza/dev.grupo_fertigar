<?php
namespace app\models\contabilidad;
use app\models\user;

use Yii;

/**
 * This is the model class for table "contabilidad_transaccion_detail".
 *
 * @property int $id ID
 * @property int $contabilidad_transaccion_id Contabilidad transaccion ID
 * @property int $contabilidad_cuenta_id Contabilidad cuenta ID
 * @property string $tipo_poliza Poliza type
 * @property int $apply_afectable Apply afectable
 * @property float|null $cargo Cargo
 * @property float|null $abono Abono
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property ContabilidadCuenta $contabilidadCuenta
 * @property ContabilidadTransaccion $contabilidadTransaccion
 * @property User $createdBy
 */
class ContabilidadTransaccionDetail extends \yii\db\ActiveRecord
{
    const APPLY_AFECTABLE_SI        = 10;
    const APPLY_AFECTABLE_NO        = 20;

    const TIPO_POLIZA_INGRESO   = 10;
    const TIPO_POLIZA_EGRESO    = 20;
    const TIPO_POLIZA_CHEQUES   = 30;
    const TIPO_POLIZA_DE_DIARIO = 40;

    public static $tipoPoliza = [
        self::TIPO_POLIZA_INGRESO      => "INGRESO",
        self::TIPO_POLIZA_EGRESO       => "EGRESO",
        self::TIPO_POLIZA_CHEQUES      => "CHEQUES",
        self::TIPO_POLIZA_DE_DIARIO    => "POLIZA DE DIARIO",
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_transaccion_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contabilidad_transaccion_id', 'contabilidad_cuenta_id','tipo_poliza'], 'required'],
            [['contabilidad_transaccion_id', 'contabilidad_cuenta_id', 'apply_afectable', 'created_at', 'created_by'], 'integer'],
            [['cargo', 'abono'], 'number'],
            [['apply_afectable'], 'default', 'value' => self::APPLY_AFECTABLE_NO ],
            [['contabilidad_cuenta_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadCuenta::className(), 'targetAttribute' => ['contabilidad_cuenta_id' => 'id']],
            [['contabilidad_transaccion_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadTransaccion::className(), 'targetAttribute' => ['contabilidad_transaccion_id' => 'id']],
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
            'contabilidad_transaccion_id' => 'Contabilidad Transaccion ID',
            'contabilidad_cuenta_id' => 'Contabilidad Cuenta ID',
            'tipo_poliza'=>'Poliza type',
            'apply_afectable' => 'Apply Afectable',
            'cargo' => 'Cargo', 
            'abono' => 'Abono',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * Gets query for [[ContabilidadCuenta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadCuenta()
    {
        return $this->hasOne(ContabilidadCuenta::className(), ['id' => 'contabilidad_cuenta_id']);
    }

    /**
     * Gets query for [[ContabilidadTransaccion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadTransaccion()
    {
        return $this->hasOne(ContabilidadTransaccion::className(), ['id' => 'contabilidad_transaccion_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }


    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
    
            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->created_by;
    
            }
    
            return true;
    
        } else
            return false;
    }
}
