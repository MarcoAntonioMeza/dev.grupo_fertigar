<?php
namespace app\models\esys;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\user\User;

/**
 * This is the model class for table "esys_lista_desplegable".
 *
 * @property int $id Id
 * @property int $id_2 Id secundario
 * @property string $param1 Parametro 1
 * @property string $param2 Parametro 2
 * @property string $label Etiqueta del listado
 * @property string $singular Singular
 * @property string $plural Plural
 * @property int $orden Orden
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property User[] $users
 * @property User[] $users0
 */
class EsysListaDesplegable extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'esys_lista_desplegable';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['label', 'singular'], 'required'],
            [['singular', 'plural'], 'trim'],

            [['id_2', 'orden', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['param1', 'param2', 'label'], 'string', 'max' => 32],
            [['singular', 'plural'], 'string', 'max' => 128],
            [['id_2', 'label', 'singular'], 'unique', 'targetAttribute' => ['id_2', 'label', 'singular'], 'message' => 'The combination of Id secundario and Etiqueta del listado has already been taken.'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
       return [
            'id' => 'Id',
            'id_2' => 'Id secundario',
            'param1' => 'Parametro 1',
            'param2' => 'Parametro 2',
            'label' => 'Etiqueta del listado',
            'singular' => 'Singular',
            'plural' => 'Plural',
            'orden' => 'Orden',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'updated_at' => 'Modificado',
            'updated_by' => 'Modificado por',
        ];
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//
    /*
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['departamento_id' => 'id']);
    }

    public function getUsers0()
    {
        return $this->hasMany(User::className(), ['titulo_personal_id' => 'id']);
    }
    */


//------------------------------------------------------------------------------------------------//
// ESYSTEMS
//------------------------------------------------------------------------------------------------//
    /**
     * @return Array
     */
    public static function getModulos()
    {
        $modulos = Array();

        foreach (Yii::$app->params['listas-desplegables'] as $key => $value) {
            $modulos[$key] = $value['title'];
        }

        return $modulos;
    }

    /**
     * @return Array/JSON
     */
    public static function getListas($modulo_id)
    {
        return Yii::$app->params['listas-desplegables'][$modulo_id]['items'];
    }

    /**
     * @return JSON string
     */
    public static function getItems($label, $for_json = false)
    {
        $query = self::find()
            ->select('id, singular, plural')
            ->andWhere([
                'label' => $label,
            ])
            ->orderBy('orden');

        if($for_json)
            return $query->all();

        else
            return ArrayHelper::map($query->all(), 'id', 'singular');
    }

    /**
     * @return JSON string
     */
    public static function getCategoria($categoria_id, $for_json = false)
    {
        $query_categoria_id = self::find()->select('env_unidad_medida')
                            ->andWhere([
                                'id' => $categoria_id,
                            ])->one();

        $query = self::find()->select('id,singular,plural')
            ->andWhere([
                'id' => $query_categoria_id->env_unidad_medida,
            ]);

        if($for_json)
            return $query->one();

        else
            return ArrayHelper::map($query->all(), 'id', 'singular');
    }

    /**
     * @re
     * @return String
     */
    public static function getTable($modulo_id)
    {
        return Yii::$app->params['listas-desplegables'][$modulo_id]['title'];
    }


//------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//
    public static function getEstados($params = [])
    {
        $params['id'] = array_key_exists('id', $params)? $params['id']: false;

        $EsysListaDesplegable = self::find()
            ->select([
                'id_2',
                'singular',
            ])
            ->andWhere(['label' => 'crm_estado'])
            ->orderBy('singular');


        return ArrayHelper::map($EsysListaDesplegable->all(), 'id_2', 'singular');
    }

    public static function getMunicipios($params = [])
    {
        $params['id']        = array_key_exists('id', $params)? $params['id']: false;
        $params['estado_id'] = array_key_exists('estado_id', $params)? $params['estado_id']: false;

        $EsysListaDesplegable = self::find()
            ->select([
                'id_2',
                'singular',
            ])
            ->andWhere(['label' => 'crm_municipio'])
            ->orderBy('singular');


        if($params['estado_id'])
            $EsysListaDesplegable->andWhere(['param1' => $params['estado_id']]);


        return ArrayHelper::map($EsysListaDesplegable->all(), 'id_2', 'singular');
    }


//------------------------------------------------------------------------------------------------//
// ACTIVE RECORD
//------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = time();
            $this->created_by = Yii::$app->user->identity->id;

        } else {
            $this->updated_at = time();
            $this->updated_by = Yii::$app->user->identity->id;
        }

        return true;
    }

}
