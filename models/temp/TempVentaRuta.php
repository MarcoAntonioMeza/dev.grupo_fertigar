<?php
namespace app\models\temp;

use Yii;
use app\models\cliente\Cliente;
use app\models\user\User;
use app\models\reparto\Reparto;
use app\models\sucursal\Sucursal;
use app\models\venta\Venta;


/**
 * This is the model class for table "venta_ruta".
 *
 * @property int $id ID
 * @property int|null $cliente_id Cliente
 * @property int $sucursal_id Sucursal ID
 * @property int|null $reparto_id Reparto ID
 * @property int $tipo Tipo
 * @property int $status Estatus
 * @property float $total Total
 * @property string|null $nota_cancelacion Nota
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 *
 * @property Cliente $cliente
 * @property User $createdBy
 * @property Reparto $reparto
 * @property Sucursal $sucursal
 * @property User $updatedBy
 */
class TempVentaRuta extends \yii\db\ActiveRecord
{

    const IS_APPLY_ON   = 10;
    const IS_APPLY_OFF  = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_venta_ruta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'sucursal_id', 'reparto_id', 'tipo', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by','venta_id'], 'integer'],
            [['sucursal_id', 'tipo', 'status', 'total'], 'required'],
            [['total'], 'number'],
            [['is_apply'], 'default','value' => self::IS_APPLY_OFF],
            [['nota_cancelacion'], 'string'],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['reparto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reparto::className(), 'targetAttribute' => ['reparto_id' => 'id']],
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
            'venta_id' => 'venta_id',
            'cliente_id' => 'Cliente ID',
            'sucursal_id' => 'Sucursal ID',
            'reparto_id' => 'Reparto ID',
            'tipo' => 'Tipo',
            'status' => 'Status',
            'is_apply' => 'is_apply',
            'total' => 'Total',
            'nota_cancelacion' => 'Nota Cancelacion',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Cliente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCliente()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_id']);
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
     * Gets query for [[Reparto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReparto()
    {
        return $this->hasOne(Reparto::className(), ['id' => 'reparto_id']);
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
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getTempVentaRutaDetalle()
    {
        return $this->hasMany(TempVentaRutaDetalle::className(), ['temp_venta_ruta_id' => 'id']);
    }

    public static function getTempVenta($reparto_id)
    {
        $tempVenta  = [];
        $query      = TempVentaRuta::find()->andWhere(["reparto_id" => $reparto_id])->all();
        foreach ($query as $key => $item_query) {
            $detail_query   = TempVentaRutaDetalle::find()->andWhere(["temp_venta_ruta_id" => $item_query->id ])->all();
            $productos      = "";
            foreach ($detail_query as $key => $item_detail) {
                $productos .="<li>". $item_detail->producto->nombre ."</li>";
            }

            array_push($tempVenta, [
                "temp_venta_id" => $item_query->id,
                "venta_id"      => $item_query->venta_id,
                "cliente"       => $item_query->cliente->nombreCompleto,
                "total"         => $item_query->total,
                "is_apply"      => $item_query->is_apply,
                "productos"     => $productos,
                "tipo"          => Venta::$tipoList[$item_query->tipo],
                "created_at"    => date("Y-m-d h:i a",$item_query->created_at),
            ]);
        }

        return $tempVenta;
    }


    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at   = time();
                $this->created_by   = Yii::$app->user->identity ? Yii::$app->user->identity->id: $this->created_by;
                //$this->sucursal_id  = $this->sucursal_id;

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
