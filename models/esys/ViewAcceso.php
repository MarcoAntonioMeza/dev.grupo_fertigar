<?php
namespace app\models\esys;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use yii\web\Response;
use common\models\User;

/**
 * This is the model class for table "view_acceso".
 *
 * @property integer $id
 * @property string $user
 * @property string $wrong_password
 * @property integer $user_id
 * @property string $user_name
 * @property string $user_username
 * @property string $user_email
 * @property string $ip
 * @property integer $access_login
 * @property integer $access_logout
 * @property integer $access
 */
class ViewAcceso extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'view_acceso';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'user' => 'Nombre de usuario',
            'wrong_password' => 'Password erroneo',
            'user_id' => 'Usuario',
            'user_name' => 'User Name',
            'user_username' => 'Nombre de usuario',
            'user_email' => 'Correo electrónico',
            'ip' => 'IP cliente',
            'access_login' => 'Hora de acceso',
            'access_logout' => 'Cierre de sesión',
            'access' => 'Acceso concedido',
        ];
    }

    public static function primaryKey()
    {
        return ['id'];
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
                    'DISTINCT SQL_CALC_FOUND_ROWS `view_acceso`.`id`',
                    'id',
                    'user',
                    'wrong_password',
                    'user_id',
                    'user_name',
                    'user_username',
                    'user_email',
                    'ip',
                    'access_login',
                    'access_logout',
                    'access',
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

                $query->andWhere(['between','access_login', $date_ini, $date_fin]);
            }


        /************************************
        / En caso de busqueda
        /***********************************/
            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'user', $search],
                    ['like', 'user_name', $search],
                    ['like', 'user_username', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        $rows = $query->all();

        return [
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
            'rows'  => $rows
        ];
    }
}
