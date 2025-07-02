<?php
namespace app\models\cliente;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\venta\Venta;
use app\models\esys\EsysDireccion;
use app\models\esys\EsysDireccionCodigoPostal;


/**
 * This is the model class for table "view_cliente".
 *
 * @property int $id Id
 * @property int $titulo_personal_id Titulo personal
 * @property string $titulo_personal Singular
 * @property string $email Correo electrónico
 * @property string $email2 Correo secundario
 * @property string $empresa Empresa
 * @property string $nombre Nombre
 * @property string $apellidos Apellidos
 * @property string $sexo Sexo
 * @property string $cargo Cargo
 * @property string $departamento Departamento
 * @property int $origen_id Se entero través de
 * @property string $origen Singular
 * @property int $asignado_a_id Asignado a
 * @property string $asignado_a
 * @property string $tel Teléfono trabajo
 * @property string $tel_ext Extensión
 * @property string $tel2 Otro teléfono
 * @property string $movil Teléfono movil
 * @property string $pag_web Página web
 * @property string $notas Notas / Comentarios
 * @property int $api_enabled Habilitar API
 * @property string $api_username Nombre de usuario (API)
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string $created_by_user
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 * @property string $updated_by_user
 */
class ViewCliente extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_cliente';
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
                    'titulo_personal_id',
                    'titulo_personal',
                    'nombre_completo',
                    'nombre',
                    'apellidos',
                    'email',
                    'sexo',
                    'telefono',
                    'telefono_movil',
                    'status',
                    'tipo_cliente',
                    'notas',
                    'created_at',
                    'created_by',
                    'created_by_user',
                    'updated_at',
                    'updated_by',
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
            if (isset($filters['asignado_id']) && $filters['asignado_id'])
                $query->andWhere(['asignado_id' =>  $filters['asignado_id']]);



            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);

            if (isset($filters['tipo_cliente']) && $filters['tipo_cliente'])
                $query->andWhere(['tipo_cliente_id' =>  $filters['tipo_cliente']]);


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'telefono_movil', $search],
                    ['like', 'telefono', $search],
                    ['like', 'nombre_completo', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


//------------------------------------------------------------------------------------------------//
// JSON VENTAS CLIENTE
//------------------------------------------------------------------------------------------------//
    public static function getVentasJsonBtt($arr)
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
                    "SQL_CALC_FOUND_ROWS `venta`.`id`",
                    'venta.total',
                    'if(sucursal.tipo = 20, "VENTA [TIENDA]","VENTA [PREVENTA]" ) as tipo',
                    'sucursal.nombre as sucursal',
                    'ruta.nombre as ruta',
                    'venta.created_at',
                    'venta.created_by',
                    'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`',
                    'venta.updated_at',
                    'venta.updated_by',
                    'concat_ws(" ",`updated`.`nombre`,`updated`.`apellidos`) AS `updated_by_user`',
                ])
                ->from(Venta::tableName())
                ->leftJoin('sucursal','venta.sucursal_id  = sucursal.id')
                ->leftJoin('sucursal ruta','venta.ruta_sucursal_id  = ruta.id')
                ->leftJoin('user created','venta.created_by = created.id')
                ->leftJoin('user updated','venta.updated_by = updated.id')
                ->andWhere(["cliente_id"  => $filters['cliente_id'] ])
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'venta.id', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getReporteVentaClienteJsonBtt($arr)
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
                "SQL_CALC_FOUND_ROWS `view_cliente`.`id`",
                'view_cliente.nombre_completo',
            ];


            if ((isset($filters['sucursal_id']) && $filters['sucursal_id']) &&  !$filters['date_range'] ) {
                $select = array_merge($select, [
                    '(SELECT SUM(venta.total) FROM venta  WHERE venta.cliente_id = view_cliente.id AND  venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta.status  = 10) as total_ingreso',
                    '(SELECT COUNT(*)  FROM venta WHERE venta.cliente_id = view_cliente.id AND venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta.status  = 10) AS num_ventas'
                ]);

            }else if ((isset($filters['date_range']) && $filters['date_range']) &&  !$filters['sucursal_id'] ) {

                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $select = array_merge($select, [
                    '(SELECT SUM(venta.total) FROM venta  WHERE venta.cliente_id = view_cliente.id AND venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.' ) as total_ingreso',
                    '(SELECT COUNT(*)  FROM venta WHERE venta.cliente_id = view_cliente.id AND venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') AS num_ventas'
                ]);

            }else if( (isset($filters['sucursal_id']) && $filters['sucursal_id']) && (isset($filters['date_range']) && $filters['date_range']) ){

                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $select = array_merge($select, [
                    '(SELECT SUM(venta.total) FROM venta  WHERE venta.cliente_id = view_cliente.id AND  venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') as total_ingreso',
                    '(SELECT COUNT(*)  FROM venta WHERE venta.cliente_id = view_cliente.id AND venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') AS num_ventas'
                ]);

            }else{
                $select = array_merge($select, [
                    '(SELECT SUM(venta.total) FROM venta  WHERE venta.cliente_id = view_cliente.id AND venta.status  = 10) as total_ingreso',
                    '(SELECT COUNT(*)  FROM venta WHERE venta.cliente_id = view_cliente.id AND venta.status  = 10) AS num_ventas'
                ]);
            }


            $query = (new Query())
                ->select($select)
                ->from('view_cliente')
                ->andWhere(["and",
                    [ "=","view_cliente.status", Cliente::STATUS_ACTIVE ],
                    [ ">","(select count(*)  from venta where venta.cliente_id = view_cliente.id and venta.status  = 10)", 0 ]
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

    public static function getReporteClienteSaldoJsonBtt($arr)
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
                "SQL_CALC_FOUND_ROWS `view_cliente`.`id`",
                'view_cliente.nombre_completo',
            ];


            $select = array_merge($select, [
                '( SELECT ( SUM(credito.monto) - SUM(IF(credito.monto_pagado IS NULL,0,credito.monto_pagado)))  FROM credito  '.
                'LEFT JOIN venta on credito.venta_id = venta.id '.
                'WHERE credito.tipo  = 10 and ( venta.cliente_id = view_cliente.id or credito.cliente_id = view_cliente.id ) '.
                'and  (credito.status = 10 or credito.status = 40) ) as por_pagar',
                
                '(SELECT credito_abono.created_at FROM credito   '.
                'INNER JOIN credito_abono on credito.id = credito_abono.credito_id '.
                'LEFT JOIN venta on credito.venta_id = venta.id '.
                'WHERE credito.tipo  = 10  and ( venta.cliente_id = view_cliente.id or credito.cliente_id = view_cliente.id )  '.
                'and ( credito.status = 10 or credito.status = 40) '.
                'and credito_abono.status = 10 order by credito_abono.id  DESC  limit 1 ) AS fecha_ultimo_abono'
            ]);



            $query = (new Query())
                ->select($select)
                ->from('view_cliente')
                ->andWhere(["and",
                    [ "=","view_cliente.status", Cliente::STATUS_ACTIVE ],
                    [ ">","( SELECT ( sum(credito.monto) - SUM(IF(credito.monto_pagado IS NULL,0,credito.monto_pagado)))  FROM credito".
                            " LEFT JOIN venta on credito.venta_id = venta.id ".
                            "WHERE credito.tipo  = 10 and ( venta.cliente_id = view_cliente.id or credito.cliente_id = view_cliente.id ) ".
                            "and ( credito.status = 10 or credito.status = 40) )", 0 
                    ]
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


    public static function getReporteClienteSaldoTop($arr)
    {
        $topClienteArray = [
            "clientes" =>  [],
            "clientes_adeudo"   =>  [],
        ];

        parse_str($arr['filters'], $filters);

        $select = [
            "SQL_CALC_FOUND_ROWS `view_cliente`.`id`",
            'view_cliente.nombre_completo',
        ];


        $select = array_merge($select, [
            '( SELECT ( sum(credito.monto) - SUM(IF(credito.monto_pagado IS NULL,0,credito.monto_pagado))) FROM credito  LEFT JOIN venta on credito.venta_id = venta.id WHERE credito.tipo  = 10 and ( venta.cliente_id = view_cliente.id or credito.cliente_id = view_cliente.id ) and ( credito.status = 10 or credito.status = 40) ) as por_pagar',
        ]);


        $query = (new Query())
        ->select($select)
        ->from('view_cliente')
        ->andWhere([ "status" => Cliente::STATUS_ACTIVE ])
        ->limit(6)
        ->orderBy('por_pagar desc')
        ->all();


        foreach ($query as $key => $item_cliente) {
            array_push($topClienteArray["clientes"], $item_cliente["nombre_completo"]);
            array_push($topClienteArray["clientes_adeudo"], floatval(round($item_cliente["por_pagar"],2)));
        }

        return $topClienteArray;
    }

    public static function getReporteClienteTop($arr)
    {
        $topClienteArray = [
            "clientes" =>  [],
            "vendido"   =>  [],
            "pesos"     =>  [],
        ];

        parse_str($arr['filters'], $filters);

        $select = [
            "SQL_CALC_FOUND_ROWS `view_cliente`.`id`",
            'view_cliente.nombre_completo',
        ];


        if ((isset($filters['sucursal_id']) && $filters['sucursal_id']) &&  !$filters['date_range'] ) {
            $select = array_merge($select, [
                '(SELECT SUM(venta.total) FROM venta  WHERE venta.cliente_id = view_cliente.id AND  venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta.status  = 10) as total_ingreso',
                '(SELECT COUNT(*)  FROM venta WHERE venta.cliente_id = view_cliente.id AND venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta.status  = 10) AS num_ventas'
            ]);

        }else if ((isset($filters['date_range']) && $filters['date_range']) &&  !$filters['sucursal_id'] ) {

            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $select = array_merge($select, [
                '(SELECT SUM(venta.total) FROM venta  WHERE venta.cliente_id = view_cliente.id AND venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.' ) as total_ingreso',
                '(SELECT COUNT(*)  FROM venta WHERE venta.cliente_id = view_cliente.id AND venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') AS num_ventas'
            ]);

        }else if( (isset($filters['sucursal_id']) && $filters['sucursal_id']) && (isset($filters['date_range']) && $filters['date_range']) ){

            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $select = array_merge($select, [
                '(SELECT SUM(venta.total) FROM venta  WHERE venta.cliente_id = view_cliente.id AND  venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') as total_ingreso',
                '(SELECT COUNT(*)  FROM venta WHERE venta.cliente_id = view_cliente.id AND venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') AS num_ventas'
            ]);

        }else{
            $select = array_merge($select, [
                '(SELECT SUM(venta.total) FROM venta  WHERE venta.cliente_id = view_cliente.id AND venta.status  = 10) as total_ingreso',
                '(SELECT COUNT(*)  FROM venta WHERE venta.cliente_id = view_cliente.id AND venta.status  = 10) AS num_ventas'
            ]);
        }


        $query = (new Query())
        ->select($select)
        ->from('view_cliente')
        ->andWhere([ "status" => Cliente::STATUS_ACTIVE ])
        ->limit(10)
        ->orderBy('num_ventas desc')
        ->all();


        foreach ($query as $key => $item_cliente) {
            array_push($topClienteArray["clientes"], $item_cliente["nombre_completo"]);
            array_push($topClienteArray["vendido"], floatval(round($item_cliente["num_ventas"],2)));
            array_push($topClienteArray["pesos"], floatval(round($item_cliente["total_ingreso"],2)));
        }
        return $topClienteArray;
    }
//------------------------------------------------------------------------------------------------//
// JSON Bootstrap Table
//------------------------------------------------------------------------------------------------//

    public static function getClienteAjax($q,$search_opt = false)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "view_cliente.`id`",
                "CONCAT_WS(' ', `nombre_completo`,'[ Tel: ',`telefono_movil`,'/',`telefono`,']') AS `text`",
                "nombre",
                "apellidos",
                "email",
                "telefono",
                "telefono_movil"
            ])
            ->from(self::tableName())

            ->orderBy('id desc')
            ->limit(50);


            $query->andWhere(['view_cliente.status' => Cliente::STATUS_ACTIVE ]);

            if ($search_opt)
                $query->andWhere(['view_cliente.id' => $q]);
            else{
                 $query->andFilterWhere([
                    'or',
                    ['like', 'telefono_movil', $q],
                    ['like', 'telefono', $q],
                    ['like', 'nombre_completo', $q],
                ]);
                //$query->andWhere(['like', 'nombre_completo', $q]);

            }
        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $search_opt ? $query->one() :$query->all();
    }
}
