<?php
namespace app\models\contabilidad;

use Yii;

/**
 * This is the model class for table "cuentas_contabilidad_rel".
 *
 * @property int $id
 * @property int $id_parent
 * @property int $id_child
 *
 * @property ContabilidadCuenta $child
 * @property ContabilidadCuenta $parent
 */
class ContabilidadCuentaRel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_cuenta_rel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_parent', 'id_child'], 'integer'],
            [['id_child'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadCuenta::className(), 'targetAttribute' => ['id_child' => 'id']],
            [['id_parent'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadCuenta::className(), 'targetAttribute' => ['id_parent' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_parent' => 'Id Parent',
            'id_child' => 'Id Child',
        ];
    }

    /**
     * Gets query for [[Child]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChild()
    {
        return $this->hasOne(ContabilidadCuenta::className(), ['id' => 'id_child']);
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(ContabilidadCuenta::className(), ['id' => 'id_parent']);
    }
}
