<?php
namespace app\models\proveedor;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use app\models\user\User;
use app\models\esys\EsysCambioLog;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysDireccion;

/**
 * This is the model class for table "proveedor".
 *
 * @property int $id ID
 * @property string|null $nombre Nombre
 * @property string|null $rfc RFC
 * @property string|null $razon_social Razon social
 * @property string|null $email Email
 * @property string|null $tel Telefono
 * @property string|null $descripcion Descripcion
 * @property string|null $notas Notas
 * @property int $status Estatus
 * @property int $created_by Creado por
 * @property int $created_at Creado por
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property int|null $pais pais
 */
class Proveedor extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 1;
    const STATUS_INACTIVE_CREDITO = 20;


    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        //self::STATUS_INACTIVE_CREDITO => 'INHABILITAR CREDITO',
        self::STATUS_INACTIVE => 'Deshabilitado',
    ];

    public $avatar_file;

    private $CambiosLog;

    public  $dir_obj;
    public  $dir_obj_array;

    const PAIS_MX = 10;
    const PAIS_US = 20;
    public static $paisList = [
        self::PAIS_MX => 'MÉXICO',
        self::PAIS_US => 'EUA',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'proveedor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcion', 'notas','persona_autorizadas','terminos_condicion'], 'string'],
            [['nombre','status'], 'required'],
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at','plazo','pais'], 'integer'],
            [['nombre', 'razon_social','avatar'], 'string', 'max' => 150],
            [['rfc'], 'string', 'max' => 15],
            [['monto'], 'number'],
            [['dir_obj_array'], 'safe'],
            [['email'], 'string', 'max' => 50],
            [['tel'], 'string', 'max' => 50],
            [['telefono_movil'], 'string', 'max' => 50],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'avatar' => 'Avatar',
            'nombre' => 'Nombre',
            'pais' => 'País',
            'rfc' => 'RFC',
            'avatar_file' => 'Avatar',
            'razon_social' => 'Razon Social',
            'email' => 'Email',
            'tel' => 'Teléfono',
            'telefono_movil' => 'Teléfono Movil',
            'descripcion' => 'Descripción',
            'persona_autorizadas' => 'PERSONAL AUTORIZADO',
            'terminos_condicion' => 'Terminos y Condiciones',
            'plazo' => 'Plazo (DIAS A PAGAR)',
            'monto' => 'MONTO A CREDITAR',
            'notas' => 'Notas',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getCambiosLog()
    {
        return EsysCambioLog::find()
            ->andWhere(['or',
                ['modulo' => $this->tableName(), 'idx' => $this->id],
                ['modulo' => EsysDireccion::tableName(), 'idx' => $this->direccion->id],
            ])
            ->all();
    }

    public function upload($name)
    {
        $this->avatar_file->saveAs('avatar/' . $name . '.' . $this->avatar_file->extension);
    }

    public function getDireccion()
    {
        return $this->hasMany(EsysDireccion::className(), ['cuenta_id' => 'id'])
            ->where(['cuenta' => EsysDireccion::CUENTA_PROVEEDOR]);
    }

    public function removeFileOld($name)
    {
        $fileAvatarOld = FileHelper::findFiles( Yii::getAlias('@app') . '/web/avatar/',['only'=>[ $name ]]);

        foreach ($fileAvatarOld as $key => $file) {
            unlink($file);
        }
    }

    public function saveDireccion()
    {
        $DireccionArray = json_decode($this->dir_obj_array);
        if ($DireccionArray) {
            foreach ($DireccionArray as $key => $direccion) {
                if ($direccion->status == 10 && $direccion->update == 1) {
                    $EsysDireccion = new EsysDireccion([
                        'cuenta' => EsysDireccion::CUENTA_PROVEEDOR,
                    ]);
                    $EsysDireccion->cuenta_id       = $this->id;
                    $EsysDireccion->tipo            = $direccion->tipo;
                    $EsysDireccion->direccion       = $direccion->direccion;
                    $EsysDireccion->num_ext         = $direccion->num_exterior;
                    $EsysDireccion->num_int         = $direccion->num_interior;
                    $EsysDireccion->estado_id       = $direccion->estado_id;
                    $EsysDireccion->municipio_id    = $direccion->municipio_id;
                    $EsysDireccion->codigo_postal_id= $direccion->colonia_id;
                    $EsysDireccion->referencia      = $direccion->referencia;
                    $EsysDireccion->save();
                }
                if ($direccion->status == 1 && $direccion->update == 10 ) {
                    $EsysDireccion = EsysDireccion::findOne($direccion->item_id);
                    $EsysDireccion->delete();
                }
            }
        }
    }


    //------------------------------------------------------------------------------------------------//
    // HELPERS
    //------------------------------------------------------------------------------------------------//


    public static function getItems($params = [])
    {
        $model = static::find()
            ->select([
                'id',
                'nombre',
            ])
        ->andWhere([ 'status' => self::STATUS_ACTIVE ])
        ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', function($value){
            return $value->nombre . ' [' . $value->id . ']';
        });
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            $this->nombre =        strtoupper(trim($this->nombre));
            $this->razon_social =  strtoupper(trim($this->razon_social));
            $this->rfc =           strtoupper(trim($this->rfc));

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->created_by;

            }else{
                // Creamos objeto para log de cambios
                $this->CambiosLog = new EsysCambiosLog($this);

                // Remplazamos manualmente valores del log de cambios
                foreach($this->CambiosLog->getListArray() as $attribute => $value) {
                    switch ($attribute) {
                        case 'avatar':
                            $this->CambiosLog->updateValue($attribute, 'old', "SE REMOVIO AVATAR");
                            $this->CambiosLog->updateValue($attribute, 'dirty', "NUEVO AVATAR");
                            break;
                        case 'status':
                            $this->CambiosLog->updateValue($attribute, 'old', self::$statusList[$value['old']]);
                            $this->CambiosLog->updateValue($attribute, 'dirty', self::$statusList[$value['dirty']]);
                            break;
                    }
                }

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }

            return true;

        } else
            return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($insert){
            //$this->dir_obj->cuenta_id = $this->id;

        }else
            // Guardamos un registro de los cambios
            $this->CambiosLog->createLog($this->id);

        //if (!$this->dir_obj->cuenta_id)
          //  $this->dir_obj->cuenta_id = $this->id;



    }

    public function afterDelete()
    {
        parent::afterDelete();

        //$this->direccion->delete();

        foreach ($this->cambiosLog as $key => $value) {
           $value->delete();
        }
    }
}
