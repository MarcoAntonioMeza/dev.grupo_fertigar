<?php
namespace app\models\reporte;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_surtir".
 *
 * @property int $id ID
 * @property int $folio_operacion ID
 * @property int $almacen_sucursal_id Almacen
 * @property string|null $sucursal_surtio
 * @property string|null $sucursal_abastecio
 * @property int $status Estatus
 * @property int $producto_id Producto ID
 * @property string $producto Nombre
 * @property float $cantidad Cantidad
 * @property float|null $costo Costo
 * @property string|null $created_by_user
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewGastos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_gastos';
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'singular' => 'Nombre de gasto',
            'observacion' => 'Observaciòn',
            'created_at' => 'Creado',
            'cantidad' => 'Cantidad',
            'tipo' => 'Tipo',
            'status' => 'Status',
            'tipo_gasto_id' => 'Tipo de gasto',
        ];
    }

//------------------------------------------------------------------------------------------------//
// JSON Bootstrap Table
//------------------------------------------------------------------------------------------------//
    public static function getJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    'singular',
                    'observacion',
                    'created_at',
                    'cantidad',
                    'tipo',
                    'status',
                    'tipo_gasto_id',
                    'updated_at',
                    'username'
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
        if(isset($filters['date_range']) && $filters['date_range']){
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $query->andWhere(['between','updated_at', $date_ini, $date_fin]);
        }

        if (isset($filters['tipo_gasto_id']) && $filters['tipo_gasto_id'])
            $query->andWhere(['tipo_gasto_id' =>  $filters['tipo_gasto_id']]);


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
