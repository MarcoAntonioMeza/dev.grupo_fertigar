<?php
namespace app\models\inv;

use Yii;
use app\models\inv\Operacion;
use app\models\inv\OperacionDetalle;
use app\models\user\User;
use app\models\producto\Producto;
/**
 * This is the model class for table "traspaso_operacion".
 *
 * @property int $id ID
 * @property int $operacion_envia_id Operacion ID
 * @property int $operacion_detalle_id Operacion detalle ID
 * @property int $operador_id Operador id
 * @property int $producto_id Producto ID
 * @property float|null $cantidad_old Cantidad old
 * @property float|null $cantidad_new Cantidad nueva
 * @property int $status status
 * @property int|null $created_at Creado
 * @property int|null $updated_at Update at
 *
 * @property OperacionDetalle $operacionDetalle
 * @property Operacion $operacion
 * @property User $operador
 * @property Producto $producto
 */
class TraspasoOperacion extends \yii\db\ActiveRecord
{
    const STATUS_PROCESO = 10;
    const STATUS_CERRADO = 30;

    public static $statusList = [
        self::STATUS_PROCESO => "PROCESO",
        self::STATUS_CERRADO => "CERRADO",
    ];  
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspaso_operacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['operacion_envia_id', 'operacion_detalle_id', 'operador_id', 'producto_id', 'status'], 'required'],
            [['operacion_envia_id', 'operacion_detalle_id', 'operador_id', 'producto_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['cantidad_old', 'cantidad_new'], 'number'],
            [['operacion_detalle_id'], 'exist', 'skipOnError' => true, 'targetClass' => OperacionDetalle::className(), 'targetAttribute' => ['operacion_detalle_id' => 'id']],
            [['operacion_envia_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operacion::className(), 'targetAttribute' => ['operacion_envia_id' => 'id']],
            [['operador_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['operador_id' => 'id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'operacion_envia_id' => 'Operacion ID',
            'operacion_detalle_id' => 'Operacion Detalle ID',
            'operador_id' => 'Operador ID',
            'producto_id' => 'Producto ID',
            'cantidad_old' => 'Cantidad Old',
            'cantidad_new' => 'Cantidad New',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[OperacionDetalle]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperacionDetalle()
    {
        return $this->hasOne(OperacionDetalle::className(), ['id' => 'operacion_detalle_id']);
    }

    /**
     * Gets query for [[Operacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperacion()
    {
        return $this->hasOne(Operacion::className(), ['id' => 'operacion_envia_id']);
    }

    /**
     * Gets query for [[Operador]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperador()
    {
        return $this->hasOne(User::className(), ['id' => 'operador_id']);
    }

    /**
     * Gets query for [[Producto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Producto::className(), ['id' => 'producto_id']);
    }

    public static function saveIncidenciaOperacionTranspaso($enviaId,$enviaDetalleID, $recibeID, $operadorID, $productoID, $cantidadOld, $cantidadNew)
    {
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $Operacion = $connection->createCommand()
            ->insert('traspaso_operacion', [
                    'operacion_envia_id'     => $enviaId,
                    'operacion_detalle_id'   => $enviaDetalleID,
                    'operacion_recibe_id'    => $recibeID,
                    'operador_id'   => $operadorID,
                    'producto_id'   => $productoID,
                    'cantidad_old'  => $cantidadOld,
                    'cantidad_new'  => $cantidadNew,
                    'status'        => self::STATUS_PROCESO,
                    'created_at'    => time(),
            ])->execute();

            $transaction->commit();

        } catch(\Exception $e) {    
            $transaction->rollback();
        }
    }
}
