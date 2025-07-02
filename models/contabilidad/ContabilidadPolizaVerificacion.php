<?php
namespace app\models\contabilidad;

use Yii;
use app\models\user\User;

/**
 * This is the model class for table "contabilidad_poliza_verificacion".
 *
 * @property int $id ID
 * @property int $transaccion Tipo transaccion
 * @property float $total Total
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property ContabilidadPolizaVerificacionDetail[] $contabilidadPolizaVerificacionDetails
 */
class ContabilidadPolizaVerificacion extends \yii\db\ActiveRecord
{
    const PERTENECE_VENTA           = 10;
    const PERTENECE_COMPRA          = 20;
    const PERTENECE_TRASPASO        = 30;
    const PERTENECE_VENTANILLA      = 40;
    const PERTENECE_POLIZA_MANUAL   = 50;
    const PERTENECE_NOMINA          = 60;

    public static $transaccionList = [
        self::PERTENECE_VENTA           => "OPERACIONES - PUNTO DE VENTA",
        self::PERTENECE_COMPRA          => "OPERACIONES - COMPRA",
        self::PERTENECE_TRASPASO        => "OPERACIONES - INVENTARIO",
        self::PERTENECE_VENTANILLA      => "OPERACIONES - VENTANILLA",
        self::PERTENECE_NOMINA          => "OPERACIONES - NOMINA",
        self::PERTENECE_POLIZA_MANUAL   => "POLIZAS MANUALES",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_poliza_verificacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transaccion', 'total', 'created_at', 'created_by'], 'required'],
            [['transaccion', 'created_at', 'created_by'], 'integer'],
            [['total'], 'number'],
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
            'transaccion' => 'Transaccion',
            'total' => 'Total',
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
     * Gets query for [[ContabilidadPolizaVerificacionDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadPolizaVerificacionDetails()
    {
        return $this->hasMany(ContabilidadPolizaVerificacionDetail::className(), ['contabilidad_poliza_verificacion_id' => 'id']);
    }

    public function getContabilidadPolizaVerificacionDetailCount()
    {
        return $this->hasMany(ContabilidadPolizaVerificacionDetail::className(), ['contabilidad_poliza_verificacion_id' => 'id'])->count();
    }

    public static function getTransaccionPolizas($tipoTransaccion)
    {
        $responseArray = [];

        switch ($tipoTransaccion) {
            case ContabilidadPolizaVerificacion::PERTENECE_VENTA :
                $queryPoliza = ContabilidadPoliza::find()->andwhere([ "and",
                    [ "=", "tipo", ContabilidadPoliza::TIPO_SISTEMA ],
                    [ "=", "status", ContabilidadPoliza::STATUS_POR_VALIDAR ]
                ])
                ->andWhere([ "or",
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_VENTA ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_VENTA_CANCELACION ],
                ])->all();


                foreach ($queryPoliza as $key => $item_poliza) {
                    $queryConfiguracion = ContabilidadTransaccion::getConfigContable($item_poliza->id);

                    if (count($queryConfiguracion) > 0 ) {
                        array_push($responseArray, [
                            "poliza_id"         => $item_poliza->id,
                            "pertenece_text"    => ContabilidadTransaccion::$tipoList[$item_poliza->pertenece],
                            "referencia"        => "#" . str_pad($item_poliza->id,6,"0",STR_PAD_LEFT),
                            "concepto"          => $item_poliza->concepto,
                            "total"             => $item_poliza->total,
                            "configuracion_detail" => $queryConfiguracion,
                            "apply_poliza"          => 10,
                            "created_by_user"       => $item_poliza->createdBy->nombreCompleto,
                            "tipo"                  => ContabilidadPoliza::TIPO_SISTEMA,
                        ]);
                    }
                }

            break;

            case ContabilidadPolizaVerificacion::PERTENECE_COMPRA :
                $queryPoliza = ContabilidadPoliza::find()->andwhere([ "and",
                    [ "=", "tipo", ContabilidadPoliza::TIPO_SISTEMA ],
                    [ "=", "status", ContabilidadPoliza::STATUS_POR_VALIDAR ]
                ])
                ->andWhere([ "or",
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_COMPRA ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_COMPRA_CANCELACION ],
                ])->all();


                foreach ($queryPoliza as $key => $item_poliza) {
                    $queryConfiguracion = ContabilidadTransaccion::getConfigContable($item_poliza->id);

                    if (count($queryConfiguracion) > 0 ) {
                        array_push($responseArray, [
                            "poliza_id"         => $item_poliza->id,
                            "pertenece_text"    => ContabilidadTransaccion::$tipoList[$item_poliza->pertenece],
                            "referencia"        => "#" . str_pad($item_poliza->id,6,"0",STR_PAD_LEFT),
                            "concepto"          => $item_poliza->concepto,
                            "total"             => $item_poliza->total,
                            "configuracion_detail" => $queryConfiguracion,
                            "apply_poliza"          => 10,
                            "created_by_user"       => $item_poliza->createdBy->nombreCompleto,
                            "tipo"                  => ContabilidadPoliza::TIPO_SISTEMA,
                        ]);
                    }
                }
            break;

            case ContabilidadPolizaVerificacion::PERTENECE_TRASPASO :
                $queryPoliza = ContabilidadPoliza::find()->andwhere([ "and",
                    [ "=", "tipo", ContabilidadPoliza::TIPO_SISTEMA ],
                    [ "=", "status", ContabilidadPoliza::STATUS_POR_VALIDAR ]
                ])
                ->andWhere([ "or",
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_TRASPASO_INVENTARIO_SALIDA ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_TRASPASO_INVENTARIO_ENTRADA ],
                ])->all();


                foreach ($queryPoliza as $key => $item_poliza) {
                    $queryConfiguracion = ContabilidadTransaccion::getConfigContable($item_poliza->id);

                    if (count($queryConfiguracion) > 0 ) {
                        array_push($responseArray, [
                            "poliza_id"         => $item_poliza->id,
                            "pertenece_text"    => ContabilidadTransaccion::$tipoList[$item_poliza->pertenece],
                            "referencia"        => "#" . str_pad($item_poliza->id,6,"0",STR_PAD_LEFT),
                            "concepto"          => $item_poliza->concepto,
                            "total"             => $item_poliza->total,
                            "configuracion_detail" => $queryConfiguracion,
                            "apply_poliza"          => 10,
                            "created_by_user"       => $item_poliza->createdBy->nombreCompleto,
                            "tipo"                  => ContabilidadPoliza::TIPO_SISTEMA,
                        ]);
                    }
                }
            break;

            case ContabilidadPolizaVerificacion::PERTENECE_VENTANILLA :
                $queryPoliza = ContabilidadPoliza::find()->andwhere([ "and",
                    [ "=", "tipo", ContabilidadPoliza::TIPO_SISTEMA ],
                    [ "=", "status", ContabilidadPoliza::STATUS_POR_VALIDAR ]
                ])
                ->andWhere([ "or",
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_SALARIO_ORDINARIO_ABONO ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_SALARIO_ORDINARIO_RETIRO ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_SALARIO_EXTRAORDINARIO_ABONO ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_SALARIO_EXTRAORDINARIO_RETIRO ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_SALARIO_APORTACION_SOCIAL_ABONO ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_SALARIO_APORTACION_SOCIAL_RETIRO ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_PAGO_CREDENCIAL ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_PAGO_LIBRETA ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_AYUDA_ABONO ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_AYUDA_RETIRO ],
                ])->all();


                foreach ($queryPoliza as $key => $item_poliza) {
                    $queryConfiguracion = ContabilidadTransaccion::getConfigContable($item_poliza->id);

                    if (count($queryConfiguracion) > 0 ) {
                        array_push($responseArray, [
                            "poliza_id"         => $item_poliza->id,
                            "pertenece_text"    => ContabilidadTransaccion::$tipoList[$item_poliza->pertenece],
                            "referencia"        => "#" . str_pad($item_poliza->id,6,"0",STR_PAD_LEFT),
                            "concepto"          => $item_poliza->concepto,
                            "total"             => $item_poliza->total,
                            "configuracion_detail" => $queryConfiguracion,
                            "apply_poliza"          => 10,
                            "created_by_user"       => $item_poliza->createdBy->nombreCompleto,
                            "tipo"                  => ContabilidadPoliza::TIPO_SISTEMA,
                        ]);
                    }
                }
            break;

            case ContabilidadPolizaVerificacion::PERTENECE_POLIZA_MANUAL :
                $queryPoliza = ContabilidadPoliza::find()->andwhere([ "and",
                    [ "=", "tipo", ContabilidadPoliza::TIPO_MANUAL ],
                    [ "=", "status", ContabilidadPoliza::STATUS_POR_VALIDAR ]
                ])
                ->andWhere([ "or",
                    [ "IS", "pertenece", new \yii\db\Expression('null') ],
                ])->all();


                foreach ($queryPoliza as $key => $item_poliza) {
                    $queryConfiguracion = ContabilidadTransaccion::getConfigContable($item_poliza->id);

                    if (count($queryConfiguracion) > 0 ) {
                        array_push($responseArray, [
                            "poliza_id"         => $item_poliza->id,
                            "pertenece_text"    => "POLIZA MANUAL",
                            "referencia"        => "#" . str_pad($item_poliza->id,6,"0",STR_PAD_LEFT),
                            "concepto"          => $item_poliza->concepto,
                            "total"             => $item_poliza->total,
                            "configuracion_detail"  => $queryConfiguracion,
                            "apply_poliza"          => 10,
                            "created_by_user"       => $item_poliza->createdBy->nombreCompleto,
                            "tipo"                  => ContabilidadPolizaVerificacion::PERTENECE_POLIZA_MANUAL
                        ]);
                    }
                }
            break;

            case ContabilidadPolizaVerificacion::PERTENECE_NOMINA :
                $queryPoliza = ContabilidadPoliza::find()->andwhere([ "and",
                    [ "=", "tipo", ContabilidadPoliza::TIPO_SISTEMA ],
                    [ "=", "status", ContabilidadPoliza::STATUS_POR_VALIDAR ]
                ])
                ->andWhere([ "or",
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_NOMINA ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_NOMINA_ISR ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_NOMINA_IVA ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_NOMINA_IMSS ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_NOMINA_BONO ],
                    [ "=", "pertenece", ContabilidadTransaccion::OPERACION_NOMINA_DEDUCCION ],
                ])->all();


                foreach ($queryPoliza as $key => $item_poliza) {
                    $queryConfiguracion = ContabilidadTransaccion::getConfigContable($item_poliza->id);

                    if (count($queryConfiguracion) > 0 ) {
                        array_push($responseArray, [
                            "poliza_id"         => $item_poliza->id,
                            "pertenece_text"    => ContabilidadTransaccion::$tipoList[$item_poliza->pertenece],
                            "referencia"        => "#" . str_pad($item_poliza->id,6,"0",STR_PAD_LEFT),
                            "concepto"          => $item_poliza->concepto,
                            "total"             => $item_poliza->total,
                            "configuracion_detail" => $queryConfiguracion,
                            "apply_poliza"          => 10,
                            "created_by_user"       => $item_poliza->createdBy->nombreCompleto,
                            "tipo"                  => ContabilidadPoliza::TIPO_SISTEMA,
                        ]);
                    }
                }
            break;
        }

        return $responseArray;
    }

    public static function postCreateVerificacionPolizas($operacionVerificacionPoliza, $tipoTransaccion)
    {
        $connection = \Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try {

            $polizaVerificacion = $connection->createCommand()
            ->insert('contabilidad_poliza_verificacion', [
                'transaccion'           => $tipoTransaccion,
                'total'                 => 0,
                'created_by'            => Yii::$app->user->identity->id,
                'created_at'            => time(),
            ])->execute();

            $polizaVerificacionID = Yii::$app->db->getLastInsertID();

            $getTotalVerificacion = 0;
            foreach ($operacionVerificacionPoliza as $key => $item_poliza) {

                if ($item_poliza["apply_poliza"] == 10) { /* APLICA POLIZA PARA CORTE*/

                    $getTotalVerificacion = $getTotalVerificacion + floatval($item_poliza["total"]);


                    foreach ($item_poliza["configuracion_detail"] as $key => $item_configuracion) {
                        if ($item_poliza["tipo"] == ContabilidadPoliza::TIPO_SISTEMA) {
                            $polizaDetail = $connection->createCommand()
                            ->insert('contabilidad_poliza_detail', [
                                'contabilidad_poliza_id'                => $item_poliza["poliza_id"],
                                'contabilidad_transaccion_id'           => $item_configuracion["transaccion_id"],
                                'contabilidad_transaccion_detail_id'    => $item_configuracion["transaccion_detail_id"],
                                'cargo'                                 => $item_configuracion["cargo"],
                                'abono'                                 => $item_configuracion["abono"],
                                'created_by'                            => Yii::$app->user->identity->id,
                                'created_at'                            => time(),
                            ])->execute();
                        }
                    }


                    $polizaVerificacionDetail = $connection->createCommand()
                    ->insert('contabilidad_poliza_verificacion_detail', [
                        'contabilidad_poliza_verificacion_id'           => $polizaVerificacionID,
                        'contabilidad_poliza_id'                        => $item_poliza["poliza_id"],
                    ])->execute();


                    $contabilidadPoliza =  $connection->createCommand()
                    ->update('contabilidad_poliza', [
                        'status'        => ContabilidadPoliza::STATUS_VALIDADO,
                        'concepto'      => $item_poliza["concepto"],
                        'updated_by'    => Yii::$app->user->identity->id,
                        'updated_at'    => time(),
                    ], "id=". $item_poliza['poliza_id'] )->execute();


                    $updatecontabilidadPolizaVerificacion =  $connection->createCommand()
                    ->update('contabilidad_poliza_verificacion', [
                        'total'        => $getTotalVerificacion,
                    ], "id=". $polizaVerificacionID )->execute();
                }
            }

            $transaction->commit();


            return [
                "code"    => 202,
                "name"    => "POLIZAS",
                "message" => 'Se realizo correctamente la operacion',
                "type"    => "Success",
            ];

        } catch(Exception $e) {
            $transaction->rollback();
            return [
                "code"    => 10,
                "name"    => "POLIZAS",
                "message" => 'Ocurrio un error, intenta nuevamente',
                "type"    => "Error",
            ];
        }

    }
}
