<?php

namespace app\models\sucursal;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\db\Expression;
use app\models\sucursal\Sucursal;
use app\models\cliente\Cliente;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;
/**
 * This is the model class for table "view_sucursal".
 *
 * @property int $id
 * @property int $encargado_id Encargado Id
 * @property string $encargado
 * @property string $nombre
 * @property string $rfc
 * @property string $email
 * @property int $status
 * @property string $img_banner Imagen banner
 * @property string $logo Logo
 * @property string $telefono
 * @property string $telefono_movil Telefono movil
 * @property string $informacion InformaciÃ³n
 * @property string $comentarios Comentarios
 * @property int $tipo Tipo
 * @property string $direccion Dirección
 * @property string $num_ext Número interior
 * @property string $num_int Número exterior
 * @property string $colonia Colonia
 * @property int $estado_id Estado
 * @property string $municipio Singular
 * @property string $estado Singular
 * @property int $created_at Creado
 * @property string $cp Código Postal
 * @property string $created_by_user
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property string $updated_by_user
 * @property int $updated_by Modificado por
 */
class ViewSucursal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_sucursal';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',

            'encargado_id' => 'Encargado Id',
            'encargado' => 'Encargado',
            'nombre' => 'Nombre',
            'rfc' => 'Rfc',
            'email' => 'Email',
            'status' => 'Status',

            'logo' => 'Logo',
            'telefono' => 'Telefono',
            'telefono_movil' => 'Telefono movil',
            'informacion' => 'InformaciÃ³n',
            'comentarios' => 'Comentarios',
            'tipo' => 'Tipo',
            'direccion' => 'Dirección',
            'num_ext' => 'Número interior',
            'num_int' => 'Número exterior',
            'estado_id' => 'Estado',
            'municipio' => 'Singular',
            'estado' => 'Singular',
            'created_at' => 'Creado',
            'cp' => 'Código Postal',
            'created_by_user' => 'Created By User',
            'created_by' => 'Creado por',
            'updated_at' => 'Modificado',
            'updated_by_user' => 'Updated By User',
            'updated_by' => 'Modificado por',
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
                    'encargado_id',
                    'encargado',
                    'nombre',
                    'rfc',
                    'email',
                    'status',
                    'telefono',
                    'telefono_movil',
                    'informacion',
                    'comentarios',
                    'tipo',
                    'direccion',
                    'num_ext',
                    'num_int',
                    'estado_id',
                    'municipio',
                    'estado',
                    'created_at',
                    'created_by_user',
                    'created_by',
                    'updated_at',
                    'updated_by_user',
                    'updated_by',
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

            if (isset($filters['estado_id']) && $filters['estado_id'])
                $query->andWhere(['estado_id' =>  $filters['estado_id']]);

            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['tipo' =>  $filters['tipo']]);


            if (isset($filters['tipo_off']) && $filters['tipo_off'])
                $query->andWhere(['<>', 'tipo', $filters['tipo_off']]);

            if($search)
                $query->andFilterWhere([
                    'or',

                    ['like', 'nombre', $search],
                    ['like', 'email', $search],

                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        $rows = $query->all();

        return [
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
            'rows'  => $rows
        ];
    }

    public static function getSucursalAjax($id)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "`id`",
                "`clave`",
                "`nombre` AS `nombre`",
                "`tipo` AS `tipo`",
                'estado',
                'estado_id',
                'direccion',
                'telefono',
                'encargado',
            ])
            ->from(self::tableName())
            ->andWhere([ 'id' =>  $id]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->one();

    }
}
