<?php
namespace app\models\compra;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\user\User;
use app\models\sucursal\Sucursal;
use app\models\proveedor\Proveedor;
use app\models\inv\Operacion;
use app\models\venta\Venta;
use app\models\credito\Credito;
use app\models\cobro\CobroVenta;

/**
 * This is the model class for table "compra".
 *
 * @property int $id ID
 * @property int $sucursal_id Sucursal ID
 * @property int $tiempo_recorrido Tiempo aprox
 * @property int $fecha_salida Fecha salida
 * @property float $total Total
 * @property int $status Estatus
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 *
 * @property User $createdBy
 * @property Sucursal $sucursal
 * @property User $updatedBy
 * @property CompraDetalle[] $compraDetalles
 */
class Compra extends \yii\db\ActiveRecord
{

    const COMPRA_ESPECIAL   = 10;
    const COMPRA_GENERAL    = 20;

    const IS_CONFIRMACION_OFF   = 10;
    const IS_CONFIRMACION_ON    = 20;


    const IS_DIFERENCIA_OFF   = 10;
    const IS_DIFERENCIA_ON    = 20;

    const STATUS_TERMINADA       = 40;
    const STATUS_PORPAGAR       = 30;
    const STATUS_PROCESO        = 20;
    const STATUS_PAGADA         = 10;
    const STATUS_CANCEL         = 1;

    public static $statusList = [
        self::STATUS_TERMINADA  => 'TERMINADO',
        self::STATUS_PAGADA     => 'PAGADA',
        self::STATUS_PROCESO    => 'PROCESO',
        self::STATUS_PORPAGAR   => 'POR PAGAR',
        self::STATUS_CANCEL     => 'CANCEL',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'compra';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sucursal_id', 'tiempo_recorrido', 'fecha_salida', 'total', 'status'], 'required'],
            [['is_confirmacion'], 'default', 'value'=> self::IS_CONFIRMACION_OFF],
            [['is_diferencia'], 'default', 'value'=> self::IS_DIFERENCIA_OFF],
            [['sucursal_id', 'tiempo_recorrido', 'fecha_salida', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at','is_especial', 'venta_id','is_confirmacion','proveedor_id'], 'integer'],
            [['total','total_diferencia'], 'number'],
            [['lat', 'lng'], 'number'],
            [['nota'], 'string'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_id' => 'id']],
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
            'sucursal_id' => 'Sucursal ID',
            'tiempo_recorrido' => 'Tiempo Recorrido',
            'fecha_salida' => 'Fecha Salida',
            'total' => 'Total',
            'total_diferencia' => 'Total',
            'is_especial' => 'Es Especial',
            'venta_id' => 'Venta ID',
            'total' => 'Total',
            'status' => 'Status',
            'sucursal.nombre' => 'Almacen / Sucursal',
            'proveedor.nombre' => 'Proveedor',
            'proveedor_id' => 'Proveedor',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getItems()
    {
        $model = (new Query())
            ->select(['compra.id', 'fecha_salida','proveedor.nombre as proveedor'])
            ->from('compra')
            ->leftJoin('proveedor','proveedor.id = compra.proveedor_id')
            ->leftJoin('operacion', 'compra.id = operacion.compra_id')
            ->where(["and",
                ['<>','compra.status',self::STATUS_CANCEL],
                ['IS','operacion.compra_id', new \yii\db\Expression('null') ]
            ])
            ->orderBy(['compra.id' => SORT_DESC]);

        return ArrayHelper::map($model->all(), 'id', function($value){
                return "#" . $value["id"]."-" .$value["proveedor"] .' ['. ($value["fecha_salida"] ? date( 'Y-m-d', $value["fecha_salida"] ) : '' ) .']';
        });
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
     * Gets query for [[Sucursal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
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
     * Gets query for [[Proveedor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProveedor()
    {
        return $this->hasOne(Proveedor::className(), ['id' => 'proveedor_id']);
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

    public function getPagoCompra()
    {
        return $this->hasMany(CobroVenta::className(), ['compra_id' => 'id']);
    }

    /**
     * Gets query for [[CompraDetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompraDetalles()
    {
        return $this->hasMany(CompraDetalle::className(), ['compra_id' => 'id']);
    }


    public static function getCostoCompra($compra_id, $producto_id)
    {
        $producto = CompraDetalle::find()->andWhere([ "and",
            [ "=", "compra_id", $compra_id ],
            [ "=", "producto_id", $producto_id ],
        ])->one();

        return isset($producto->id) && $producto->id ? round($producto->costo,2) : 0;
    }

    public static function getPromedioCompra($productoID)
    {
        $query = CompraDetalle::find()
        ->innerJoin("compra", "compra_detalle.compra_id = compra.id" )
        ->andWhere([ "and", 
            ["=", "compra.status", Compra::STATUS_TERMINADA ],
            ["=", "compra_detalle.producto_id", $productoID ]
        ])->orderBy("compra_detalle.id desc")
        ->groupBy('compra_detalle.compra_id')
        ->limit(3)
        ->all();

        $responseArray = [
            "historial_venta"   => [],
            "valor"             => 0
        ];

        $costoArrays = [];

        foreach ($query as $key => $item) {
            array_push($costoArrays, $item->costo);
        }

        // Verificar que la lista no esté vacía para evitar división por cero
        if (count($costoArrays) > 0) {
            $promedio = array_sum($costoArrays) / count($costoArrays);
            
            $responseArray = [
                "historial_venta"   => [],
                "valor"             => round($promedio,2)
            ];   

            foreach ($query as $key => $itemOperacion) {
                array_push($responseArray["historial_venta"], [
                    "compra_id" => $itemOperacion->compra_id,
                    "sucursal"  => $itemOperacion->compra->sucursal->nombre,
                    "fecha"     => date('d/m/Y h:i',$itemOperacion->compra->created_at),
                    "costo"     => $itemOperacion->costo
                ]);
            }
        }

        return $responseArray;        
    }


    /**
     * Gets query for [[CompraDetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEntrada()
    {
        return $this->hasOne(Operacion::className(), ['compra_id' => 'id']);
    }

    public static function validItemCompra($compra_id, $producto_id, $cantidad)
    {
        $Compra  = Compra::findOne($compra_id);
        foreach ($Compra->compraDetalles as $key => $c_detalle) {
            if ($c_detalle->producto_id == $producto_id && $c_detalle->cantidad == $cantidad )
                return true;
        }
        return false;
    }


    public static function cierreCompra($compra_id)
    {
        $compra = Compra::findOne($compra_id);

        $calcula_total          = 0;
        
        $applyChangeOperacion   = false;
        foreach ($compra->compraDetalles as $key => $itemCompra) {
            $searchFind = false;

            if (isset($compra->entrada->id)) {
                foreach ($compra->entrada->operacionDetalles as $key => $itemOperacion) {
                    if ($itemOperacion->producto_id ==  $itemCompra->producto_id && $itemOperacion->cantidad ==  $itemCompra->cantidad) {
                    $searchFind = true; 
                    }  
                }
            }

            if(!$searchFind)
                $applyChangeOperacion = true;
        }

        if ($applyChangeOperacion) {

            if (isset($compra->entrada->id)) {
                foreach ($compra->entrada->operacionDetalles as $key => $item_entrada) {
                    $calcula_total = $calcula_total +  round(floatval($item_entrada->costo * $item_entrada->cantidad), 2);
                }
            }

            $compra->is_diferencia      = Compra::IS_DIFERENCIA_ON;
            $compra->total_diferencia   = $calcula_total;
            $compra->update();

            $getAllPago = CobroVenta::getCompraAll($compra->id);

            foreach ($getAllPago as $key => $metodo_pago) {
                if (count($getAllPago) == 1 ) {
                    $metodo_pago->cantidad    = $calcula_total;
                    $metodo_pago->update();

                    if ($metodo_pago->metodo_pago == CobroVenta::COBRO_CREDITO) {
                        $Credito = Credito::findOne([ "compra_id" => $compra->id ]);
                        if (isset($Credito->id)) {
                            $Credito->monto      = $calcula_total;
                            $Credito->save();
                        }
                    }
                }
            }
        }

    }

    public static function calPromedioProducto($compra_id)
    {

        $compra = Compra::findOne($compra_id);
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();

        try{
            foreach ($compra->compraDetalles as $key => $itemCompra) {

                $getPromedio = Compra::getPromedioCompra($itemCompra->producto_id);
                if(isset($getPromedio["valor"]) && $getPromedio["valor"] > 0){

                    $connection->createCommand()
                    ->update('producto', [
                        'costo'      => $getPromedio["valor"],
                    ], "id=". $itemCompra->producto_id )->execute();    
                }
            }

            $transaction->commit();

        } catch(\Exception $e) {
            $transaction->rollback();
            
            return false;
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
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->updated_by;
            }

            return true;

        } else
            return false;
    }
}
