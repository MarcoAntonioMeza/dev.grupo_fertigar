<?php
namespace app\models\reparto;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\venta\Venta;

/**
 * This is the model class for table "view_reparto".
 *
 * @property int $id ID
 * @property int $sucursal_id Sucursal ID
 * @property string $sucursal
 * @property int $status Status
 * @property string|null $created_by_user
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewReparto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_reparto';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sucursal_id' => 'Sucursal ID',
            'sucursal' => 'Sucursal',
            'status' => 'Status',
            'created_by_user' => 'Created By User',
            'created_by' => 'Created By',
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
                    'sucursal_id',
                    'sucursal',
                    'encargado',
                    'telefono',
                    'telefono_movil',
                    'status',
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


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'sucursal', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getConcentradoProducto($reparto_id)
    {

        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
                ->select([
                    "reparto_detalle.id",
                    'reparto_detalle.producto_id',
                    'producto.nombre as producto',
                    'SUM(reparto_detalle.cantidad)  as cantidad_total',
                ])
                ->from('reparto_detalle')
                ->leftJoin('producto','reparto_detalle.producto_id = producto.id')
                ->andWhere(["reparto_detalle.reparto_id" => $reparto_id])
                ->groupBy('producto.id');

        return $query->all();
    }

    public static function getDegloseInventario($reparto_id, $producto_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
        ->select([
            "reparto_detalle.id",
            'reparto_detalle.producto_id',
            'concat_ws(" ",trim(`cliente`.`nombre`),trim(`cliente`.`apellidos`)) AS cliente',
            'reparto_detalle.tipo',
            'producto.nombre as producto',
            'venta_detalle.cantidad as venta_cantidad',
            'venta.status as venta_status',
            'reparto_detalle.cantidad  as cantidad_asignada',
        ])
        ->from('reparto_detalle')
        ->leftJoin('producto','reparto_detalle.producto_id = producto.id')
        ->leftJoin('venta_detalle','reparto_detalle.venta_detalle_id = venta_detalle.id')
        ->leftJoin('venta','venta_detalle.venta_id = venta.id')
        ->leftJoin('cliente','venta.cliente_id = cliente.id')
        ->andWhere([ "and",
            ["=", "reparto_detalle.reparto_id", $reparto_id ],
            ["=","reparto_detalle.producto_id",$producto_id]
        ]);

        return $query->all();
    }

    public static function getReporteCentroNegocio($arr)
    {
        parse_str($arr['filters'], $filters);

        $responseArray = [];
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = (new Query())
        ->select([
            "reparto.id",
            "concat_ws(' ',sucursal.nombre, ' [ #',reparto.id, '] ') as reparto_name",
            'reparto.status',
            'reparto.created_at',
        ])
        ->from('reparto')
        ->innerJoin('sucursal','reparto.sucursal_id = sucursal.id')
        ->orderBy("reparto.id DESC");

        $date_ini =  $date_fin = null;

        if(isset($filters['InputFechaInicio']) && $filters['InputFechaInicio'] && isset($filters['InputFechaFin']) && $filters['InputFechaFin']){
            $date_ini = strtotime($filters['InputFechaInicio']);
            $date_fin = strtotime($filters['InputFechaFin']) + 86340;

            $query->andWhere(['between','reparto.created_at', $date_ini, $date_fin]);
        }

        foreach ($query->all() as $key => $item_central) {
            array_push($responseArray, [
                "id"                =>  $item_central['id'],
                "reparto_name"      =>  $item_central['reparto_name'],
                "valor_preventa"    => Reparto::getTotalPreventa($item_central['id']),
                "valor_tara_abierta"=> Reparto::getTotalTaraAbierta($item_central['id']),
                "valor_credito"     => Reparto::getTotalValorCredito($item_central['id']),
                "valor_contable"    => Reparto::getTotalValorContable($item_central['id']),
                "abono_cliente"     => Reparto::getTotalValorAbonos($item_central['id']),
                "devoluciones"      => Reparto::getTotalDevoluciones($item_central['id']),
                "status_text"       => Reparto::$statusList[$item_central['status']],
                "status_alert"      => Reparto::$statusAlertList[$item_central['status']],
            ]);
        }

        array_push($responseArray, [
            "id"                =>  null,
            "reparto_name"      => "TIENDA",
            "valor_preventa"    => 0,
            "valor_tara_abierta"=> 0,
            "valor_credito"     => Venta::getTotalCredito(4,$date_ini, $date_fin),
            "valor_contable"    => Venta::getTotalContado(4,$date_ini, $date_fin),
            "abono_cliente"     => Venta::getTotalAbonado(4,$date_ini, $date_fin),
            "devoluciones"      => Venta::getTotalDevolucion(4,$date_ini, $date_fin),
            "status_text"       => "------",
            "status_alert"      => "------",
        ]);


        return $responseArray;
    }
}
