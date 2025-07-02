<?php
namespace app\models\esys;

use Yii;
use yii\db\Query;
use yii\base\Model;
use yii\helpers\Html;
use app\models\Esys;

class EsysCambiosLog extends Model
{
    private $tableName;
    private $prefijo;
    private $sufijo;
    private $attributes = [];

    function __construct($obj)
    {
        parent::__construct();

        $this->tableName = $obj->tableName();


        // Preparamos el Objeto
        if(isset($obj->cambiosLogPrefijo))
            $this->prefijo = $obj->cambiosLogPrefijo;

        if(isset($obj->cambiosLogSufijo))
            $this->sufijo = $obj->cambiosLogSufijo;


        // Creamos Array de cambios
        $allOldAttributes = $obj->getOldAttributes();

        foreach ($obj->getDirtyAttributes() as $attribute => $value) {
            if($allOldAttributes[$attribute] != $value) {
                $this->attributes[$attribute] = [
                    'old'   => $allOldAttributes[$attribute],
                    'dirty' => $value,
                ];
            }
        }

        unset($this->attributes['updated_at'], $this->attributes['updated_by']);
    }

//------------------------------------------------------------------------------------------------//
// Crear Log
//------------------------------------------------------------------------------------------------//
    public function getListArray()
    {
        return $this->attributes;
    }

    public function createLog($idx, $idx2 = null, $idx3 = null, $idx4 = null)
    {
        self::createExpressLog($this->tableName, $this->attributes, [$idx, $idx2, $idx3, $idx4], $this->prefijo, $this->sufijo);
    }

    public function updateValue($attribute, $tipo, $value)
    {
        $this->attributes[$attribute][$tipo] = $value;
    }

    public function deleteValue($attribute)
    {
        unset($this->attributes[$attribute]);
    }

    public static function createExpressLog($tableName, $attributes, $idxs, $prefijo = null, $sufijo = null)
    {
        if(is_array($idxs)) {
            $idxs[1] = isset($idxs[1])? $idxs[1]: null;
            $idxs[2] = isset($idxs[2])? $idxs[2]: null;
            $idxs[3] = isset($idxs[3])? $idxs[3]: null;

        }else
            $idxs = [$idxs, null, null, null];

        foreach ($attributes as $attribute => $value) {
            if($value['old'] != $value['dirty']) {
                $EsysCambioLog = new EsysCambioLog([
                    'modulo'         => $tableName,
                    'idx'            => $idxs[0],
                    'idx2'           => $idxs[1],
                    'idx3'           => $idxs[2],
                    'idx4'           => $idxs[3],
                    'prefijo'        => $prefijo,
                    'sufijo'         => $sufijo,
                    'registro'       => $attribute,
                    'valor_anterior' => (String) $value['old'],
                    'valor_nuevo'    => (String) $value['dirty'],
                    'created_at'     => time(),
                ]);

                $EsysCambioLog->save();
            }
        }
    }


//------------------------------------------------------------------------------------------------//
// Obtener Log
//------------------------------------------------------------------------------------------------//
    public static function getHtmlLog($params, $limit = false, $truncate = false)
    {
        $new_time  = '';
        $new_user  = '';
        $last_time = '';
        $last_user = '';

        ob_start();
        foreach (self::getLog($params, $limit, $truncate) as $key => $value):
            $new_time = round($value['created_at'] / 20);
            $new_user = $value['user_id'];

            $new_li   = ($new_user != $last_user) || ($new_time != $last_time);

            if($new_li): ?>
                <li class="mar-btn">
                    <div>
                        <span class="pull-right">
                            <p class="text-muted">hace <small title="<?= Esys::fecha_en_texto($value['created_at']) ?>"><?= Esys::hace_tiempo_en_texto($value['created_at']) ?></small></p>
                        </span>
                        <span><?= html::a(
                            $value['user'] . ' [' . $value['user_id'] . ']',
                            ['/admin/user/view', 'id' => $value['user_id']],
                            ['class' => 'text-primary']
                        ) ?></span>
                    </div>
            <?php endif ?>

            <div class="mar-btm">
                <i><?= $value['label'] ?></i> : &nbsp; de &nbsp; <b><?= $value['valor_anterior'] ?></b> &nbsp; a &nbsp; <b><?= $value['valor_nuevo'] ?></b>
            </div>

            <?php
            $last_user = $value['user_id'];
            $last_time = round($value['created_at'] / 20);
            $new_li    = ($new_user != $last_user) || ($new_time != $last_time);

            echo $new_li? '</li>': '';
        endforeach;

        return "<ul>" . ob_get_clean() . "</ul>";
    }

    public static function getLog($params, $limit = false, $truncate = false)
    {
        $labels  = [];
        $where[] = 'or';
        $logs    = [];

        $query = (new Query())
            ->select([
                '`esys_cambio_log`.`modulo`',
                '`esys_cambio_log`.`registro`',
                '`esys_cambio_log`.`prefijo`',
                '`esys_cambio_log`.`sufijo`',
                ($truncate? 'IF(LENGTH(`esys_cambio_log`.`valor_anterior`) > 200, CONCAT(LEFT(`esys_cambio_log`.`valor_anterior`, 200), " [...]"), `esys_cambio_log`.`valor_anterior`) as valor_anterior': '`esys_cambio_log`.`valor_anterior`'),
                ($truncate? 'IF(LENGTH(`esys_cambio_log`.`valor_nuevo`) > 200, CONCAT(LEFT(`esys_cambio_log`.`valor_nuevo`, 200), " [...]"), `esys_cambio_log`.`valor_nuevo`) as valor_nuevo': '`esys_cambio_log`.`valor_nuevo`'),
                '`esys_cambio_log`.`created_by`',
                '`user`.`nombre`',
                '`user`.`apellidos`',
                '`esys_cambio_log`.`created_at`',
            ])
            ->from('esys_cambio_log')
            ->innerJoin('user', '`esys_cambio_log`.`created_by` = `user`.`id`')
            ->orderBy('`esys_cambio_log`.`created_at` DESC');

        if(is_object($params[0])) {
            $params_tmp[0] = $params;
            $params        = $params_tmp;
        }

        // si paso mas de una consulta
        foreach ($params as $key => $value) {
            $modulo = $value[0]->tableName();

            $labels[$modulo] = $value[0]->attributeLabels();

            $where[] = [    
                'modulo' => $modulo,
                'idx'    => $value[1],
                'idx2'   => isset($value[2])? $value[2]: null,
                'idx3'   => isset($value[3])? $value[3]: null,
                'idx4'   => isset($value[4])? $value[4]: null,
            ];
        }

        // Aplicamos filtrado
        $query->andFilterWhere($where);

        if($limit)
            $query->limit($limit);


        // Preparamos datos
        foreach ($query->all() as $key => $value) {
            $logs[] = [
                'modulo'         => $value['modulo'],
                'registro'       => $value['registro'],
                'label'          => $value['prefijo'] . $labels[$value['modulo']][$value['registro']] . $value['sufijo'],
                'valor_anterior' => $truncate? strip_tags($value['valor_anterior']): $value['valor_anterior'],
                'valor_nuevo'    => $truncate? strip_tags($value['valor_nuevo']): $value['valor_nuevo'],
                'user_id'        => $value['created_by'],
                'user'           => $value['nombre'] . ' ' . $value['apellidos'],
                'created_at'     => $value['created_at'],
            ];
        }

        return $logs;
    }

}
