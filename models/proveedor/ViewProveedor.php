<?php
namespace app\models\proveedor;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\compra\Compra;

/**
 * This is the model class for table "view_proveedor".
 *
 * @property int $id ID
 * @property string|null $nombre Nombre
 * @property string|null $rfc RFC
 * @property string|null $razon_social Razon social
 * @property string|null $email Email
 * @property int|null $tipo_id Tipo
 * @property string|null $tipo Singular
 * @property string|null $tel Telefono
 * @property string|null $descripcion Descripcion
 * @property string|null $notas Notas
 * @property int $status Estatus
 * @property int $created_by Creado por
 * @property string|null $created_by_user
 * @property int $created_at Creado por
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewProveedor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_proveedor';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'rfc' => 'Rfc',
            'razon_social' => 'Razon Social',
            'email' => 'Email',
            'tipo_id' => 'Tipo ID',
            'tipo' => 'Tipo',
            'tel' => 'Tel',
            'descripcion' => 'Descripcion',
            'notas' => 'Notas',
            'status' => 'Status',
            'created_by' => 'Created By',
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
                    'nombre',
                    'rfc',
                    'razon_social',
                    'email',
                    'tel',
                    'descripcion',
                    'notas',
                    'total_adeudo',
                    'status',
                    'created_by',
                    'created_by_user',
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


            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'tel', $search],
                    ['like', 'nombre', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getReporteProveedorSaldoJsonBtt($arr)
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


            $select = [
                "SQL_CALC_FOUND_ROWS `view_proveedor`.`id`",
                'view_proveedor.nombre as proveedor',
            ];


            $select = array_merge($select, [
                '( SELECT ( sum(credito.monto) - sum(credito.monto_pagado))  FROM credito  LEFT JOIN compra on credito.compra_id = compra.id WHERE credito.tipo  = 20 and ( compra.proveedor_id = view_proveedor.id or credito.proveedor_id = view_proveedor.id ) and ( credito.status = 10 or credito.status = 40) ) as por_pagar',

                '( SELECT credito_abono.created_at FROM credito   INNER JOIN credito_abono on credito.id = credito_abono.credito_id LEFT JOIN compra on credito.compra_id = compra.id  WHERE credito.tipo  = 20  and ( compra.proveedor_id = view_proveedor.id or credito.proveedor_id = view_proveedor.id )  and ( credito.status = 10 or credito.status = 40) and credito_abono.status = 10 order by credito_abono.id  DESC  limit 1 ) AS fecha_ultimo_abono'
            ]);



            $query = (new Query())
                ->select($select)
                ->from('view_proveedor')
                ->andWhere(["and",
                    [ "=","view_proveedor.status", Proveedor::STATUS_ACTIVE ],
                    [ ">","( SELECT ( sum(credito.monto) - sum(credito.monto_pagado))  FROM credito  LEFT JOIN compra on credito.compra_id = compra.id WHERE credito.tipo  = 20 and ( compra.proveedor_id = view_proveedor.id or credito.proveedor_id = view_proveedor.id ) and ( credito.status = 10 or credito.status = 40) )", 0 ]
                ])
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'nombre', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getReporteProveedorSaldoTop($arr)
    {
        $topProveedorArray = [
            "proveedores" =>  [],
            "proveedores_adeudo"   =>  [],
        ];

        parse_str($arr['filters'], $filters);

        $select = [
            "SQL_CALC_FOUND_ROWS `view_proveedor`.`id`",
            'view_proveedor.nombre as proveedor',
        ];


        $select = array_merge($select, [
            '( SELECT ( sum(credito.monto) - sum(credito.monto_pagado))  FROM credito  LEFT JOIN compra on credito.compra_id = compra.id WHERE credito.tipo  = 20 and ( compra.proveedor_id = view_proveedor.id or credito.proveedor_id = view_proveedor.id ) and ( credito.status = 10 or credito.status = 40) ) as por_pagar',
        ]);


        $query = (new Query())
        ->select($select)
        ->from('view_proveedor')
        ->andWhere([ "status" => Proveedor::STATUS_ACTIVE ])
        ->limit(10)
        ->orderBy('por_pagar desc')
        ->all();

        foreach ($query as $key => $item_proveedor) {
            array_push($topProveedorArray["proveedores"], $item_proveedor["proveedor"]);
            array_push($topProveedorArray["proveedores_adeudo"], floatval(round($item_proveedor["por_pagar"],2)));
        }

        return $topProveedorArray;
    }
//------------------------------------------------------------------------------------------------//
// JSON COMPRAS PROVEEDOR
//------------------------------------------------------------------------------------------------//
    public static function getComprasJsonBtt($arr)
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
                    "SQL_CALC_FOUND_ROWS `compra`.`id`",
                    'compra.total',
                    'sucursal.nombre as sucursal',
                    'compra.created_at',
                    'compra.created_by',
                    'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`',
                    'compra.updated_at',
                    'compra.updated_by',
                    'concat_ws(" ",`updated`.`nombre`,`updated`.`apellidos`) AS `updated_by_user`',
                ])
                ->from(Compra::tableName())
                ->innerJoin('sucursal','compra.sucursal_id    = sucursal.id')
                ->innerJoin('user created','compra.created_by = created.id')
                ->innerJoin('user updated','compra.updated_by = updated.id')
                ->andWhere(["compra.proveedor_id"  => $filters['proveedor_id'] ])
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'compra.id', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getDireccionAjax($proveedor_id)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

            /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "esys_direccion.id",
                "esys_direccion.direccion",
                "esys_direccion.num_ext",
                "esys_direccion.num_int",
                "esys_direccion.estado_id",
                "estado.singular as estado",
                "esys_direccion.municipio_id",
                "municipio.singular as municipio",
                "cp.colonia as colonia",
                "cp.codigo_postal  as codigo_postal",
                "cp.id  as colonia_id",
                "esys_direccion.referencia"
            ])
            ->from("esys_direccion")
            ->innerJoin('proveedor','esys_direccion.cuenta_id  = proveedor.id  and esys_direccion.cuenta = 5')
            ->leftJoin('esys_lista_desplegable estado','esys_direccion.estado_id = estado.id_2 and estado.label = "crm_estado"')
            ->leftJoin('esys_lista_desplegable municipio','esys_direccion.municipio_id = municipio.id_2 and municipio.param1 = estado.id_2 and municipio.label = "crm_municipio"')
            ->leftJoin('esys_direccion_codigo_postal cp','esys_direccion.codigo_postal_id = cp.id')
            ->andWhere(['proveedor.id' =>  $proveedor_id ]);
            //->groupBy('esys_direccion.estado_id, esys_direccion.municipio_id, cp.id');

        return  $query->all();
    }

    public static function getProveedorAjax($q)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "view_proveedor.`id`",
                "CONCAT_WS(' ', `nombre`,'[',`razon_social`,']') AS `text`",
                "nombre",
            ])
            ->from(self::tableName())

            ->orderBy('id desc')
            ->limit(50);


            $query->andWhere(['view_proveedor.status' => Proveedor::STATUS_ACTIVE ]);


            $query->andFilterWhere([
                'or',
                ['like', 'nombre', $q],
            ]);
            //$query->andWhere(['like', 'nombre_completo', $q]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->all();
    }
}
