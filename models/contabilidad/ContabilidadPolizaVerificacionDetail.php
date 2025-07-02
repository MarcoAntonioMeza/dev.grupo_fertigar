<?php
namespace app\models\contabilidad;

use Yii;

/**
 * This is the model class for table "contabilidad_poliza_verificacion_detail".
 *
 * @property int $id ID
 * @property int $contabilidad_poliza_verificacion_id Contabilidad poliza verificacion ID
 * @property int $contabilidad_poliza_id Contabilidad poliza ID
 *
 * @property ContabilidadPoliza $contabilidadPoliza
 * @property ContabilidadPolizaVerificacion $contabilidadPolizaVerificacion
 */
class ContabilidadPolizaVerificacionDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_poliza_verificacion_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contabilidad_poliza_verificacion_id', 'contabilidad_poliza_id'], 'required'],
            [['contabilidad_poliza_verificacion_id', 'contabilidad_poliza_id'], 'integer'],
            [['contabilidad_poliza_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadPoliza::className(), 'targetAttribute' => ['contabilidad_poliza_id' => 'id']],
            [['contabilidad_poliza_verificacion_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadPolizaVerificacion::className(), 'targetAttribute' => ['contabilidad_poliza_verificacion_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contabilidad_poliza_verificacion_id' => 'Contabilidad Poliza Verificacion ID',
            'contabilidad_poliza_id' => 'Contabilidad Poliza ID',
        ];
    }

    /**
     * Gets query for [[ContabilidadPoliza]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadPoliza()
    {
        return $this->hasOne(ContabilidadPoliza::className(), ['id' => 'contabilidad_poliza_id']);
    }

    /**
     * Gets query for [[ContabilidadPolizaVerificacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadPolizaVerificacion()
    {
        return $this->hasOne(ContabilidadPolizaVerificacion::className(), ['id' => 'contabilidad_poliza_verificacion_id']);
    }
}
