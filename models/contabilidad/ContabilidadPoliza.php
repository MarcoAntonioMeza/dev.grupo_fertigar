<?php
namespace app\models\contabilidad;

use Yii;
use app\models\user\User;
use app\models\venta\VentaCobro;
use app\models\compra\CompraPago;
use app\models\inv\Operacion;
use app\models\ventanilla\CajaVentanillaOperacionDetail;
use app\models\ventanilla\CajaVentanillaOtraOperacion;
use app\models\ventanilla\CajaVentanillaOperacion;

/**
 * This is the model class for table "contabilidad_poliza".
 *
 * @property int $id ID
 * @property int $pertenece Pertenece
 * @property string|null $concepto concepto
 * @property int $status Estatus
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property ContabilidadPolizaDetail[] $contabilidadPolizaDetails
 */
class ContabilidadPoliza extends \yii\db\ActiveRecord
{

    const STATUS_POR_VALIDAR   = 10;
    const STATUS_VALIDADO      = 20;

    const TIPO_SISTEMA   = 10;
    const TIPO_MANUAL    = 20;

    public static $statusList = [
        self::STATUS_POR_VALIDAR        => "POR VERIFICAR",
        self::STATUS_VALIDADO           => "VERIFICADO",
    ];

    public static $tipoList = [
        self::TIPO_SISTEMA   => "SISTEMA",
        self::TIPO_MANUAL    => "MANUAL",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_poliza';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pertenece'], 'required'],
            [['pertenece', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['concepto'], 'string', 'max' => 150],
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
            'id' => 'ID',
            'pertenece' => 'Pertenece',
            'concepto' => 'Concepto',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[AlmacenOperacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlmacenOperacion()
    {
        return $this->hasOne(Operacion::className(), ['id' => 'almacen_operacion_id']);
    }

    /**
     * Gets query for [[CajaVentanillaOperacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCajaVentanillaOperacionDetail()
    {
        return $this->hasOne(CajaVentanillaOperacionDetail::className(), ['id' => 'caja_ventanilla_operacion_detail_id']);
    }


    /**
     * Gets query for [[Compra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompra()
    {
        return $this->hasOne(Compra::className(), ['id' => 'compra_id']);
    }

    /**
     * Gets query for [[CompraPago]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompraPago()
    {
        return $this->hasOne(CompraPago::className(), ['id' => 'compra_pago_id']);
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
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * Gets query for [[VentaCobro]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentaCobro()
    {
        return $this->hasOne(VentaCobro::className(), ['id' => 'venta_cobro_id']);
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

    /**
     * Gets query for [[ContabilidadPolizaDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadPolizaDetails()
    {
        return $this->hasMany(ContabilidadPolizaDetail::className(), ['contabilidad_poliza_id' => 'id']);
    }

    public static function saveOperacionPoliza($pertenece, $operacionID, $total, $user_id)
    {
        $connection = \Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try {
             $contabilidadPoliza = $connection->createCommand()
            ->insert('contabilidad_poliza', [
                    'venta_id'                              => $pertenece == ContabilidadTransaccion::OPERACION_VENTA_CANCELACION ?  $operacionID : null,
                    'compra_id'                             => $pertenece == ContabilidadTransaccion::OPERACION_COMPRA_CANCELACION ?  $operacionID : null,
                    'compra_pago_id'                        => $pertenece == ContabilidadTransaccion::OPERACION_COMPRA ?  $operacionID : null,
                    'venta_cobro_id'                        => $pertenece == ContabilidadTransaccion::OPERACION_VENTA ?  $operacionID : null,
                    'caja_ventanilla_operacion_detail_id'          => $pertenece == ContabilidadTransaccion::OPERACION_SALARIO_ORDINARIO_ABONO || $pertenece == ContabilidadTransaccion::OPERACION_SALARIO_EXTRAORDINARIO_ABONO || $pertenece == ContabilidadTransaccion::OPERACION_SALARIO_EXTRAORDINARIO_RETIRO || $pertenece == ContabilidadTransaccion::OPERACION_SALARIO_APORTACION_SOCIAL_ABONO || $pertenece == ContabilidadTransaccion::OPERACION_SALARIO_APORTACION_SOCIAL_RETIRO || $pertenece == ContabilidadTransaccion::OPERACION_AYUDA_ABONO || $pertenece == ContabilidadTransaccion::OPERACION_AYUDA_RETIRO || $pertenece == ContabilidadTransaccion::OPERACION_PAGO_LIBRETA || $pertenece == ContabilidadTransaccion::OPERACION_PAGO_CREDENCIAL ?  $operacionID : null,
                    'almacen_operacion_id'     => $pertenece == ContabilidadTransaccion::OPERACION_COMPRA_ENTRADA_INFERIOR  || $pertenece == ContabilidadTransaccion::OPERACION_COMPRA_ENTRADA_SUPERIOR  || $pertenece == ContabilidadTransaccion::OPERACION_TRASPASO_INVENTARIO_ENTRADA  || $pertenece == ContabilidadTransaccion::OPERACION_TRASPASO_INVENTARIO_SALIDA ?  $operacionID : null,
                    'nomina_id'                             => $pertenece == ContabilidadTransaccion::OPERACION_NOMINA || $pertenece == ContabilidadTransaccion::OPERACION_NOMINA_ISR || $pertenece == ContabilidadTransaccion::OPERACION_NOMINA_IVA || $pertenece == ContabilidadTransaccion::OPERACION_NOMINA_IMSS || $pertenece == ContabilidadTransaccion::OPERACION_NOMINA_BONO || $pertenece == ContabilidadTransaccion::OPERACION_NOMINA_DEDUCCION ?  $operacionID : null,
                    'pertenece'                             => $pertenece,
                    'tipo'                                  => self::TIPO_SISTEMA,
                    'concepto'                              => null,
                    'status'                                => self::STATUS_POR_VALIDAR,
                    'total'                                 => $total,
                    'created_by'                            => $user_id,
                    'created_at'                            => time(),
            ])->execute();

            $transaction->commit();

        } catch(Exception $e) {

            $transaction->rollback();
        }
    }

    public static function createPoliza($poliza_type,  $concepto, $cuentas, $idCuenta, $cargos, $abonos)
    {
         $connection = \Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try {
            /*OBTENTER EL TOTAL DE LA POLIZA*/
            $calTotal =  0;
            foreach ($cargos as $key => $item_cargo) {
                $calTotal = $calTotal + floatval($item_cargo);
            }

            $contabilidadPoliza = $connection->createCommand()
            ->insert('contabilidad_poliza', [
                "tipo"          => ContabilidadPoliza::TIPO_MANUAL,
                "pertenece"     => null,
                "concepto"      => $concepto,
                "status"        => self::STATUS_POR_VALIDAR,
                "total"         => $calTotal,
                'created_by'    => Yii::$app->user->identity->id,
                'created_at'    => time(),
            ])->execute();

            $polizaID = Yii::$app->db->getLastInsertID();

            foreach ($cuentas as $key_cuenta => $cuenta) {
                $contabilidadPolizaDetail = $connection->createCommand()
                ->insert('contabilidad_poliza_detail', [
                    "contabilidad_poliza_id"    => $polizaID,
                    "cargo"                     => isset($cargos[$key_cuenta]) ? $cargos[$key_cuenta] : 0,
                    "abono"                     => isset($abonos[$key_cuenta]) ? $abonos[$key_cuenta] : 0,
                    "tipo_poliza"               => $poliza_type,
                    "contabilidad_cuenta_id"    => $idCuenta[$key_cuenta],
                    'created_by'                => Yii::$app->user->identity->id,
                    'created_at'                => time(),
                ])->execute();
            }

            $transaction->commit();

            return [
                "code" => 202,
                "message" => "SE REALIZO CORRECTAMENTE LA OPERACION"
            ];

        } catch(Exception $e) {

            $transaction->rollback();

            return [
                "code" => 10,
                "message" => "Ocurrion un error, intenta nuevamente"
            ];
        }
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
