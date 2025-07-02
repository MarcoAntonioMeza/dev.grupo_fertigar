<?php

namespace app\models\cliente;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\user\User;
use app\models\esys\EsysDireccion;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysCambioLog;
use app\models\Esys;
use  app\models\sat\Regimenfiscal;

/**
 * This is the model class for table "cliente".
 *
 * @property int $id ID
 * @property string $nombre Nombre
 * @property string $apellidos Apellidos
 * @property string $email Email
 * @property int $sexo Sexo
 * @property string $telefono Telefono
 * @property string $movil Movil
 * @property int $status Estatus
 * @property string $notas Comentario / Observaciones
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Cliente extends \yii\db\ActiveRecord
{


    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 1;



    const SEXO_HOMBRE = 10;
    const SEXO_MUJER = 20;


    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Deshabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    public static $sexoList = [
        self::SEXO_HOMBRE   => 'Hombre',
        self::SEXO_MUJER => 'Mujer',
    ];


    const ORIGEN_MX = 10;
    const ORIGEN_USA = 20;

    public static $origenList = [
        self::ORIGEN_MX   => 'México',
        self::ORIGEN_USA => 'Estados Unidos',
    ];

    const TIPO_LISTA_PRECIO_PUBLICO = 10;
    const TIPO_LISTA_PRECIO_MAYOREO = 20;
    const TIPO_LISTA_PRECIO_SUBDIS = 30;

    public static $tipoListaPrecioList = [
        self::TIPO_LISTA_PRECIO_PUBLICO   => 'Precio Publico',
        self::TIPO_LISTA_PRECIO_MAYOREO => 'Precio Mayoreo',
        self::TIPO_LISTA_PRECIO_SUBDIS => 'Precio Subdistribuidor',
    ];





    public $dir_obj;
    public $cliente_call;

    public $csv_file;
    public $rows_details = [];

    public $omit_telefono_movil;

    private $num_rows = 0;
    private $csv_column_name = [];

    private $CambiosLog;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'sexo',
                'atraves_de_id',
                'status',
                'created_at',
                'created_by',
                'updated_at',
                'updated_by',
                'titulo_personal_id',
                'asignado_id',
                'tipo_cliente_id',
                'omit_telefono_movil',
                'pais',
                'lista_precios'
            ], 'integer'],
            [['notas'], 'string'],
            [['monto_credito'], 'number'],
            [['semanas'], 'integer'],
            [['nombre', 'apellidos', 'rfc'], 'required'],
            [['nombre', 'apellidos'], 'string', 'max' => 150],
            [['email'], 'string', 'max' => 50],
            [['telefono', 'uso_cfdi'], 'string', 'max' => 20],
            //[['telefono_movil'], 'string', 'min' => 10, 'max' => 10 ,'message' => 'El telefono movíl debe ser  a 10 catacteres'],
            [['email', 'rfc'], 'unique'],
            /*['telefono_movil', 'unique', 'message' => 'El telefono movíl ya ha sido relacionado con otro cliente, ingrese otro nuevamente.', 'when' => function($model) {
                return self::find()->andWhere(['telefono_movil' => $model->telefono_movil])->andWhere(['status' => self::STATUS_ACTIVE ])->count() > 0 ? true : false;
            }],*/
            [['telefono_movil'], 'required'],
            [['asignado_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['asignado_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['titulo_personal_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['titulo_personal_id' => 'id']],
            [['agente_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['agente_id' => 'id']],
            [['regimen_fiscal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Regimenfiscal::className(), 'targetAttribute' => ['regimen_fiscal_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'regimen_fiscal_id' => 'Regimen fiscal',
            'pais' => 'Pais',
            'uso_cfdi' => 'Uso CFDI',
            'agente_id' => 'Agente asignado',
            'lista_precios' => 'Lista de precios asignada',
            'titulo_personal_id' => 'Titulo personal',
            'atraves_de_id' => 'Se entero a través de',
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'email' => 'Email',
            'sexo' => 'Genero',
            'telefono' => 'Telefono Casa',
            'monto_credito' => 'Total de credito',
            'semanas' => 'Plazo (SEMANAS)',
            'movil' => 'Movil',
            'telefono_movil' => 'Telefono Movil',
            'status' => 'Estatus',
            'origen' => 'Origen',
            'notas' => 'Comentario / Observaciones',
            'tituloPersonal.singular' => 'Titulo personal',
            'asignado_id' => 'Asignado a :',
            'tipo_cliente_id' => 'Tipo de cliente',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'updated_at' => 'Modificado',
            'updated_by' => 'Modificado por',
            'rfc' => 'RFC',
        ];
    }

    public function getNombreCompleto()
    {
        return $this->nombre . ' ' . $this->apellidos;
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgente()
    {
        return $this->hasOne(User::className(), ['id' => 'agente_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegimenFiscal()
    {
        return $this->hasOne(Regimenfiscal::className(), ['id' => 'regimen_fiscal_id']);
    }

    public function getAsignadoCliente()
    {
        return $this->hasOne(User::className(), ['id' => 'asignado_id']);
    }

    public function getTituloPersonal()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'titulo_personal_id']);
    }
    public function getAtravesDe()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'atraves_de_id']);
    }

    public function getDireccion()
    {
        return $this->hasOne(EsysDireccion::className(), ['cuenta_id' => 'id'])
            ->where(['cuenta' => EsysDireccion::CUENTA_CLIENTE, 'tipo' => EsysDireccion::TIPO_PERSONAL]);
    }
    public function getCambiosLog()
    {
        return EsysCambioLog::find()
            ->andWhere([
                'or',
                ['modulo' => $this->tableName(), 'idx' => $this->id],
                ['modulo' => EsysDireccion::tableName(), 'idx' => $this->direccion->id],
            ])
            ->all();
    }

    public function getTipo()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'tipo_cliente_id']);
    }

    /**
     * @return JSON string
     */
    public static function getAsiganadoA()
    {
        $query = User::find()
            ->select('id,  nombre, apellidos')
            ->leftJoin('auth_assignment', '`user`.`id` = `auth_assignment`.`user_id`')
            ->andWhere([
                'item_name' => 'Asesor ventas'
            ])
            ->orderBy('id asc');

        return ArrayHelper::map($query->all(), 'id', function ($value) {
            return '[' . $value->id . '] ' . $value->nombre . ' ' . $value->apellidos;
        });
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->status     = self::STATUS_ACTIVE;
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            } else {
                // Creamos objeto para log de cambios
                $this->CambiosLog = new EsysCambiosLog($this);

                // Remplazamos manualmente valores del log de cambios
                foreach ($this->CambiosLog->getListArray() as $attribute => $value) {
                    switch ($attribute) {
                        case 'titulo_personal_id':
                        case 'atraves_de_id':
                        case 'tipo_cliente_id':
                            if ($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', EsysListaDesplegable::find()->select(['singular'])->where(['id' => $value['old']])->one()->singular);

                            if ($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', EsysListaDesplegable::find()->select(['singular'])->where(['id' => $value['dirty']])->one()->singular);
                            break;

                        case 'fecha_nac':
                            if ($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', Esys::unixTimeToString($value['old']));

                            if ($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', Esys::unixTimeToString($value['dirty']));
                            break;

                        case 'status':
                            $this->CambiosLog->updateValue($attribute, 'old', self::$statusList[$value['old']]);
                            $this->CambiosLog->updateValue($attribute, 'dirty', self::$statusList[$value['dirty']]);
                            break;

                        case 'sexo':
                            $this->CambiosLog->updateValue($attribute, 'old',  isset(self::$sexoList[$value['old']]) ? self::$sexoList[$value['old']] : '');

                            $this->CambiosLog->updateValue($attribute, 'dirty', self::$sexoList[$value['dirty']]);
                            break;
                    }
                }


                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            }

            return true;
        } else
            return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert)
            $this->dir_obj->cuenta_id = $this->id;
        else
            // Guardamos un registro de los cambios
            $this->CambiosLog->createLog($this->id);


        // Guardar dirección
        $this->dir_obj->save();
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $this->direccion->delete();

        foreach ($this->cambiosLog as $key => $value) {
            $value->delete();
        }
    }
}
