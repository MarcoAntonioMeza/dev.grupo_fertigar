<?php
namespace app\models\contabilidad;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\user\User;
use yii\base\Model;

/**
 * This is the model class for table "contabilidad_cuenta".
 *
 * @property int $id
 * @property string|null $nombre
 * @property double|null $code
 * @property int|null $afectable
 * @property int|null $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property ContabilidadCuentaRel[] $ContabilidadCuentaRels
 * @property ContabilidadCuentaRel[] $ContabilidadCuentaRels0
 */
class ContabilidadCuenta extends \yii\db\ActiveRecord
{
    //variables para definir el afectable de las cuentas
    const AFECTABLE         = 10;
    const NO_AFECTABLE      = 20;
    public static $afectableList = [
        self::AFECTABLE     => 'Afectable',
        self::NO_AFECTABLE  => 'No afectable'
    ];
    //variables para definir el status de las cuentas
    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 20;  

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Activo',
        self::STATUS_INACTIVE => 'Inactivo',
    ];
    //Variables para definir el nivel.
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_cuenta';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['afectable', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['afectable', 'status','nombre'], 'required'],
            [['code'],'string'],
            [['code'],'unique'],
            [['nombre'], 'string', 'max' => 255],
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
            'nombre' => 'Nombre',
            'nivel'=>'Titulo',
            'code' => 'Código',
            'afectable' => 'Afectable',
            'status' => 'Estatus',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * Gets query for [[ContabilidadCuentaRels]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadCuentaRels()
    {
        return $this->hasMany(ContabilidadCuentaRel::className(), ['id_child' => 'id']);
    }

    /**
     * Gets query for [[ContabilidadCuentaRels0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadCuentaRelacion()
    {
        return $this->hasMany(ContabilidadCuentaRel::className(), ['id_parent' => 'id']);
    }

    public static function getCuentas()
    {
        $model = self::find()
            ->select(['id', 'code', 'nombre'])
            ->where(['and',['=','status',self::STATUS_ACTIVE]])
            ->orderBy('code');

        return ArrayHelper::map($model->all(), 'id', function($value){
                return   '['.$value->code.'] '.$value->nombre;
        }); 
    }
    
    public static function getNombreCuentas($id)
    {
        $model = self::findOne($id);
        return $model;
    }

    public static function getClaves($ajaxrequest)
    {
        /*  return $ajaxrequest;  */

        $model = self::find()
            ->select(['id', 'code', 'nombre'])
            ->where(['and',['like','nombre',$ajaxrequest]])
            ->orderBy('code')->limit(5)->all();
            
            return $model;
    }
    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
    
            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->created_by;
    
            }else{
    
                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }
            return true;
        } else
            return false;
    }
}
