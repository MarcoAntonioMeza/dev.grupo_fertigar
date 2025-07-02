<?php
namespace app\models\tranformacion;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_tranformacion".
 *
 * @property int $id ID
 * @property int|null $sucursal_id Sucursal ID
 * @property string $sucursal
 * @property int $motivo_id Motivo
 * @property int|null $producto_new Producto New
 * @property string|null $producto Nombre
 * @property float|null $producto_cantidad Producto cantidad
 * @property int $created_at Creado
 * @property string|null $created_by_user
 * @property int $created_by Creado por
 */
class ViewTranformacionMerma extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_merma';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'sucursal_id', 'motivo_id', 'producto_new', 'created_at', 'created_by'], 'integer'],
            [['sucursal', 'motivo_id', 'created_at', 'created_by'], 'required'],
            [['producto_cantidad'], 'number'],
            [['sucursal'], 'string', 'max' => 200],
            [['producto'], 'string', 'max' => 150],
            [['created_by_user'], 'string', 'max' => 201],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'motivo_id' => 'Sucursal ID',
            'nota' => 'Sucursal',
            'producto_cantidad' => 'Motivo ID',
            'nombre' => 'Producto New',
            'nombre_sucursal' => 'Producto',
            'created_at' => 'FECHA DE CREACIÓN',
            'sucursal_id' => 'SUCURSAL ID'
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
                    'motivo_id',
                    'nota',
                    'producto_cantidad',
                    'nombre',
                    'nombre_sucursal',
                    'created_at',
                    'sucursal_id'
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);

            /**==============================================
             * Filtamos por asiganacion agente de ventas
             ================================================*/
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

             if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
            $query->andWhere(['sucursal_id' =>  $filters['sucursal_id']]);

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
}
