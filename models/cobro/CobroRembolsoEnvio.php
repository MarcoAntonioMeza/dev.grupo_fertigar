<?php
namespace app\models\cobro;

use Yii;
use app\models\user\User;
use app\models\envio\Envio;
/**
 * This is the model class for table "cobro_rembolso_envio".
 *
 * @property int $id ID
 * @property int $envio_id Envio ID
 * @property int $tipo Tipo
 * @property int $metodo_pago Metodo de pago
 * @property double $cantidad Cantidad
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property Envio $envio
 */
class CobroRembolsoEnvio extends \yii\db\ActiveRecord
{

    const COBRO_EFECTIVO        = 10;
    const COBRO_CHEQUE          = 20;
    const COBRO_TRANFERENCIA    = 30;
    const COBRO_TARJETA_CREDITO = 40;
    const COBRO_TARJETA_DEBITO  = 50;
    const COBRO_DEPOSITO        = 60;

    const ISCOBRO_MEX            = 10;

    const TIPO_COBRO            = 10;
    const TIPO_DEVOLUCION       = 20;

    public static $tipoList = [
        self::TIPO_COBRO            => 'Cobro',
        self::TIPO_DEVOLUCION       => 'Devolución',
    ];


    public static $servicioList = [
        self::COBRO_EFECTIVO        => 'Efectivo',
        self::COBRO_CHEQUE          => 'Cheque',
        self::COBRO_TRANFERENCIA    => 'Tranferencia',
        self::COBRO_TARJETA_CREDITO => 'Tarjeta de credito',
        self::COBRO_TARJETA_DEBITO  => 'Tarjeta de debito',
        self::COBRO_DEPOSITO        => 'Deposito',
    ];

    public $cobroRembolsoEnvioArray;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cobro_rembolso_envio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['envio_id'], 'required'],
            [['envio_id', 'tipo', 'metodo_pago', 'created_at', 'created_by','ticket_item_id','is_cobro_mex'], 'integer'],
            [['cantidad'], 'number'],
            [['nota'], 'string'],
            [['cobroRembolsoEnvioArray'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['envio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Envio::className(), 'targetAttribute' => ['envio_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'envio_id' => 'Envio ID',
            'tipo' => 'Tipo',
            'is_cobro_mex' => 'Cobro Mex',
            'metodo_pago' => 'Metodo Pago',
            'cantidad' => 'Cantidad',
            'nota' => 'Nota',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
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
    public function getEnvio()
    {
        return $this->hasOne(Envio::className(), ['id' => 'envio_id']);
    }

    public static  function getRembolso($envio_id)
    {
        return self::find()->andWhere(["envio_id" => $envio_id ])->andWhere(["tipo" => self::TIPO_DEVOLUCION ])->all();
    }

    public function saveCobroEnvio($envio_id, $is_mex_pago = false)
    {
        //$CobroRembolsoEnvio  =  CobroRembolsoEnvio::deleteAll([ "envio_id" => $envio_id]);
        $envio = Envio::findOne($envio_id);
        $envio->dir_obj   = $envio->direccion;
        $cobroRembolsoEnvio = json_decode($this->cobroRembolsoEnvioArray);

        if ($cobroRembolsoEnvio) {
            $cobroTotal = 0;
            foreach ($cobroRembolsoEnvio as $key => $cobro) {
                if ($cobro->origen  ==  1 ) {
                    $CobroRembolsoEnvio  =  new CobroRembolsoEnvio();
                    $CobroRembolsoEnvio->envio_id       = $envio_id;
                    $CobroRembolsoEnvio->tipo           = self::TIPO_COBRO;
                    $CobroRembolsoEnvio->metodo_pago    = $cobro->metodo_pago_id;
                    $CobroRembolsoEnvio->cantidad       = $cobro->cantidad;
                    if ($is_mex_pago)
                        $CobroRembolsoEnvio->is_cobro_mex = self::ISCOBRO_MEX;

                    $CobroRembolsoEnvio->save();
                }
                $cobroTotal =  $cobroTotal + $cobro->cantidad;
            }
            if (!$is_mex_pago) {
                if ($envio->tipo_envio == Envio::TIPO_ENVIO_MEX ) {
                    if ( $cobroTotal >=  $envio->total) {
                        $envio->status = Envio::STATUS_ENTREGADO;
                        $envio->save();
                    }

                    if ($envio->total > $cobroTotal) {
                        $envio->status = Envio::STATUS_PREPAGADO;
                        $envio->save();
                    }
                }
            }
        }
        return true;
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }

            return true;

        } else
            return false;
    }
}
