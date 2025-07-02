<?php

namespace app\models\contabilidad;

use Yii;

/**
 * This is the model class for table "contabilidad_presupuestos".
 *
 * @property int $id id
 * @property int $year year
 * @property int $id_cuenta id cuenta
 * @property float|null $cantidad cantidad
 * @property int|null $created_at created_at
 * @property int|null $created_by created_by
 * @property int|null $updated_at updated_at
 * @property int|null $updated_by updated_by
 */
class ContabilidadPresupuestos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad_presupuestos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['year'], 'required'],
            [['year', 'id_cuenta', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['cantidad'], 'number'],
        ];
    } 

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'year' => 'Year',
            'id_cuenta' => 'Id Cuenta',
            'cantidad' => 'Cantidad',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public static function presupuestos()
    {
        $responseArray = [];
        $ingresos = ContabilidadCuenta::find()->select(['code','nombre','id','created_at'])->where(['like','code','400'])->all();
        $gastos = ContabilidadCuenta::find()->select(['code','nombre','id','created_at'])->where(['like','code','600'])->all();
        array_push($responseArray,[
            "ingresos"   => $ingresos,
            "gastos"     => $gastos
        ]);  
        return $responseArray;
    }

    public static function estados($fecha_init, $fecha_end)
    {
        //Variables recibidas
        $inicio = $fecha_init;
        $final = $fecha_end;
        //obtener solo el year
        $year_init = date("Y", $fecha_init);
        $year_end = date("Y", $fecha_end);
        //variable para retornar
        $presupuestos = [];

        //validar que las fechas del presupuesto no sean de a;os distintos
        if($year_init!=$year_end){
            Yii::$app->session->setFlash('warning', 'Favor de ingresar un rango de fechas valido.');
            return $presupuestos;
        }else{
            if($inicio<=$final){
                //variables de arreglos
                $group_id=[];
                $query = ContabilidadPresupuestos::find()->select(['id','year','id_cuenta','cantidad'])->where(['=','year',$year_init])->all();
                
                foreach ($query as $key => $presupuesto) {
             
                //variables de calculacion de historico
                //no hay balanza en el 2023, cuando se ponga en roduccion dambiar "2022" ppor "date("Y)"
                $año_actual=2022;
                $inicio_f= strtotime($año_actual.'-01-01');
                    
                $cuenta = ContabilidadCuenta::find()->select(['code','nombre','id','created_at'])->where(['=','id',$presupuesto->id_cuenta])->one();
                $balanzas = ContabilidadPolizaDetail::getBalanzaParamshistory($inicio, $final,$cuenta->id);
                $monto =0;
                foreach($balanzas as $key => $valor){
                    $monto = $monto+$valor['montos']; 
                }

                foreach ($balanzas as $key => $balanza) 
                {
                    if(array_key_exists($balanza['cuenta_id'], $group_id))
                    {
                       $group_id[$balanza['cuenta_id']][] = $balanza;  
                    }
                    else
                    {
                        $group_id[$balanza['cuenta_id']][] = $balanza;
                    }
                }
                //rsort($group_id);
                foreach($group_id as $count => $groupAcc) //recorrer grupos
                {
                    $valor_cargo = 0;
                    $valor_abono = 0;
                    $valor_monto = 0;
                    $historico_cargo = 0;
                    $historico_abono = 0;                
                    $deudor = 0;
                    $acreedor = 0;
                    foreach ($groupAcc as $key => $dataAcc) //recorrer datos
                    {
                        $valor_cargo = $dataAcc['cargo'];
                        $valor_abono = $dataAcc['abono'];
                        $valor_monto = $dataAcc['montos'];
                        $codigo_cuenta = $dataAcc['codigo'];
                        $nombre[$count] = $dataAcc['cuentas'];                
                        if($valor_cargo == 0)
                        {
                            $resultado_a = ($valor_abono * $valor_monto)/100; 
                            $historico_abono += $resultado_a;
                        }
                        if($valor_abono == 0)
                        {
                            $resultado_c = ($valor_cargo * $valor_monto)/100; 
                            $historico_cargo += $resultado_c;
                        }
                    }
        
                    if($historico_cargo > $historico_abono)
                    {
                        $deudor = $historico_cargo - $historico_abono;
                    }
                    else
                    {
                        $acreedor = $historico_abono - $historico_cargo;
                    }
         
                }
                   //aqui es para contabilizar los dias
                   $dias =(($inicio)-($final))/86400;
                   $dias = abs($dias); 
                   $dias = floor($dias);
                   $cantidad = ($presupuesto->cantidad/365)*($dias+1);

                   

                    $dias_a =(($inicio_f)-($final))/86400;
                    $dias_a = abs($dias_a); 
                    $dias_a = floor($dias_a);
                    $cantidad_acumulada = ($presupuesto->cantidad/365)*($dias_a+1);



                    if ($historico_cargo > $historico_abono) {
                        array_push($presupuestos,[
                            "id"   => $presupuesto->id,
                            "cantidad" => $cantidad,
                            "balanza_total"=>$monto,
                            "cuenta"   => $cuenta,
                            "balanza"  => $balanzas,
                            "acumulado" => $cantidad_acumulada,
                            "deudor" => $deudor,
                            "acreedor" => $acreedor,
                            'estatus' => 'deudor'
                        ]);
                    }
                    else{    
                        array_push($presupuestos,[
                            "id"   => $presupuesto->id,
                            "cantidad" => $cantidad,
                            "balanza_total"=>$monto,
                            "cuenta"   => $cuenta,
                            "balanza"  => $balanzas,
                            "acumulado" => $cantidad_acumulada,
                            "deudor" => $deudor,
                            "acreedor" => $acreedor,
                            'estatus' => 'acredor'
                        ]);
                    }
                
            }
            
            }else{
            Yii::$app->session->setFlash('danger', 'Favor de ingresar un rango de fechas valido.(la fecha de inicio no puede ser mayor a la fecha final)');
            }
        }
        
       

       return $presupuestos;
    }

}
