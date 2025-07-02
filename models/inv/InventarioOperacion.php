<?php
namespace app\models\inv;

use Yii;
use yii\db\Query;
use app\models\user\User;
use app\models\sucursal\Sucursal;
use app\models\producto\Producto;
use app\models\inv\InvProductoSucursal;
use app\models\trans\TransProductoInventario;

/**
 * This is the model class for table "inventario_operacion".
 *
 * @property int $id ID
 * @property int $inventario_sucursal_id Sucursal ID
 * @property int $asignado_id Asignado
 * @property int $status Estatus
 * @property int $tipo Tipo
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 *
 * @property User $createdBy
 * @property Sucursal $inventarioSucursal
 * @property User $updatedBy
 * @property InventarioOperacionDetalle[] $inventarioOperacionDetalles
 */
class InventarioOperacion extends \yii\db\ActiveRecord
{
    const TIPO_AJUSTE_PARCIAL     = 10;
    const TIPO_AJUSTE_COMPLETA    = 20;

    const VERIFICACION_ON       = 10;
    const VERIFICACION_OFF      = 20;

    const STATUS_CANCEL           = 1;
    const STATUS_SOLICITUD        = 10;
    const STATUS_PROCESO          = 20;
    const STATUS_REVISION         = 30;
    const STATUS_TERMINADO        = 40;


    public static $statusList = [
        self::STATUS_SOLICITUD          => "SOLICITUD",
        self::STATUS_PROCESO            => "PROCESO",
        self::STATUS_REVISION           => "REVISION",
        self::STATUS_TERMINADO          => "TERMINADO",
        self::STATUS_CANCEL             => "CANCELADO",
    ];

    public static $tipoList = [
        self::TIPO_AJUSTE_PARCIAL     => 'AJUSTE DE INVENTARIO PARCIAL',
        self::TIPO_AJUSTE_COMPLETA    => 'AJUSTE DE INVENTARIO COMPLETO',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inventario_operacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inventario_sucursal_id', 'asignado_id', 'tipo'], 'required'],
            [['status'],'default','value' => InventarioOperacion::STATUS_SOLICITUD],
            [['verificacion'],'default','value' => InventarioOperacion::VERIFICACION_OFF],
            [['inventario_sucursal_id', 'asignado_id', 'status', 'tipo', 'created_at', 'created_by', 'updated_at', 'updated_by','verificacion'], 'integer'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['inventario_sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['inventario_sucursal_id' => 'id']],
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
            'inventario_sucursal_id' => 'Inventario Sucursal ID',
            'asignado_id' => 'Asignado ID',
            'status' => 'Status',
            'tipo' => 'Tipo',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
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


    public function getAsignado()
    {
        return $this->hasOne(User::className(), ['id' => 'asignado_id']);
    }

    /**
     * Gets query for [[InventarioSucursal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventarioSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'inventario_sucursal_id']);
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
     * Gets query for [[InventarioOperacionDetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventarioOperacionDetalles()
    {
        return $this->hasMany(InventarioOperacionDetalle::className(), ['inventario_operacion_id' => 'id']);
    }

    public static function cantidadEncontrada($operacion_id)
    {
        $responseArray = [];

        $cantidadArray = (new Query())
                        ->select([

                            "producto.tipo_medida",
                            "inventario_operacion.inventario_sucursal_id",
                            "inventario_operacion_detalle.producto_id",
                            "inventario_operacion_detalle.cantidad_inventario",
                            "inventario_operacion_detalle.cantidad_old",
                        ])
                        ->from("inventario_operacion_detalle")
                        ->innerJoin("inventario_operacion","inventario_operacion_detalle.inventario_operacion_id = inventario_operacion.id")
                        ->innerJoin("producto","inventario_operacion_detalle.producto_id = producto.id")
                        ->andWhere(["inventario_operacion_id" => $operacion_id])
                        //->groupBy("producto.tipo_medida")
                        ->all();

        foreach ($cantidadArray as $key => $item_inventario)
        {
            $diferencia = floatval($item_inventario["cantidad_inventario"]) -  floatval($item_inventario["cantidad_old"]);

            if ($diferencia > 0 ) {

                $is_add = true;

                foreach ($responseArray as $key_response => $item_response) {
                    if ( $item_response["tipo_medida"] == $item_inventario["tipo_medida"]) {
                        $responseArray[$key_response]["cantidad"] = floatval($responseArray[$key_response]["cantidad"]) + $diferencia;
                        $is_add = false;
                    }
                }

                if ($is_add) {
                    array_push($responseArray, [
                        "tipo_medida"   => $item_inventario["tipo_medida"],
                        "cantidad"      => $diferencia,
                    ]);
                }
            }
        }

        return $responseArray;
    }

    public static function cantidadPerdida($operacion_id)
    {
        $responseArray = [];

        $cantidadArray = (new Query())
                        ->select([

                            "producto.tipo_medida",
                            "inventario_operacion.inventario_sucursal_id",
                            "inventario_operacion_detalle.producto_id",
                            "inventario_operacion_detalle.cantidad_inventario",
                            "inventario_operacion_detalle.cantidad_old",
                        ])
                        ->from("inventario_operacion_detalle")
                        ->innerJoin("inventario_operacion","inventario_operacion_detalle.inventario_operacion_id = inventario_operacion.id")
                        ->innerJoin("producto","inventario_operacion_detalle.producto_id = producto.id")
                        ->andWhere(["inventario_operacion_id" => $operacion_id])
                        //->groupBy("producto.tipo_medida")
                        ->all();

        foreach ($cantidadArray as $key => $item_inventario)
        {
            $diferencia = $item_inventario["cantidad_inventario"] -  floatval($item_inventario["cantidad_old"]);

            if ($diferencia < 0 ) {

                $is_add = true;

                foreach ($responseArray as $key_response => $item_response) {
                    if ( $item_response["tipo_medida"] == $item_inventario["tipo_medida"]) {
                        $responseArray[$key_response]["cantidad"] = floatval($responseArray[$key_response]["cantidad"]) + $diferencia;
                        $is_add = false;
                    }
                }

                if ($is_add) {
                    array_push($responseArray, [
                        "tipo_medida"   => $item_inventario["tipo_medida"],
                        "cantidad"      => $diferencia,
                    ]);
                }
            }
        }

        return $responseArray;

    }

    public static function calculaCostoPerdida($operacion_id, $unidad_medida)
    {
        $cantidad_perdida = 0;

        $cantidadArray = (new Query())
                        ->select([
                            "producto.tipo_medida",
                            "producto.costo",
                            "inventario_operacion.inventario_sucursal_id",
                            "inventario_operacion_detalle.producto_id",
                            "inventario_operacion_detalle.cantidad_inventario",
                            "inventario_operacion_detalle.cantidad_old",
                        ])
                        ->from("inventario_operacion_detalle")
                        ->innerJoin("inventario_operacion","inventario_operacion_detalle.inventario_operacion_id = inventario_operacion.id")
                        ->innerJoin("producto","inventario_operacion_detalle.producto_id = producto.id")
                        ->andWhere(["and",
                            ["=","inventario_operacion_id", $operacion_id],
                            ["=", "producto.tipo_medida", $unidad_medida]
                        ])
                        ->all();

        foreach ($cantidadArray as $key => $item_inventario)
        {
            $diferencia = $item_inventario["cantidad_inventario"] -  floatval($item_inventario["cantidad_old"]);

            if ($diferencia < 0 ) {

                $cantidad_perdida = floatval($cantidad_perdida) + ( floatval($item_inventario["costo"]) * ($diferencia * -1) );
            }
        }

        return $cantidad_perdida;
    }

    public static function cantidadPerdidaOperacionAjuste($operacion_id)
    {
        $responseArray = [];

        $cantidadArray = (new Query())
                        ->select([

                            "producto.tipo_medida",
                            "inventario_operacion.inventario_sucursal_id",
                            "inventario_operacion_detalle.producto_id",
                            "inventario_operacion_detalle.cantidad_inventario",
                            "inventario_operacion_detalle.cantidad_old",
                        ])
                        ->from("inventario_operacion_detalle")
                        ->innerJoin("inventario_operacion","inventario_operacion_detalle.inventario_operacion_id = inventario_operacion.id")
                        ->innerJoin("producto","inventario_operacion_detalle.producto_id = producto.id")
                        ->andWhere(["inventario_operacion_id" => $operacion_id])
                        //->groupBy("producto.tipo_medida")
                        ->all();

        foreach ($cantidadArray as $key => $item_inventario)
        {
            $diferencia = $item_inventario["cantidad_inventario"] -  floatval($item_inventario["cantidad_old"]);

            if ($diferencia < 0 ) {

                $is_add = true;

                foreach ($responseArray as $key_response => $item_response) {
                    if ( $item_response["tipo_medida"] == $item_inventario["tipo_medida"]) {
                        $responseArray[$key_response]["cantidad"] = floatval($responseArray[$key_response]["cantidad"]) + $diferencia;
                        $is_add = false;
                    }
                }

                if ($is_add) {
                    array_push($responseArray, [
                        "tipo_medida"   => $item_inventario["tipo_medida"],
                        "cantidad"      => $diferencia,
                    ]);
                }
            }
        }

        return $responseArray;

    }

    public static function cantidadTotal($operacion_id)
    {
        $cantidadArray = (new Query())
                        ->select([
                            "producto.tipo_medida",
                            "SUM(inventario_operacion_detalle.cantidad_inventario)  AS cantidad",
                        ])
                        ->from("inventario_operacion_detalle")
                        ->innerJoin("producto","inventario_operacion_detalle.producto_id = producto.id")
                        ->andWhere(["inventario_operacion_id" => $operacion_id])
                        ->groupBy("producto.tipo_medida")
                        ->all();

        return $cantidadArray;
    }



    public static function getCountProducto($sucursal_id)
    {
        return InvProductoSucursal::find()->innerJoin("producto","inv_producto_sucursal.producto_id = producto.id")
        ->andWhere(["and",
            ["=", "inv_producto_sucursal.sucursal_id", $sucursal_id ],
            ["=", "producto.status", Producto::STATUS_ACTIVE ],
            [">", "inv_producto_sucursal.cantidad", 0 ],
        ])->count();
    }

    public function updateOperacion()
    {
        if ($this->tipo == InventarioOperacion::TIPO_AJUSTE_COMPLETA && $this->status == InventarioOperacion::STATUS_SOLICITUD) {
            foreach ($this->inventarioOperacionDetalles as $key => $item_detail) {
                   $item_detail->delete();
            }
        }
    }

    public static function getProductoInventario($sucursal_id)
    {
        return InvProductoSucursal::find()->innerJoin("producto","inv_producto_sucursal.producto_id = producto.id")
        ->andWhere(["and",
            ["=", "inv_producto_sucursal.sucursal_id", $sucursal_id ],
            ["=", "producto.status", Producto::STATUS_ACTIVE ],
            [">", "inv_producto_sucursal.cantidad", 0 ],
        ])->orderBy("producto.nombre ASC")->all();
    }

    public static function getInventario($solicitud_id)
    {
        $InventarioOperacion    = self::findOne($solicitud_id);
        $producto_array         = [];
        if ($InventarioOperacion->tipo == self::TIPO_AJUSTE_PARCIAL) {

            foreach ($InventarioOperacion->inventarioOperacionDetalles as $key => $item_producto) {
                array_push($producto_array, [
                    "item_id"       => $item_producto->id,
                    "sucursal_id"   => $InventarioOperacion->inventario_sucursal_id,
                    "producto"      => $item_producto->producto->nombre,
                    "producto_id"   => $item_producto->producto_id,
                    "cantidad"      => floatval($item_producto->cantidad_inventario),
                    "status"        => InventarioOperacionDetalle::getStatusCargada($solicitud_id, $item_producto->producto_id),
                ]);
            }
        }

        if ($InventarioOperacion->tipo == self::TIPO_AJUSTE_COMPLETA) {
            $query = InvProductoSucursal::find()->innerJoin("producto","inv_producto_sucursal.producto_id = producto.id")
                    ->andWhere(["and",
                        ["=", "inv_producto_sucursal.sucursal_id", $InventarioOperacion->inventario_sucursal_id ],
                        ["=", "producto.status", Producto::STATUS_ACTIVE ],
                        [">", "inv_producto_sucursal.cantidad", 0 ],
                    ])->orderBy("producto.nombre ASC")->all();


            foreach ($query as $key => $item_producto) {
                array_push($producto_array, [
                    "item_id"       => $item_producto->id,
                    "sucursal_id"   => $InventarioOperacion->inventario_sucursal_id,
                    "producto"      => $item_producto->producto->nombre,
                    "producto_id"   => $item_producto->producto_id,
                    "cantidad"      => InventarioOperacionDetalle::getCantidadCargada($solicitud_id, $item_producto->producto_id),
                    "status"        => InventarioOperacionDetalle::getStatusCargada($solicitud_id, $item_producto->producto_id),
                ]);
            }

        }

        return $producto_array;
    }

    public static function getAjustarInventario($solicitud_id)
    {
        $InventarioOperacion    = self::findOne($solicitud_id);
        $producto_array         = [];
        if ($InventarioOperacion->tipo == self::TIPO_AJUSTE_PARCIAL) {

            foreach ($InventarioOperacion->inventarioOperacionDetalles as $key => $item_producto) {
                array_push($producto_array, [
                    "item_id"           => $item_producto->id,
                    "sucursal_id"       => $InventarioOperacion->inventario_sucursal_id,
                    "producto"          => $item_producto->producto->nombre,
                    "producto_id"       => $item_producto->producto_id,
                    "unidad_medida"     => Producto::$medidaList[$item_producto->producto->tipo_medida],
                    "cantidad"          => floatval($item_producto->cantidad_inventario),
                    "cantidad_sistema"  => InvProductoSucursal::getInventarioActual($InventarioOperacion->inventario_sucursal_id, $item_producto->producto_id),
                    "status"            => InventarioOperacionDetalle::getStatusCargada($solicitud_id, $item_producto->producto_id),
                ]);
            }
        }

        if ($InventarioOperacion->tipo == self::TIPO_AJUSTE_COMPLETA) {
            $query = InvProductoSucursal::find()->innerJoin("producto","inv_producto_sucursal.producto_id = producto.id")
                    ->andWhere(["and",
                        ["=", "inv_producto_sucursal.sucursal_id", $InventarioOperacion->inventario_sucursal_id ],
                        ["=", "producto.status", Producto::STATUS_ACTIVE ],
                        [">", "inv_producto_sucursal.cantidad", 0 ]
                    ])->orderBy("producto.nombre ASC")->all();


            foreach ($query as $key => $item_producto) {
                array_push($producto_array, [
                    "item_id"           => $item_producto->id,
                    "sucursal_id"       => $InventarioOperacion->inventario_sucursal_id,
                    "producto"          => $item_producto->producto->nombre,
                    "producto_id"       => $item_producto->producto_id,
                    "unidad_medida"     => Producto::$medidaList[$item_producto->producto->tipo_medida],
                    "cantidad"          => InventarioOperacionDetalle::getCantidadCargada($solicitud_id, $item_producto->producto_id),
                    "cantidad_sistema"  => InvProductoSucursal::getInventarioActual($InventarioOperacion->inventario_sucursal_id, $item_producto->producto_id),
                    "status"            => InventarioOperacionDetalle::getStatusCargada($solicitud_id, $item_producto->producto_id),
                ]);
            }
        }

        return $producto_array;
    }

    public static function saveInventario($inventario_array, $solicitud_id, $is_operador)
    {
        $InventarioOperacion    = self::findOne($solicitud_id);
        $inventarioObject       = json_decode($inventario_array);

        try {
            foreach ($inventarioObject as $key => $item_producto) {


                if ($item_producto->producto_id) {

                    $InventarioOperacionDetalle = InventarioOperacionDetalle::find()->andWhere(["and",
                        ["=", "inventario_operacion_id", $solicitud_id],
                        ["=", "producto_id", $item_producto->producto_id],
                    ])->one();

                    if (!isset($InventarioOperacionDetalle->id))
                        $InventarioOperacionDetalle = new InventarioOperacionDetalle();


                    $InventarioOperacionDetalle->producto_id                = $item_producto->producto_id;
                    $InventarioOperacionDetalle->inventario_operacion_id    = $solicitud_id;
                    $InventarioOperacionDetalle->tipo                       = intval($item_producto->status) == 10 ? InventarioOperacionDetalle::TIPO_VIGENTE : InventarioOperacionDetalle::TIPO_REMOVE;
                    $InventarioOperacionDetalle->cantidad_inventario        = intval($item_producto->status) == 10 ? $item_producto->cantidad : 0;

                    $InventarioOperacionDetalle->save();
                }
            }
            if ($is_operador) {
                $InventarioOperacion->status = self::STATUS_PROCESO;
                $InventarioOperacion->save();
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public static function validInvetario($solicitud_id)
    {
        $is_valid = true;

        $InventarioOperacion = self::findOne($solicitud_id);

        foreach ($InventarioOperacion->inventarioOperacionDetalles as $key => $item_inventario) {
            if (floatval($item_inventario->cantidad_inventario) != floatval(InvProductoSucursal::getInventarioActual($InventarioOperacion->inventario_sucursal_id, $item_inventario->producto_id)) ) {
                $is_valid = false;
            }
        }

        return $is_valid;
    }



    public static function loadInventarioOperador($solicitud_id)
    {
        $InventarioOperacion = self::findOne($solicitud_id);
        foreach ($InventarioOperacion->inventarioOperacionDetalles as $key => $item_inventario) {

            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $InventarioOperacion->inventario_sucursal_id ], [ "=", "producto_id", $item_inventario->producto_id ] ] )->one();

            if (intval($item_inventario->tipo) == InventarioOperacionDetalle::TIPO_VIGENTE) {

                if (isset($InvProducto->id)) {
                    $cantidadProducto                   = $InvProducto->cantidad;
                    $item_inventario->cantidad_old      = $InvProducto->cantidad;
                    $InvProducto->cantidad  = $item_inventario->cantidad_inventario;
                    $InvProducto->save();

                    if ($cantidadProducto != $item_inventario->cantidad_inventario){
                        if ($cantidadProducto > $item_inventario->cantidad_inventario)
                            TransProductoInventario::saveTransAjuste($InventarioOperacion->inventario_sucursal_id,null,$item_inventario->producto_id,  ( $cantidadProducto  - $item_inventario->cantidad_inventario ) ,TransProductoInventario::TIPO_SALIDA);
                        else
                            TransProductoInventario::saveTransAjuste($InventarioOperacion->inventario_sucursal_id,null,$item_inventario->producto_id, ( $item_inventario->cantidad_inventario - $cantidadProducto ),TransProductoInventario::TIPO_ENTRADA);
                    }
                }else{
                    $InvProductoSucursal                =  new InvProductoSucursal();
                    $InvProductoSucursal->sucursal_id   = $InventarioOperacion->inventario_sucursal_id;
                    $InvProductoSucursal->producto_id   = $item_inventario->producto_id;
                    $InvProductoSucursal->cantidad      = $item_inventario->cantidad_inventario;
                    $InvProductoSucursal->save();

                    if (intval($item_inventario->cantidad_inventario) > 0 )
                        TransProductoInventario::saveTransAjuste($InventarioOperacion->inventario_sucursal_id,null,$item_inventario->producto_id, $item_inventario->cantidad_inventario,TransProductoInventario::TIPO_ENTRADA);
                }
            }

            if (intval($item_inventario->tipo) == InventarioOperacionDetalle::TIPO_REMOVE) {
                if (isset($InvProducto->id)) {
                    //$item_inventario->cantidad_old  = $InvProducto->cantidad;
                    try {

                        $InvProducto->delete();

                    } catch (Exception $e) {

                    }
                }
            }
            $item_inventario->save();
        }

        $InventarioOperacion->status = InventarioOperacion::STATUS_TERMINADO;
        $InventarioOperacion->save();

        return true;

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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {

        }else{
            // Guardamos un registro de los cambios
            $this->updateOperacion();
        }
    }

}
