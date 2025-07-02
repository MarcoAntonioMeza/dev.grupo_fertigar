<?php
namespace app\models\user;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\db\Expression;

/**
 * This is the model class for table "view_user".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property integer $titulo_personal_id
 * @property string $titulo_personal
 * @property string $nombre
 * @property string $apellidos
 * @property string $nombre_completo
 * @property string $perfil
 * @property string $perfiles_asignar
 * @property string $sexo
 * @property integer $fecha_nac
 * @property string $telefono
 * @property string $telefono_movil
 * @property string $cargo
 * @property integer $departamento_id
 * @property string $departamento
 * @property integer $direccion_id
 * @property string $direccion
 * @property string $num_ext
 * @property string $num_int
 * @property string $colonia
 * @property string $referencia
 * @property integer $estado_id
 * @property string $estado
 * @property integer $municipio_id
 * @property string $municipio
 * @property string $cp
 * @property string $comentarios
 * @property string $informacion
 * @property integer $status
 * @property integer $created_at
 * @property integer $created_by
 * @property string $created_by_user
 * @property integer $updated_at
 * @property integer $updated_by
 * @property string $updated_by_user
 */
class ViewUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'view_user';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Nombre de usuario',
            'email' => 'Correo electrónico',
            'titulo_personal_id' => 'Titulo personal',
            'titulo_personal' => 'Titulo personal',
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'nombre_completo' => 'Nombre Completo',
            'perfil' => 'Perfil',
            'perfiles_asignar' => 'Perfiles Asignar',
            'sexo' => 'Sexo',
            'fecha_nac' => 'Fecha de nacimiento',
            'telefono' => 'Teléfono',
            'telefono_movil' => 'Teléfono movil',
            'cargo' => 'Cargo',
            'departamento_id' => 'Departamento',
            'departamento' => 'Departamento',
            'direccion_id' => 'Id',
            'direccion' => 'Dirección',
            'num_ext' => 'Número interior',
            'num_int' => 'Número exterior',
            'colonia' => 'Colonia',
            'referencia' => 'Referencia',
            'estado_id' => 'Estado',
            'estado' => 'Estado',
            'municipio_id' => 'Deleg./Mpio.',
            'municipio' => 'Deleg./Mpio.',

            'comentarios' => 'Comentarios',
            'informacion' => 'Información',
            'status' => 'Estatus',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'created_by_user' => 'Created By User',
            'updated_at' => 'Modificado',
            'updated_by' => 'Modificador por',
            'updated_by_user' => 'Updated By User',
        ];
    }

    public static function primaryKey()
    {
        return ['id'];
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//


//------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//


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
                    'id',
                    'username',
                    'email',
                    'titulo_personal_id',
                    'titulo_personal',
                    'nombre',
                    'apellidos',
                    'nombre_completo',
                    'perfil',
                    'perfiles_asignar',
                    'sexo',
                    'fecha_nac',
                    'origen',
                    'telefono',
                    'telefono_movil',
                    'cargo',
                    'departamento_id',
                    'departamento',
                    'direccion_id',
                    'direccion',
                    'num_ext',
                    'num_int',
                    'referencia',
                    'estado_id',
                    'estado',
                    'municipio_id',
                    'municipio',
                    'comentarios',
                    'informacion',
                    'status',
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
            if(isset($filters['perfil']))
                $query->andFilterWhere(['perfil' => $filters['perfil']]);

            if(isset($filters['departamento_id']))
                $query->andFilterWhere(['departamento_id' => $filters['departamento_id']]);

            if(isset($filters['origen']))
                $query->andFilterWhere(['origen' => $filters['origen']]);

            if(isset($filters['tipo']))
                $query->andFilterWhere(['tipo' => $filters['tipo']]);




            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'username', $search],
                    ['like', 'email', $search],
                    ['like', 'nombre_completo', $search],
                ]);


        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        $rows = $query->all();

        return [
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
            'rows'  => $rows
        ];
    }

    public static function getUserAjax($q)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "`id`",
                "`nombre_completo` AS `text`",
            ])
            ->from(self::tableName())
            ->andWhere(['like', 'nombre_completo', $q])
            ->orderBy('id desc')
            ->limit(50);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->all();
    }
}
