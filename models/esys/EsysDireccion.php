<?php
namespace app\models\esys;

use Yii;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysListaDesplegable;

/**
 * This is the model class for table "esys_direccion".
 *
 * @property int $id Id
 * @property int $cuenta Cuenta
 * @property int $cuenta_id Cuenta ID
 * @property int $tipo Tipo
 * @property string $direccion Dirección
 * @property string $num_ext Número interior
 * @property string $num_int Número exterior
 * @property string $colonia Colonia
 * @property string $referencia Referencia
 * @property int $estado_id Estado
 * @property int $municipio_id Deleg./Mpio.
 * @property string $cp Código Postal
 * @property string $lat Latitud
 * @property string $lng Longitud
 *
 */
class EsysDireccion extends \yii\db\ActiveRecord
{
    const CUENTA_USUARIO    = 1;
    const CUENTA_CLIENTE    = 2;
    const CUENTA_SUCURSAL   = 3;
    const CUENTA_VENTA      = 4;
    const CUENTA_PROVEEDOR  = 5;


    const IS_CHECK  = 10;

    public $codigo_search;
    public $codigo_colonia;


    public $cuentaList = [
        self::CUENTA_USUARIO  => 'Usuario',
        self::CUENTA_CLIENTE  => 'Cliente',
        self::CUENTA_SUCURSAL  => 'Sucursal',
        self::CUENTA_VENTA  => 'Venta',
        self::CUENTA_PROVEEDOR  => 'Proveedor',
    ];

    const TIPO_PERSONAL = 1;
    const TIPO_FISCAL   = 2;

    public static $tipoList = [
        self::TIPO_PERSONAL => 'Personal',
        self::TIPO_FISCAL   => 'Fiscal',
    ];

    private $CambiosLog;



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'esys_direccion';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuenta', 'cuenta_id'], 'required'],
            [['id', 'cuenta', 'cuenta_id', 'tipo', 'estado_id', 'municipio_id','is_check'], 'integer'],
            [['lat', 'lng'], 'number'],
            [['direccion', 'referencia'], 'string', 'max' => 256],
            [['num_ext', 'num_int'], 'string', 'max' => 16],
            [['colonia_usa'], 'string', 'max' => 150],
            [['colonia_new'], 'string', 'max' => 150],
            [['municipio_usa'], 'string', 'max' => 150],
            [['estado_usa'], 'string', 'max' => 150],
            [['codigo_postal_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysDireccionCodigoPostal::className(), 'targetAttribute' => ['codigo_postal_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'cuenta' => 'Cuenta',
            'cuenta_id' => 'Cuenta ID',
            'tipo' => 'Tipo',
            'direccion' => 'Dirección',
            'num_ext' => 'Número interior',
            'num_int' => 'Número exterior',
            'colonia' => 'Colonia',
            'referencia' => 'Referencia',
            'estado_id' => 'Estado',
            'estado.singular' => 'Estado',
            'colonia_new' =>'Colonia new',
            'municipio_id' => 'Deleg./Mpio.',
            'municipio.singular' => 'Deleg./Mpio.',
            'codigo_postal_id' => 'Código Postal',
            'codigo_search' => 'Código Postal',
            'codigo_postal_usa' => 'Código Postal',
            'estado_usa' => 'Estado',
            'municipio_usa' => 'Deleg./Mpio.',
            'colonia_usa' => 'Colonia',
            'codigo_colonia' => 'Colonia',
            'esysDireccionCodigoPostal.estado.singular' => 'Estado',
            'esysDireccionCodigoPostal.municipio.singular' => 'Municipio',
            'lat' => 'Latitud',
            'lng' => 'Longitud',
            'is_check' => 'Is check',
        ];
    }

    public static function primaryKey()
    {
        return ['id'];
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//
    public function getEstado()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id_2' => 'estado_id'])->where(['label' => 'crm_estado']);
    }

    public function getMunicipio()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id_2' => 'municipio_id','param1' => 'estado_id' ])->where(['label' => 'crm_municipio']);
    }

    public function getEsysDireccionCodigoPostal()
    {
        return $this->hasOne(EsysDireccionCodigoPostal::className(), ['id' => 'codigo_postal_id']);
    }


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

            }else{
                // Creamos objeto para log de cambios
                $this->CambiosLog = new EsysCambiosLog($this);

                // Remplazamos manualmente valores del log de cambios

                foreach($this->CambiosLog->getListArray() as $attribute => $value) {
                    switch ($attribute) {
                        case 'estado_id':
                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', EsysListaDesplegable::find()->select(['singular'])->where(['id_2' => $value['dirty'], 'label' => 'crm_estado'])->one()->singular);

                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', EsysListaDesplegable::find()->select(['singular'])->where(['id_2' => $value['old'], 'label' => 'crm_estado'])->one()->singular);
                            break;

                        case 'municipio_id':

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', EsysListaDesplegable::find()->select(['singular'])->where(['and',['id_2' => $value['dirty'],'param1' => $this->estado_id, 'label' => 'crm_municipio']])->one()->singular);

                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', EsysListaDesplegable::find()->select(['singular'])->where([ 'and',['id_2' => $value['old'],'param1' => $this->estado_id, 'label' => 'crm_municipio']])->one()->singular);
                        break;

                         case 'codigo_postal_id':

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', EsysDireccionCodigoPostal::find()->select(['colonia'])->where(['id' => $value['dirty']])->one()->colonia);

                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', EsysDireccionCodigoPostal::find()->select(['colonia'])->where(['id' => $value['old']])->one()->colonia);
                        break;
                    }
                }


            }

            return true;

        } else
            return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {

        }else{
            // Guardamos un registro de los cambios
            $this->CambiosLog->createLog($this->id);
        }
   }


}
