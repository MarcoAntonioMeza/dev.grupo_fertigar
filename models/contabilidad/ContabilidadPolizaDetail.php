<?php
namespace app\models\contabilidad;

use Yii;
use app\models\user\User;
use yii\data\Sort;

/**
 * This is the model class for table "contabilidad_poliza_detail".
 *
 * @property int $id ID
 * @property int $contabilidad_poliza_id Contabilidad poliza ID
 * @property int $contabilidad_transaccion_id Contabilidad transaccion ID
 * @property int $contabilidad_transaccion_detail_id Contabilidad transaccion detail ID
 * @property float|null $cargo Cargo
 * @property float|null $abono Cargo
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property ContabilidadPoliza $contabilidadPoliza
 * @property ContabilidadTransaccionDetail $contabilidadTransaccionDetail
 * @property ContabilidadTransaccion $contabilidadTransaccion
 * @property User $createdBy
 */
class ContabilidadPolizaDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_poliza_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contabilidad_poliza_id', 'contabilidad_transaccion_id', 'contabilidad_transaccion_detail_id', 'created_at', 'created_by'], 'required'],
            [['contabilidad_poliza_id', 'contabilidad_transaccion_id', 'contabilidad_transaccion_detail_id', 'created_at', 'created_by'], 'integer'],
            [['cargo', 'abono'], 'number'],
            [['contabilidad_poliza_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadPoliza::className(), 'targetAttribute' => ['contabilidad_poliza_id' => 'id']],
            [['contabilidad_transaccion_detail_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadTransaccionDetail::className(), 'targetAttribute' => ['contabilidad_transaccion_detail_id' => 'id']],
            [['contabilidad_transaccion_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContabilidadTransaccion::className(), 'targetAttribute' => ['contabilidad_transaccion_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contabilidad_poliza_id' => 'Contabilidad Poliza ID',
            'contabilidad_transaccion_id' => 'Contabilidad Transaccion ID',
            'contabilidad_transaccion_detail_id' => 'Contabilidad Transaccion Detail ID',
            'cargo' => 'Cargo',
            'abono' => 'Abono',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * Gets query for [[ContabilidadPoliza]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadPoliza()
    {
        return $this->hasOne(ContabilidadPoliza::className(), ['id' => 'contabilidad_poliza_id']);
    }

    /**
     * Gets query for [[ContabilidadTransaccionDetail]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadTransaccionDetail()
    {
        return $this->hasOne(ContabilidadTransaccionDetail::className(), ['id' => 'contabilidad_transaccion_detail_id']);
    }

    /**
     * Gets query for [[ContabilidadTransaccion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContabilidadTransaccion()
    {
        return $this->hasOne(ContabilidadTransaccion::className(), ['id' => 'contabilidad_transaccion_id']);
    }

     public function getContabilidadCuenta()
    {
        return $this->hasOne(ContabilidadCuenta::className(), ['id' => 'contabilidad_cuenta_id']);
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

    public static function getConfigPolizaManual($poliza_id)
    {
        $responseArray  = [];

        $queryDetail    =  ContabilidadPolizaDetail::find()->andWhere(["contabilidad_poliza_id" => $poliza_id ])->all();

        foreach ($queryDetail as $key => $item_detail) {
            array_push($responseArray,[
                "cuenta"      => $item_detail->contabilidadCuenta->nombre,
                "cargo"       => $item_detail->cargo,
                "abono"       => $item_detail->abono,
                "tipo_poliza" => $item_detail->tipo_poliza,
            ]);
        }


        return $responseArray;
    }

    public static function getBalanzaParams($fecha_inicio, $fecha_fin)
    {
        $responseArray = [];
        $fecha_inicial = strtotime($fecha_inicio);
        $fecha_final = strtotime($fecha_fin);

        $pertenecer = self::find()->select(['contabilidad_poliza_id','contabilidad_transaccion_detail_id', 'cargo', 'abono','contabilidad_cuenta_id'])->andWhere(['and',
        ['>=', 'created_at', $fecha_inicial],
        ['<=', 'created_at', $fecha_final]
        ])->all();

        foreach ($pertenecer as $key => $pertenece) 
        {
            $cuentas = ContabilidadCuenta::find()->select(['code','nombre','id'])->where(['=','id',$pertenece->contabilidad_cuenta_id])->one();
            $montos = ContabilidadPoliza::find()->select(['total'])->where(['=','id',$pertenece->contabilidad_poliza_id])->one();
            if($cuentas != null)
            {
                array_push($responseArray,[
                    "codigo"      => $cuentas['code'],
                    "cuenta_id"   => $cuentas['id'],
                    "cuentas"     => $cuentas['nombre'],
                    "montos"      => $montos->total,
                    "cargo"       => $pertenece->cargo ,
                    "abono"       => $pertenece->abono,
                ]);  
            }
            else 
            {
                $alterno[0]['nombre'] = ContabilidadTransaccionDetail::find()->select(['contabilidad_cuenta_id'])->where(['=','id', $pertenece->contabilidad_transaccion_detail_id])->one();
                $cuentas = ContabilidadCuenta::find()->select(['code','nombre', 'id'])->where(['=','id',$alterno[0]['nombre']['contabilidad_cuenta_id']])->one();
                array_push($responseArray,[
                    "codigo"      => $cuentas['code'],
                    "cuenta_id"   => $cuentas['id'],
                    "cuentas"     => $cuentas['nombre'],
                    "montos"      => $montos->total,
                    "cargo"       => $pertenece->cargo ,
                    "abono"       => $pertenece->abono,
                ]);  
            }
        }

        return $responseArray;
    }

    public static function getBalanzaParamshistory($fecha_inicio, $fecha_fin,$id_cuenta)
    {
        $responseArray = [];
        $fecha_inicial = $fecha_inicio;
        $fecha_final = $fecha_fin;

        $pertenecer = self::find()->select(['contabilidad_poliza_id','contabilidad_transaccion_detail_id', 'cargo', 'abono','contabilidad_cuenta_id'])->andWhere(['and',
        ['=','contabilidad_cuenta_id',$id_cuenta]
        ])->all();

        if($pertenecer==null){

            $cuentas = ContabilidadCuenta::find()->select(['code','nombre', 'id'])->where(['=','id',$id_cuenta])->one();
            $transacciones = ContabilidadTransaccionDetail::find()->select(['id'])->where(['=','contabilidad_cuenta_id',$id_cuenta])->all();

            foreach($transacciones as $key => $ids){

                $pertenecer = self::find()->select(['contabilidad_poliza_id','contabilidad_transaccion_detail_id', 'cargo', 'abono'])->andWhere(['and',               
                ['=','contabilidad_transaccion_detail_id',$ids['id']]
                ])->all();  

                foreach ($pertenecer as $key => $pertenece) 
                {
                    $montos = ContabilidadPoliza::find()->select(['id','total','created_at'])->where(['=','id',$pertenece->contabilidad_poliza_id])->one();

                    if ($montos->created_at >= $fecha_inicial && $montos->created_at <= $fecha_final) {
                        array_push($responseArray,[
                            "codigo"      => $cuentas['code'],
                            "cuenta_id"   => $cuentas['id'],
                            "cuentas"     => $cuentas['nombre'],
                            "montos"      => $montos->total,
                            "cargo"       => $pertenece->cargo ,
                            "abono"       => $pertenece->abono,
                            "fecha"=>$montos->created_at,
                            "fecha_min"=>$fecha_inicial,
                            "fecha_max"=>$fecha_final,
                            'id_poliza'=>$montos->id
                        ]);
                    }
                }
            }
        }
        else{

            foreach ($pertenecer as $key => $pertenece) 
            {
                $cuentas = ContabilidadCuenta::find()->select(['code','nombre','id'])->where(['=','id',$pertenece->contabilidad_cuenta_id])->one();
                $montos = ContabilidadPoliza::find()->select(['total','created_at'])->where(['=','id',$pertenece->contabilidad_poliza_id])->one();
            
                   
                if ($montos->created_at >= $fecha_inicial && $montos->created_at <= $fecha_final) {
                    array_push($responseArray,[
                        "codigo"      => $cuentas['code'],
                        "cuenta_id"   => $cuentas['id'],
                        "cuentas"     => $cuentas['nombre'],
                        "montos"      => $montos->total,
                        "cargo"       => $pertenece->cargo ,
                        "abono"       => $pertenece->abono,
                    ]);
                }
            }
        }
        return $responseArray;
    }
}
