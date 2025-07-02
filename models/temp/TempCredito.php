<?php
namespace app\models\temp;

use Yii;
use app\models\user\User;
use app\models\cliente\Cliente;
use app\models\credito\Credito;

/**
 * This is the model class for table "temp_credito".
 *
 * @property int $id Credito
 * @property int|null $temp_venta_id Venta ID
 * @property int|null $cliente_id Cliente
 * @property string|null $trans_token_venta Token venta
 * @property int|null $pertenece Pertenece
 * @property float $monto Monto
 * @property int|null $fecha_credito Fecha credito
 * @property string|null $nota Nota
 * @property int $tipo Tipo
 * @property string|null $descripcion Descripcion
 * @property int $status Estatus
 * @property float|null $monto_pagado Monto pagado
 * @property int $created_by Creado por
 * @property int|null $created_at Creado por
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 *
 * @property Cliente $cliente
 * @property User $createdBy
 * @property TempVentaRuta $tempVenta
 * @property User $updatedBy
 */
class TempCredito extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_credito';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['temp_venta_id', 'cliente_id', 'pertenece', 'fecha_credito', 'tipo', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['monto', 'tipo'], 'required'],
            [['monto', 'monto_pagado'], 'number'],
            [['pertenece'],'default', 'value' => Credito::PERTENECE_SISTEMA ],
            [['nota', 'descripcion'], 'string'],
            [['trans_token_venta'], 'string', 'max' => 150],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['temp_venta_id'], 'exist', 'skipOnError' => true, 'targetClass' => TempVentaRuta::className(), 'targetAttribute' => ['temp_venta_id' => 'id']],
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
            'temp_venta_id' => 'Temp Venta ID',
            'cliente_id' => 'Cliente ID',
            'trans_token_venta' => 'Trans Token Venta',
            'pertenece' => 'Pertenece',
            'monto' => 'Monto',
            'fecha_credito' => 'Fecha Credito',
            'nota' => 'Nota',
            'tipo' => 'Tipo',
            'descripcion' => 'Descripcion',
            'status' => 'Status',
            'monto_pagado' => 'Monto Pagado',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
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
     * Gets query for [[TempVenta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTempVenta()
    {
        return $this->hasOne(TempVentaRuta::className(), ['id' => 'temp_venta_id']);
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

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->status     = Credito::STATUS_ACTIVE;
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
