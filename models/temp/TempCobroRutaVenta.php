<?php
namespace app\models\temp;

use Yii;
use app\models\user\User;
use app\models\credito\Credito;
use app\models\temp\TempVentaRuta;
use app\models\cobro\CobroVenta;


/**
 * This is the model class for table "cobro_ruta_venta".
 *
 * @property int $id ID
 * @property int|null $temp_venta_ruta_id Venta ruta ID
 * @property int|null $credito_id Credito ID
 * @property string|null $trans_token_credito Token operacion
 * @property string|null $trans_token_venta Token operacion
 * @property int $tipo Tipo
 * @property int $metodo_pago Metodo de pago
 * @property int $tipo_cobro_pago Cobro / Pago
 * @property float $cantidad Cantidad
 * @property float|null $cantidad_pago Cantidad Pago
 * @property int|null $fecha_credito Fecha credito
 * @property string|null $nota Nota
 * @property float|null $cargo_extra Cargo extra
 * @property int|null $producto_id Producto ID
 * @property float|null $cantidad_recibe Cantidad
 * @property string|null $nota_otro Nota
 * @property int $is_cancel Is cancel
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property Credito $credito
 * @property VentaRuta $ventaRuta
 */
class TempCobroRutaVenta extends \yii\db\ActiveRecord
{
    const IS_APPLY_ON   = 10;
    const IS_APPLY_OFF  = 20;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_cobro_ruta_venta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['temp_venta_ruta_id', 'credito_id', 'tipo', 'metodo_pago', 'tipo_cobro_pago', 'fecha_credito', 'is_cancel', 'created_at', 'created_by','operacion_reparto_id'], 'integer'],
            [['tipo', 'metodo_pago', 'tipo_cobro_pago', 'cantidad','operacion_reparto_id'], 'required'],
            [['cantidad', 'cantidad_pago', 'cargo_extra'], 'number'],
            [['nota'], 'string'],
            [['is_apply'], 'default','value' => self::IS_APPLY_OFF],
            [['is_cancel'], 'default', 'value' =>  CobroVenta::IS_CANCEL_OFF ],
            [['trans_token_credito', 'trans_token_venta'], 'string', 'max' => 150],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['credito_id'], 'exist', 'skipOnError' => true, 'targetClass' => Credito::className(), 'targetAttribute' => ['credito_id' => 'id']],
            [['temp_venta_ruta_id'], 'exist', 'skipOnError' => true, 'targetClass' => TempVentaRuta::className(), 'targetAttribute' => ['temp_venta_ruta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'temp_venta_ruta_id' => 'Venta Ruta ID',
            'credito_id' => 'Credito ID',
            'trans_token_credito' => 'Trans Token Credito',
            'trans_token_venta' => 'Trans Token Venta',
            'tipo' => 'Tipo',
            'metodo_pago' => 'Metodo Pago',
            'tipo_cobro_pago' => 'Tipo Cobro Pago',
            'cantidad' => 'Cantidad',
            'cantidad_pago' => 'Cantidad Pago',
            'fecha_credito' => 'Fecha Credito',
            'nota' => 'Nota',
            'cargo_extra' => 'Cargo Extra',
            'producto_id' => 'Producto ID',
            'cantidad_recibe' => 'Cantidad Recibe',
            'nota_otro' => 'Nota Otro',
            'is_cancel' => 'Is Cancel',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
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

    /**
     * Gets query for [[Credito]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCredito()
    {
        return $this->hasOne(Credito::className(), ['id' => 'credito_id']);
    }

    /**
     * Gets query for [[VentaRuta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentaRuta()
    {
        return $this->hasOne(VentaRuta::className(), ['id' => 'temp_venta_ruta_id']);
    }

    public static function getTempCobro($reparto_id)
    {
        $tempCobro = [];

        $query = TempCobroRutaVenta::find()->andWhere(["operacion_reparto_id" => $reparto_id ])->all();

        foreach ($query as $key => $item_query) {
            array_push($tempCobro, [
                "pertenece"     => CobroVenta::$tipoList[$item_query->tipo],
                "metodo_pago"   => CobroVenta::$servicioList[$item_query->metodo_pago],
                "cantidad"      => $item_query->cantidad,
                "is_apply"      => $item_query->is_apply,
                "created_at"    => date("Y-m-d h:i a",$item_query->created_at),
            ]);
        }

        return $tempCobro;
    }


    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
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
