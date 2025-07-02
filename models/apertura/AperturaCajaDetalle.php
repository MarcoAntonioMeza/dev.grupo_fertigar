<?php
namespace app\models\apertura;

use app\models\esys\EsysListaDesplegable;
use Yii;
use app\models\venta\Venta;
use app\models\user\User;
use app\models\credito\Credito;
use app\models\credito\CreditoAbono;
/**
 * This is the model class for table "apertura_caja_detalle".
 *
 * @property int $id ID
 * @property int $tipo Tipo
 * @property int|null $venta_id Venta ID
 * @property int|null $credito_id Credito ID
 * @property float $cantidad Cantidad
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property Credito $credito
 * @property Venta $venta
 */
class AperturaCajaDetalle extends \yii\db\ActiveRecord
{
    const TIPO_VENTA        = 10;
    const TIPO_CREDITO      = 20;
    const TIPO_RETIRO       = 30;
    const TIPO_GASTO        = 40;
    const TIPO_CANCEL_VENTA = 50;

    const PERTENECE_INGRESO = 10;
    const PERTENECE_RETIRO = 20;


    public static $tipoList = [
        self::TIPO_VENTA        => 'VENTA',
        self::TIPO_CREDITO      => 'CREDITO',
        self::TIPO_RETIRO       => 'RETIRO / EFECTIVO',
        self::TIPO_GASTO        => 'GASTO',
        self::TIPO_CANCEL_VENTA => 'CANCELACION VENTA',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    const STATUS_SUCCESS   = 10;
    const STATUS_CANCEL = 1;

    public static $statusList = [
        self::STATUS_SUCCESS    => 'SUCCESS',
        self::STATUS_CANCEL     => 'CANCEL',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apertura_caja_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo', 'cantidad', 'status','apertura_caja_id'], 'required'],
            [['tipo', 'venta_id', 'credito_id', 'status', 'apertura_caja_id','created_at', 'created_by','pertenece','updated_at'], 'integer'],
            [['pertenece'], 'default', 'value'=> self::PERTENECE_INGRESO],
            [['cantidad'], 'number'],
            [['token_pay','concepto'], 'string'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['credito_id'], 'exist', 'skipOnError' => true, 'targetClass' => Credito::className(), 'targetAttribute' => ['credito_id' => 'id']],
            [['venta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Venta::className(), 'targetAttribute' => ['venta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo' => 'Tipo',
            'venta_id' => 'Venta ID',
            'credito_id' => 'Credito ID',
            'cantidad' => 'Cantidad',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'observacion' => 'observacion',
            'tipo_gasto_id' => 'tipo gasto',
            'updated_at' => 'actualizar registro',
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
     * Gets query for [[Venta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVenta()
    {
        return $this->hasOne(Venta::className(), ['id' => 'venta_id']);
    }

    public function getTipoGasto()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'tipo_gasto_id']);
    }

    public static function isVigentePago($item_id, $tipo)
    {
        $is_true = true;
        if ($tipo == AperturaCajaDetalle::TIPO_VENTA) {
            $venta = Venta::findOne($item_id);
            if (isset($venta->id) && $venta->status == Venta::STATUS_CANCEL)
                $is_true = false;
        }

        if ($tipo == AperturaCajaDetalle::TIPO_CREDITO) {
            $CreditoAbono = CreditoAbono::find()->andWhere(["and",
                ["=","token_pay", $item_id],
                [ "=","status",CreditoAbono::STATUS_CANCEL]
            ])->all();

            if ($CreditoAbono)
                $is_true = false;
        }

        return $is_true;
    }

    public static function cancelPagoCantidad($item_id, $tipo)
    {
        $cantidad = false;
        if ($tipo == AperturaCajaDetalle::TIPO_VENTA) {
            $venta = Venta::findOne($item_id);
            if (isset($venta->id) && $venta->status == Venta::STATUS_CANCEL)
                $cantidad = $venta->total;
        }

        if ($tipo == AperturaCajaDetalle::TIPO_CREDITO) {
            $CreditoAbono = CreditoAbono::find()->andWhere(["and",
                ["=","token_pay", $item_id],
                [ "=","status",CreditoAbono::STATUS_CANCEL]
            ])->all();
            foreach ($CreditoAbono as $key => $item_credito) {
                $cantidad = floatval($cantidad) +  floatval($item_credito->cantidad);
            }
        }

        return $cantidad;
    }

    public static function getGastosXcaja($id)
    {
        $responseArray = [];

        $query = AperturaCajaDetalle::find()
        ->andWhere([ "and",
            [ "=", "apertura_caja_id", $id ],
            [ "=", "tipo", AperturaCajaDetalle::TIPO_GASTO ]
        ])
        ->all();

        foreach ($query as $key => $item_detail) {
            if ($item_detail->tipo_gasto_id) {
                array_push($responseArray, [
                    "singular" => $item_detail->tipoGasto->singular ." ". $item_detail->observacion,
                    "cantidad" => $item_detail->cantidad
                ]);
            }
        }

        return $responseArray;
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
