<?php
namespace app\models\auth;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\base\Model;
use app\models\user\User;

/**
 * Perfil is the model behind the contact form.
 */
class Perfil extends Model
{
    public $old_name;
    public $name;
    public $description;
    public $isNewRecord;

    public $items;

    public $privilegios;
    public $privilegios_count;

    public $created_at;
    public $created_by;
    public $created_by_user;
    public $updated_at;
    public $updated_by;
    public $updated_by_user;


    public function __construct($config = []) {
        $this->isNewRecord = true;
        //$this->scenario = self::CREATE;

        $this->setAuth_items();

        parent::__construct($config);
    }

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'old_name'], 'string', 'max' => 64],
            [['name'], 'checkPerfilExist'],
            [['privilegios_count'], 'boolean'],
            [['privilegios_count'], 'required', 'message' => 'Tienes que seleccionar por lo menos un privilegio.'],
            [['privilegios'], 'safe'],
            [['name', 'description'], 'filter', 'filter' => 'trim'],
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name'            => 'Nombre del perfil',
            'description'     => 'Descripción',
            'created_at'      => 'Creado',
            'created_by'      => 'Creado por',
            'created_by_user' => 'Creado por',
            'updated_at'      => 'Modificado',
            'updated_by'      => 'Modificado por',
            'updated_by_user' => 'Modificado por',
        ];
    }

    public function checkPerfilExist($attribute, $params)
    {
        $Perfil = AuthItem::find()
            ->select('type')
            ->where(['name' => $this->$attribute])
            ->asArray();

        // Si estamos editando
        if(!$this->isNewRecord)
            $Perfil->andWhere(['<>', 'name', $this->old_name]);

        $Perfil = $Perfil->one();

        if(isset($Perfil['type'])){
            $error = (int) $Perfil['type'] === 1? 'Ya existe un perfil con este nombre': 'Este es un nombre reservado, selecciona otro nombre de perfil';

            $this->addError($attribute, $error);
        }

        return true;
    }

    private function setAuth_items()
    {
        $authItemGrouped = $this->getAuthItemGrouped();
        $groups          = $this->getGroups();

        // Grupos principales
        foreach ($this->getGroupsMain() as $key => $groupsMain) {
            $this->items[$groupsMain['area']]['name'] = isset(Yii::$app->params['auth_item_group'][$groupsMain['area']])?
                Yii::$app->params['auth_item_group'][$groupsMain['area']]:
                $groupsMain['area'];

            // Groups
            foreach ($groups as $key2 => $group) {
                $subgrupos = [];


                if ($group['area'] == $groupsMain['area']) {
                    foreach ($authItemGrouped as $key3 => $itemGrouped) {
                        if ($itemGrouped['area'] == $groupsMain['area'] && $itemGrouped['grupo'] == $group['grupo']) {
                            $subgrupos[$itemGrouped['subgrupo']] = $itemGrouped;

                            unset($itemGrouped[$key3]);
                        }
                    }

                    $this->items[$groupsMain['area']]['grupos'][] = [
                        'icon'      => $group['icon'],
                        'subgrupos' => $subgrupos,
                    ];

                    unset($groups[$key2]);
                }
            }
        }

    }

    private function getGroupsMain()
    {
        return (new Query())
            ->select([
                'DISTINCT `auth_item_group`.`area`',
            ])
            ->from('auth_item_group')
            ->orderBy('`orden`')
            ->all();
    }

    private function getGroups()
    {
        return (new Query())
            ->select([
                '`auth_item_group`.`area`',
                '`auth_item_group`.`grupo`',
                '`auth_item_group`.`icon`',
            ])
            ->from('auth_item_group')
            ->orderBy('`grupo`')
            ->all();
    }

    private function getAuthItemGrouped()
    {
        return (new Query())
            ->select([
                '`auth_item_group`.`area`',
                '`auth_item_group`.`grupo`',
                '`auth_item`.`subgrupo`',
                '(SELECT `ai`.`description` FROM `auth_item` `ai` WHERE `ai`.`grupo_id` = `auth_item_group`.`id` AND `ai`.`subgrupo` = `auth_item`.`subgrupo` AND `ai`.`description` IS NOT NULL LIMIT 1) AS `description`',
                'sum(if(`auth_item`.`accion` = "view", 1, null)) AS `view`',
                'sum(if(`auth_item`.`accion` = "create", 1, null)) AS `create`',
                'sum(if(`auth_item`.`accion` = "update", 1, null)) AS `update`',
                'sum(if(`auth_item`.`accion` = "cancel", 1, null)) AS `cancel`',
                'sum(if(`auth_item`.`accion` = "delete", 1, null)) AS `delete`',
            ])
            ->from('auth_item')
            ->leftJoin('auth_item_group', '`auth_item`.`grupo_id` = `auth_item_group`.`id`')
            ->where(['auth_item.type' => 2])
            ->groupBy('`auth_item_group`.`area`, `auth_item_group`.`grupo`, `auth_item`.`subgrupo`')
            ->orderBy('`auth_item_group`.`area`, `auth_item_group`.`orden`, `auth_item`.`orden`')
            ->all();
    }

    private function getAreas() {
        $groupsMain = (new Query())
            ->select([
                'DISTINCT `auth_item_group`.`area`',
            ])
            ->from('auth_item_group')
            ->orderBy('`area`')
            ->all();

        foreach ($groupsMain as $key => $value) {

        }

        return $groupsList;
    }

    public static function findOne($name) {
        if(($AuthItem = AuthItem::findOne(['name' => $name])) !== null) {
            $Perfil = new Perfil([
                'isNewRecord'     => false,
                'old_name'        => $AuthItem->name,
                'name'            => $AuthItem->name,
                'description'     => $AuthItem->description,
                'created_at'      => $AuthItem->created_at,
                'created_by'      => $AuthItem->created_by,
                'created_by_user' => $AuthItem->created_by? $AuthItem->createdBy->nombre . ' ' . $AuthItem->createdBy->apellidos: null,
                'updated_at'      => $AuthItem->updated_at,
                'updated_by'      => $AuthItem->updated_by,
                'updated_by_user' => $AuthItem->updated_by? $AuthItem->updatedBy->nombre . ' ' . $AuthItem->updatedBy->apellidos: null,
            ]);

            $AuthItemChild = AuthItemChild::findAll(['parent' => $name]);

            foreach ($AuthItemChild as $key => $value) {
                $Perfil->privilegios[$value->child] = 1;
            }

            return $Perfil;

        } else
            return null;
    }

    public function save()
    {
        if($this->validate()){
            $AuthItem = $this->isNewRecord? new AuthItem(['type' => 1]): AuthItem::findOne(['name' => $this->old_name]);

            $AuthItem->name        = $this->name;
            $AuthItem->description = $this->description;

            if($AuthItem->save()){
                if(!$this->isNewRecord)
                    AuthItemChild::deleteAll(['parent' => $this->name]);

                foreach($this->privilegios as $key => $value) {
                    $AuthItemChild = new AuthItemChild([
                        'parent' => $this->name,
                        'child'  => $key,
                    ]);

                    $AuthItemChild->save();
                }

                self::deleteCacheFile();

                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        AuthItem::findOne(['name' => $this->name])->delete();

        self::deleteCacheFile();
    }

    public static function deleteCacheFile()
    {
        $rbac_cache_file = Yii::getAlias('@runtime') . '/cache/rb/rbac.bin';

        if(file_exists($rbac_cache_file))
            unlink($rbac_cache_file);
    }


    /*public function updatePerfil()
    {
        $auth    = Yii::$app->authManager;

        if ($this->perfiles_names) {
            // Recorremos todos los perfiles del sistema
            foreach ($auth->getRoles() as $perfil => $value) {
                $AuthAssignmentPerfil = AuthAssignment::find()->where(['gerencia' => $this->name, 'perfil' => $perfil])->one();

                // Comprobamos si es necesario eliminar del usuario el perfil que pudiera asignar
                if($AuthAssignmentPerfil && !in_array($perfil, $this->perfiles_names)) {
                    $AuthAssignmentPerfil->delete();
                }

                // Comprobamos si es necesario agregar el nuevo perfil que pudiera asignar el usuario
                if(!$AuthAssignmentPerfil && in_array($perfil, $this->perfiles_names)) {
                    $AuthAssignmentPerfil = new AuthAssignmentPerfil([
                        'gerencia' => $this->name,
                        'perfil'  => $perfil,
                    ]);

                    $AuthAssignmentPerfil->save();
                }
            }
        }
    }*/

//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//
    public function getCreatedBy()
    {
        return User::findOne($this->created_by);
    }

    public function getUpdatedBy()
    {
        return User::findOne($this->updated_by);
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
        //parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    'SQL_CALC_FOUND_ROWS `auth_item`.`name`',
                    'auth_item.name',
                    'auth_item.description',
                    'auth_item.created_at',
                    'auth_item.created_by',
                    "CONCAT_WS(' ', created.nombre, created.apellidos) AS created_by_user",
                    'auth_item.updated_at',
                    'auth_item.updated_by',
                    "CONCAT_WS(' ', updated.nombre, updated.apellidos) AS updated_by_user",
                ])
                ->from('auth_item')
                ->leftJoin('user created', 'auth_item.created_by = created.id')
                ->leftJoin('user updated', 'auth_item.updated_by = updated.id')
                ->where(['auth_item.type' => 1])
                ->andWhere(['NOT IN', 'name', ['@uthenticated', 'admin', 'theCreator']])
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'name', $search],
                    ['like', 'description', $search],
                ]);


        // Imprime String de la consulta SQL
        // echo ($query->createCommand()->rawSql) . '<br/><br/>';

        $rows = $query->all();

        return [
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
            'rows'  => $rows
        ];
    }

}
