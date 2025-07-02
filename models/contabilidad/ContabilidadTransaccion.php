<?php
namespace app\models\contabilidad;

use Yii;
use app\models\user\User;
use app\models\venta\Venta;
use app\models\compra\Compra;
use app\models\compra\CompraPago;
use app\models\venta\VentaCobro;
use app\models\contabilidad\ContabilidadCuenta;
use app\models\ventanilla\CajaVentanillaOperacion;

/**
 * This is the model class for table "contabilidad_transaccion".
 *
 * @property int $id
 * @property int|null $venta_id Venta ID
 * @property int|null $compra_id Compra ID
 * @property int|null $caja_ventanilla_operacion_id Caja ventanilla operacion ID
 * @property int|null $contabilidad_cuenta_id Contabilidad cuenta ID
 * @property int $tipo Tipo
 * @property int|null $afectable
 * @property float|null $cargo Cargo
 * @property float|null $abono Abono
 * @property int|null $metodo_pago Metodo de pago
 * @property float|null $cantidad Cantidad
 * @property int $status status
 * @property int|null $created_at
 * @property int $created_by
 * @property int|null $updated_at
 * @property string|null $updated_by
 *
 * @property CajaVentanillaOperacion $cajaVentanillaOperacion
 * @property Compra $compra
 * @property ContabilidadCuenta $contabilidadCuenta
 * @property User $createdBy
 * @property Venta $venta
 */
class ContabilidadTransaccion extends \yii\db\ActiveRecord
{
    const AFECTABLE = 10; 
    const NO_AFECTABLE = 20; 

    public static $afectableList = [
        self::AFECTABLE => 'Afectable',
        self::NO_AFECTABLE => 'No afectable'
    ];

 

    const STATUS_PROCESO        = 10;
    const STATUS_CONFIGURADO    = 20;

    public static $statusList = [
        self::STATUS_CONFIGURADO    => "CONFIGURADO",
        self::STATUS_PROCESO        => "POR CONFIGURAR",
    ];

    const OPERACION_COMPRA                              = 10;
    const OPERACION_COMPRA_ENTRADA_INFERIOR             = 11;
    const OPERACION_COMPRA_ENTRADA_SUPERIOR             = 12;
    const OPERACION_COMPRA_CANCELACION                  = 20;
    const OPERACION_VENTA                               = 30;
    const OPERACION_VENTA_CANCELACION                   = 40;
    const OPERACION_SALARIO_ORDINARIO_ABONO             = 50;
    const OPERACION_SALARIO_ORDINARIO_RETIRO            = 60;
    const OPERACION_SALARIO_EXTRAORDINARIO_ABONO        = 70;
    const OPERACION_SALARIO_EXTRAORDINARIO_RETIRO       = 80;
    const OPERACION_SALARIO_APORTACION_SOCIAL_ABONO     = 90;

    const OPERACION_SALARIO_APORTACION_SOCIAL_RETIRO    = 100;
    const OPERACION_PAGO_CREDENCIAL                     = 110;
    const OPERACION_PAGO_LIBRETA                        = 120;
    const OPERACION_AYUDA_ABONO                         = 130;
    const OPERACION_AYUDA_RETIRO                        = 140;
    const OPERACION_TRASPASO_INVENTARIO_SALIDA          = 150;
    const OPERACION_TRASPASO_INVENTARIO_ENTRADA         = 160;
    const OPERACION_NOMINA                              = 170;
    const OPERACION_NOMINA_ISR                          = 180;
    const OPERACION_NOMINA_IVA                          = 190;
    const OPERACION_NOMINA_IMSS                         = 200;
    const OPERACION_NOMINA_BONO                         = 210;
    const OPERACION_NOMINA_DEDUCCION                    = 220;



    public static $tipoList = [
         self::OPERACION_COMPRA                                 => "COMPRA",
         self::OPERACION_COMPRA_ENTRADA_INFERIOR                => "AJUSTE ENTRADA COMPRA [INFERIOR]",
         self::OPERACION_COMPRA_ENTRADA_SUPERIOR                => "AJUSTE ENTRADA COMPRA [SUPERIOR]",
         self::OPERACION_COMPRA_CANCELACION                     => "CANCELACION DE COMPRA",
         self::OPERACION_VENTA                                  => "VENTA",
         self::OPERACION_VENTA_CANCELACION                      => "CANCELACION DE VENTA",
         self::OPERACION_SALARIO_ORDINARIO_ABONO                => "ABONO SALARIO ORDINARIO",
         self::OPERACION_SALARIO_ORDINARIO_RETIRO               => "RETIRO SALARIO ORDINARIO",
         self::OPERACION_SALARIO_EXTRAORDINARIO_ABONO           => "ABONO SALARIO EXTRAORDINARIO",
         self::OPERACION_SALARIO_EXTRAORDINARIO_RETIRO          => "RETIRO SALARIO EXTRAORDINARIO",
         self::OPERACION_SALARIO_APORTACION_SOCIAL_ABONO        => "ABONO APORTACION SOCIAL",
         self::OPERACION_SALARIO_APORTACION_SOCIAL_RETIRO       => "RETIRO APORTACION SOCIAL",
         self::OPERACION_PAGO_CREDENCIAL                        => "PAGO DE CREDENCIAL",
         self::OPERACION_PAGO_LIBRETA                           => "PAGO DE LIBRETA",
         self::OPERACION_AYUDA_ABONO                            => "ABONO A AYUDA MUTUA",
         self::OPERACION_AYUDA_RETIRO                           => "RETIRO AYUDA MUTUA",
         self::OPERACION_TRASPASO_INVENTARIO_SALIDA             => "TRASPASO DE INVENTARIO - SALIDA",
         self::OPERACION_TRASPASO_INVENTARIO_ENTRADA            => "TRASPASO DE INVENTARIO - ENTRADA",
         self::OPERACION_NOMINA                                 => "NOMINA",
         self::OPERACION_NOMINA_ISR                             => "NOMINA - ISR",
         self::OPERACION_NOMINA_IVA                             => "NOMINA - IVA",
         self::OPERACION_NOMINA_IMSS                            => "NOMINA - IMSS",
         self::OPERACION_NOMINA_BONO                            => "NOMINA - BONO",
         self::OPERACION_NOMINA_DEDUCCION                       => "NOMINA - DEDUCCION",
    ];

    const MOTIVO_EFECTIVO        = 10;
    const MOTIVO_CHEQUE          = 20;
    const MOTIVO_TRANFERENCIA    = 30;
    const MOTIVO_TARJETA_CREDITO = 40;
    const MOTIVO_TARJETA_DEBITO  = 50;
    const MOTIVO_DEPOSITO        = 60;
    const MOTIVO_AYUDA_ELECTRO   = 70;
    const MOTIVO_AYUDA_UNYPAP    = 80;
    const MOTIVO_CANCELACION     = 90;
    const MOTIVO_MOV_INVENTARIO  = 100;
    const MOTIVO_MOV_AJUSTE      = 110;
    const MOTIVO_NOMINA          = 120;

    public static $motivoList = [
        self::MOTIVO_EFECTIVO               => 'EFECTIVO',
        self::MOTIVO_CHEQUE                 => 'CHEQUE',
        self::MOTIVO_TRANFERENCIA           => 'TRANSFERENCIA',
        self::MOTIVO_TARJETA_CREDITO        => 'TARJETA DE CREDITO',
        self::MOTIVO_TARJETA_DEBITO         => 'TARJETA DE DEBITO',
        self::MOTIVO_DEPOSITO               => 'DEPOSITO',
        self::MOTIVO_AYUDA_ELECTRO          => 'AYUDA - ELECTROAPOYO',
        self::MOTIVO_AYUDA_UNYPAP           => 'AYUDA - INYPAP',
        self::MOTIVO_CANCELACION            => 'CANCELACION',
        self::MOTIVO_MOV_INVENTARIO         => 'TRASPASO - INVENTARIO',
        self::MOTIVO_MOV_AJUSTE             => 'AJUSTE',
        self::MOTIVO_NOMINA                 => 'NOMINA',

    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_transaccion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo', 'motivo', 'status', 'created_at', 'created_by', 'updated_at'], 'integer'],
            [['tipo'], 'required'],
            [['status'], 'default', 'value' => self::STATUS_PROCESO ],
            [['updated_by'], 'string', 'max' => 50],
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
            'tipo' => 'Tipo',
            'apply_afectable' => 'Afectable',
            'cargo' => 'Cargo',
            'abono' => 'Abono',
            'metodo_pago' => 'Metodo Pago',
            'cantidad' => 'Cantidad',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
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
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
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

    public static function getConfigContable($poliza_id)
    {

        $responseArray = [];

        $queryPoliza = ContabilidadPoliza::findOne($poliza_id);

        if ($queryPoliza) {
            if ($queryPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA) {

                $queryTransaccion = ContabilidadTransaccion::find()->andwhere(["and",
                    [ "=", "tipo", $queryPoliza->pertenece ],
                    [ "=", "status", ContabilidadTransaccion::STATUS_CONFIGURADO ]
                ])->all();

                foreach ($queryTransaccion as $key => $item_transaccion) {
                    if($item_transaccion->tipo == self::OPERACION_COMPRA ){
                        if ($queryPoliza->compraPago->metodo_pago == CompraPago::PAGO_EFECTIVO ) {
                            if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                                $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                                foreach ($queryTransaccionDetail as $key => $item_detail) {
                                    array_push($responseArray,[
                                        "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                        "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                        "cargo" => $item_detail->cargo,
                                        "abono" => $item_detail->abono,
                                        "created_at" => $item_detail->created_at,
                                        "tipo_poliza" => $item_detail->tipo_poliza,
                                        "apply_afectable"   => $item_detail->apply_afectable,
                                        "motivo"            => $item_transaccion->motivo,
                                        "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                        "transaccion_id"    => $item_transaccion->id,
                                        "transaccion_detail_id"    => $item_detail->id,
                                    ]);
                                }

                            }
                        }

                        if ($queryPoliza->compraPago->metodo_pago == CompraPago::PAGO_CHEQUE ) {
                            if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_CHEQUE) {
                                $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                                foreach ($queryTransaccionDetail as $key => $item_detail) {
                                    array_push($responseArray,[
                                        "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                        "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                        "cargo" => $item_detail->cargo,
                                        "abono" => $item_detail->abono,
                                        "created_at" => $item_detail->created_at,
                                        "tipo_poliza" => $item_detail->tipo_poliza,
                                        "apply_afectable"   => $item_detail->apply_afectable,
                                        "motivo"            => $item_transaccion->motivo,
                                        "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                        "transaccion_id"    => $item_transaccion->id,
                                        "transaccion_detail_id"    => $item_detail->id,
                                    ]);
                                }

                            }
                        }

                        if ($queryPoliza->compraPago->metodo_pago == CompraPago::PAGO_TRANFERENCIA ) {
                            if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_TRANFERENCIA) {
                                $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                                foreach ($queryTransaccionDetail as $key => $item_detail) {
                                    array_push($responseArray,[
                                        "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                        "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                        "cargo" => $item_detail->cargo,
                                        "abono" => $item_detail->abono,
                                        "created_at" => $item_detail->created_at,
                                        "tipo_poliza" => $item_detail->tipo_poliza,
                                        "apply_afectable"   => $item_detail->apply_afectable,
                                        "motivo"            => $item_transaccion->motivo,
                                        "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                        "transaccion_id"    => $item_transaccion->id,
                                        "transaccion_detail_id"    => $item_detail->id,
                                    ]);
                                }

                            }
                        }

                        if ($queryPoliza->compraPago->metodo_pago == CompraPago::PAGO_TARJETA_CREDITO ) {
                            if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_TARJETA_CREDITO) {
                                $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                                foreach ($queryTransaccionDetail as $key => $item_detail) {
                                    array_push($responseArray,[
                                        "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                        "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                        "cargo" => $item_detail->cargo,
                                        "abono" => $item_detail->abono,
                                        "created_at" => $item_detail->created_at,
                                        "tipo_poliza" => $item_detail->tipo_poliza,
                                        "apply_afectable"   => $item_detail->apply_afectable,
                                        "motivo"            => $item_transaccion->motivo,
                                        "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                        "transaccion_id"    => $item_transaccion->id,
                                        "transaccion_detail_id"    => $item_detail->id,
                                    ]);
                                }

                            }
                        }

                        if ($queryPoliza->compraPago->metodo_pago == CompraPago::PAGO_TARJETA_DEBITO ) {
                            if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_TARJETA_DEBITO) {
                                $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                                foreach ($queryTransaccionDetail as $key => $item_detail) {
                                    array_push($responseArray,[
                                        "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                        "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                        "cargo" => $item_detail->cargo,
                                        "abono" => $item_detail->abono,
                                        "created_at" => $item_detail->created_at,
                                        "tipo_poliza" => $item_detail->tipo_poliza,
                                        "apply_afectable"   => $item_detail->apply_afectable,
                                        "motivo"            => $item_transaccion->motivo,
                                        "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                        "transaccion_id"    => $item_transaccion->id,
                                        "transaccion_detail_id"    => $item_detail->id,
                                    ]);
                                }

                            }
                        }

                        if ($queryPoliza->compraPago->metodo_pago == CompraPago::PAGO_DEPOSITO ) {
                            if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_DEPOSITO) {
                                $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                                foreach ($queryTransaccionDetail as $key => $item_detail) {
                                    array_push($responseArray,[
                                        "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                        "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                        "cargo" => $item_detail->cargo,
                                        "abono" => $item_detail->abono,
                                        "created_at" => $item_detail->created_at,
                                        "tipo_poliza" => $item_detail->tipo_poliza,
                                        "apply_afectable"   => $item_detail->apply_afectable,
                                        "motivo"            => $item_transaccion->motivo,
                                        "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                        "transaccion_id"    => $item_transaccion->id,
                                        "transaccion_detail_id"    => $item_detail->id,
                                    ]);
                                }

                            }
                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_COMPRA_ENTRADA_INFERIOR ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_MOV_AJUSTE) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }
                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_COMPRA_ENTRADA_SUPERIOR ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_MOV_AJUSTE) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }
                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_COMPRA_CANCELACION ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_CANCELACION) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_VENTA ){
                        if ($queryPoliza->ventaCobro->metodo_pago == VentaCobro::COBRO_EFECTIVO ) {
                            if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                                $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                                foreach ($queryTransaccionDetail as $key => $item_detail) {
                                    array_push($responseArray,[
                                        "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                        "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                        "cargo" => $item_detail->cargo,
                                        "abono" => $item_detail->abono,
                                        "created_at" => $item_detail->created_at,
                                        "tipo_poliza" => $item_detail->tipo_poliza,
                                        "apply_afectable"   => $item_detail->apply_afectable,
                                        "motivo"            => $item_transaccion->motivo,
                                        "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                        "transaccion_id"    => $item_transaccion->id,
                                        "transaccion_detail_id"    => $item_detail->id,
                                    ]);
                                }

                            }
                        }

                        if ($queryPoliza->ventaCobro->metodo_pago == VentaCobro::COBRO_CREDITO ) {
                            if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_AYUDA_ELECTRO) {
                                $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                                foreach ($queryTransaccionDetail as $key => $item_detail) {
                                    array_push($responseArray,[
                                        "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                        "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                        "cargo" => $item_detail->cargo,
                                        "abono" => $item_detail->abono,
                                        "created_at" => $item_detail->created_at,
                                        "tipo_poliza" => $item_detail->tipo_poliza,
                                        "apply_afectable"   => $item_detail->apply_afectable,
                                        "motivo"            => $item_transaccion->motivo,
                                        "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                        "transaccion_id"    => $item_transaccion->id,
                                        "transaccion_detail_id"    => $item_detail->id,
                                    ]);
                                }

                            }
                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_VENTA_CANCELACION ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_CANCELACION) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_SALARIO_ORDINARIO_ABONO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_SALARIO_ORDINARIO_RETIRO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_SALARIO_EXTRAORDINARIO_ABONO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_SALARIO_EXTRAORDINARIO_RETIRO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_SALARIO_APORTACION_SOCIAL_ABONO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }



                    if($item_transaccion->tipo == self::OPERACION_SALARIO_APORTACION_SOCIAL_RETIRO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_PAGO_CREDENCIAL ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_PAGO_LIBRETA ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_AYUDA_ABONO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_AYUDA_RETIRO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_EFECTIVO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_TRASPASO_INVENTARIO_SALIDA ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_MOV_INVENTARIO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_TRASPASO_INVENTARIO_ENTRADA ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_MOV_INVENTARIO) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta" => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo" => $item_detail->cargo,
                                    "abono" => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza" => $item_detail->tipo_poliza,
                                    "apply_afectable"   => $item_detail->apply_afectable,
                                    "motivo"            => $item_transaccion->motivo,
                                    "motivo_text"            => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"    => $item_transaccion->id,
                                    "transaccion_detail_id"    => $item_detail->id,
                                ]);
                            }

                        }

                    }

                    if($item_transaccion->tipo == self::OPERACION_NOMINA ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_NOMINA) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta"        => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo"         => $item_detail->cargo,
                                    "abono"         => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza"   => $item_detail->tipo_poliza,
                                    "apply_afectable"       => $item_detail->apply_afectable,
                                    "motivo"                => $item_transaccion->motivo,
                                    "motivo_text"           => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"        => $item_transaccion->id,
                                    "transaccion_detail_id" => $item_detail->id,
                                ]);
                            }
                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_NOMINA_ISR ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_NOMINA) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta"        => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo"         => $item_detail->cargo,
                                    "abono"         => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza"   => $item_detail->tipo_poliza,
                                    "apply_afectable"       => $item_detail->apply_afectable,
                                    "motivo"                => $item_transaccion->motivo,
                                    "motivo_text"           => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"        => $item_transaccion->id,
                                    "transaccion_detail_id" => $item_detail->id,
                                ]);
                            }
                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_NOMINA_IVA ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_NOMINA) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta"        => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo"         => $item_detail->cargo,
                                    "abono"         => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza"   => $item_detail->tipo_poliza,
                                    "apply_afectable"       => $item_detail->apply_afectable,
                                    "motivo"                => $item_transaccion->motivo,
                                    "motivo_text"           => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"        => $item_transaccion->id,
                                    "transaccion_detail_id" => $item_detail->id,
                                ]);
                            }
                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_NOMINA_IMSS ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_NOMINA) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta"        => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo"         => $item_detail->cargo,
                                    "abono"         => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza"   => $item_detail->tipo_poliza,
                                    "apply_afectable"       => $item_detail->apply_afectable,
                                    "motivo"                => $item_transaccion->motivo,
                                    "motivo_text"           => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"        => $item_transaccion->id,
                                    "transaccion_detail_id" => $item_detail->id,
                                ]);
                            }
                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_NOMINA_BONO ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_NOMINA) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta"        => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo"         => $item_detail->cargo,
                                    "abono"         => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza"   => $item_detail->tipo_poliza,
                                    "apply_afectable"       => $item_detail->apply_afectable,
                                    "motivo"                => $item_transaccion->motivo,
                                    "motivo_text"           => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"        => $item_transaccion->id,
                                    "transaccion_detail_id" => $item_detail->id,
                                ]);
                            }
                        }
                    }

                    if($item_transaccion->tipo == self::OPERACION_NOMINA_DEDUCCION ){
                        if ( $item_transaccion->motivo == ContabilidadTransaccion::MOTIVO_NOMINA) {
                            $queryTransaccionDetail = ContabilidadTransaccionDetail::find()->andwhere([ "contabilidad_transaccion_id" => $item_transaccion->id ])->all();
                            foreach ($queryTransaccionDetail as $key => $item_detail) {
                                array_push($responseArray,[
                                    "cuenta"        => $item_detail->contabilidadCuenta->nombre,
                                    "cuenta_numero" => $item_detail->contabilidadCuenta->code,
                                    "cargo"         => $item_detail->cargo,
                                    "abono"         => $item_detail->abono,
                                    "created_at" => $item_detail->created_at,
                                    "tipo_poliza"   => $item_detail->tipo_poliza,
                                    "apply_afectable"       => $item_detail->apply_afectable,
                                    "motivo"                => $item_transaccion->motivo,
                                    "motivo_text"           => ContabilidadTransaccion::$motivoList[$item_transaccion->motivo],
                                    "transaccion_id"        => $item_transaccion->id,
                                    "transaccion_detail_id" => $item_detail->id,
                                ]);
                            }
                        }
                    }

                }
            }

            if ($queryPoliza->tipo == ContabilidadPoliza::TIPO_MANUAL) {

                    $queryPolizaDetail = ContabilidadPolizaDetail::find()->andwhere([ "contabilidad_poliza_id" => $queryPoliza->id ])->all();

                    foreach ($queryPolizaDetail as $key => $item_detail) {
                        array_push($responseArray,[
                            "cuenta"                    => $item_detail->contabilidadCuenta->nombre,
                            "cuenta_numero"             => $item_detail->contabilidadCuenta->code,
                            "cargo"                     => $item_detail->cargo,
                            "abono"                     => $item_detail->abono,
                            "tipo_poliza"               => $item_detail->tipo_poliza,
                            "apply_afectable"           => null,
                            "motivo"                    => null,
                            "motivo_text"               => "POLIZA MANUAL ". $item_detail->contabilidadPoliza->concepto,
                            "transaccion_id"            => null,
                            "transaccion_detail_id"     => null,
                        ]);
                    }

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
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->created_by;

            }

            return true;

        } else
            return false;
    }
}
