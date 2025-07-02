<?php
namespace app\models\reporte;

use Yii;
use yii\db\Query;
use yii\web\Response;
/**
 * This is the model class for table "view_abastecimiento".
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
 * @property int|null $venta_id Venta ID
 * @property string|null $created_by_user
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewAbastecimiento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_abastecimiento';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'folio_operacion' => 'Folio Operacion',
            'almacen_sucursal_id' => 'Almacen Sucursal ID',
            'sucursal_surtio' => 'Sucursal Surtio',
            'sucursal_abastecio' => 'Sucursal Abastecio',
            'status' => 'Status',
            'producto_id' => 'Producto ID',
            'producto' => 'Producto',
            'cantidad' => 'Cantidad',
            'costo' => 'Costo',
            'venta_id' => 'Venta ID',
            'created_by_user' => 'Created By User',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
            'updated_by_user' => 'Updated By User',
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
                    "SQL_CALC_FOUND_ROWS `id`",
                    'folio_operacion',
                    'almacen_sucursal_id',
                    'sucursal_surtio',
                    'sucursal_abastecio',
                    'status',
                    'producto_id',
                    'producto',
                    'cantidad',
                    'costo',
                    'venta_id',
                    'created_by_user',
                    'created_by',
                    'created_at',
                    'updated_by',
                    'updated_at',
                    'updated_by_user',
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

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $query->andWhere(['almacen_sucursal_id' =>  $filters['sucursal_id']]);


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'folio_operacion', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
