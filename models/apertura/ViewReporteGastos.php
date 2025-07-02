<?php
namespace app\models\apertura;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_apertura_cierre".
 *
 * @property int $id ID
 * @property int $user_id Vendedor ID
 * @property string|null $vendedor
 * @property int $fecha_apertura Fecha apertura
 * @property int|null $fecha_cierre Fecha cierre
 * @property float $cantidad_caja Cantidad en caja
 * @property float|null $total Total
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string|null $created_by_user
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 * @property string|null $updated_by_user
 */
class ViewReporteGastos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_gastos_reporte';
    }
    public static function getGastosXcaja($id_caja)
    {
        $reportexcaja = self::find()->andWhere([ "and",
            [ "=", "id_caja", $id_caja ],
        ])->all();

        return $reportexcaja;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_caja' => 'ID DE CAJA',
            'singular' => 'CONCEPTO DE GASTO',
            'observacion' => 'Observacion',
            'created_at' => 'Fecha creación',
            'cantidad' => 'Cantidad',
            'tipo' => 'Tipo',
            'status' => 'Stado',
            'username' => 'Username',
            'updated_at' => 'Actualización de registro',
            'tipo_gasto_id' => 'Id tipo de gasto',
        ];
    }



}
