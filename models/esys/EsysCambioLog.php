<?php
namespace app\models\esys;

use Yii;
use yii\db\Query;
use yii\helpers\Html;
use app\models\Esys;

/**
 * This is the model class for table "esys_cambio_log".
 *
 * @property int $id Id
 * @property string $modulo Módulo
 * @property int $idx Indice #1
 * @property int $idx2 Indice #2
 * @property int $idx3 Indice #3
 * @property int $idx4 Indice #4
 * @property string $prefijo Prefijo
 * @property string $sufijo Sufijo
 * @property string $registro Registro
 * @property string $valor_anterior Valor_anterior
 * @property string $valor_nuevo valor_nuevo
 * @property int $created_at Fecha
 * @property int $created_by Creado por
 */
class EsysCambioLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'esys_cambio_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['modulo', 'idx', 'registro'], 'required'],

            [['id', 'idx', 'idx2', 'idx3', 'idx4', 'created_at', 'created_by'], 'integer'],
            [['valor_anterior', 'valor_nuevo'], 'string'],
            [['modulo'], 'string', 'max' => 32],
            [['prefijo', 'sufijo', 'registro'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'modulo' => 'Módulo',
            'idx' => 'Indice #1',
            'idx2' => 'Indice #2',
            'idx3' => 'Indice #3',
            'idx4' => 'Indice #4',
            'prefijo' => 'Prefijo',
            'sufijo' => 'Sufijo',
            'registro' => 'Registro',
            'valor_anterior' => 'Valor_anterior',
            'valor_nuevo' => 'valor_nuevo',
            'created_at' => 'Fecha',
            'created_by' => 'Creado por',
        ];
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//


//------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//


//------------------------------------------------------------------------------------------------//
// ACTIVE RECORD
//------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)){
            if ($insert) {
                if(Yii::$app->user->identity)
                    $created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
                    //$created_by = Yii::$app->user->identity->id;

                else //if($this->modulo == 'user')
                    $created_by = $this->idx;

                $this->created_by = $created_by;
            }

            return true;

        } else
            return false;
    }

}
