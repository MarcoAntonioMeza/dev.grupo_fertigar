<?php
namespace app\modules\contabilidad\controllers;
use Yii;
use yii\web\Response;
use kartik\mpdf\Pdf;
use app\models\contabilidad\ContabilidadPolizaDetail;
use app\models\contabilidad\ContabilidadPresupuestos;


class EstadosController extends \app\controllers\AppController
{
    private $can;
    public function init()
    {
        parent::init();
            $this->can = [
                'create' => Yii::$app->user->can('admin'),
                'update' => Yii::$app->user->can('admin'),
                'delete' => Yii::$app->user->can('admin'),
                'view' => Yii::$app->user->can('admin'),
            ]; 
    }
    /** 
     * Renders the index view for the module
     * @return string
     */
    
    public function actionIndex($reporte = null)
    {
        $request = Yii::$app->request->post();
        $is_report = $reporte;
        $año_actual= date('Y');
        $fecha_init= $año_actual.'-01-01';
        $fecha_end= $año_actual.'-12-31';
        if($request)
        {
            $fecha_inicial = $request['fecha_inicial'];
            $fecha_final = $request['fecha_final'];
        }
        else
        {
            $fecha_inicial = $fecha_init;
            $fecha_final = $fecha_end;
        }

        $total_cargo_sum = 0;
        $total_abono_sum = 0;
        $total_deudor_sum = 0;
        $total_acreedor_sum = 0;
        $group_id = [];
        $responseArray = [];   
        
        if ($fecha_inicial == null && $fecha_final == null) {
             $balanzas = ContabilidadPolizaDetail::getBalanzaParams($fecha_init, $fecha_end);
        }
        else
        {
            $balanzas = ContabilidadPolizaDetail::getBalanzaParams($fecha_inicial, $fecha_final);
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

            array_push($responseArray,[
                "codigo"   => $codigo_cuenta,
                "cuenta"   => $nombre[$count],
                "historico_cargo" => $historico_cargo,
                "historico_abono" => $historico_abono,
                "deudor" => $deudor,
                "acreedor" => $acreedor,
            ]);  
        }
        foreach ($responseArray as $key => $conjunto) 
        {
            $total_cargo_sum += $conjunto['historico_cargo'];
            $total_abono_sum += $conjunto['historico_abono'];
            $total_deudor_sum += $conjunto['deudor'];
            $total_acreedor_sum += $conjunto['acreedor'];
        }
        if ($is_report != '' || $is_report != null) 
        {
            $report_totales = [
                'fecha_inicial'=> $fecha_inicial,
                'fecha_final' => $fecha_final,
                'total_cargo'=> $total_cargo_sum,
                'total_abono'=> $total_abono_sum,
                'total_deudor'=>$total_deudor_sum,
                'total_acreedor'=>$total_acreedor_sum,
            ];
            $content = $this->renderPartial('report_balanza', ['report_totales' => $report_totales, 'responseArray' => $responseArray ]);
            $pdf = new Pdf([
                // set to use core fonts only
                'mode' => Pdf::MODE_CORE,
                // A4 paper format
                'format' => Pdf::FORMAT_LETTER,
                // portrait orientation
                'orientation' => Pdf::ORIENT_PORTRAIT,
                // stream to browser inline
                'destination' => Pdf::DEST_BROWSER,
                // your html content input
                'content' => $content,
                // format content from your own css file if needed or use the
                // enhanced bootstrap css built by Krajee for mPDF formatting
                'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                // any css to be embedded if required
                'cssInline' => '.kv-heading-1{font-size:18px}',
                 // set mPDF properties on the fly
                 // call mPDF methods on the fly
            ]);
            // return the pdf output as per the destination setting
            return $pdf->render();         
        }
        else
        {
            return $this->render('index',[
                'fecha_inicial' => $fecha_inicial, 
                'fecha_final' => $fecha_final, 
                'total_cargos' => $total_cargo_sum,
                'total_abonos' => $total_abono_sum,
                'total_deudor' => $total_deudor_sum,
                'total_acreedor' => $total_acreedor_sum,
                'responseArray' => $responseArray,
                'can' => $this->can,
            ]);
        }
    }
    public function actionPresupuestos()
    {   
        $response = ContabilidadPresupuestos::presupuestos();
        $request = Yii::$app->request->post();
        /* $validacion =  ContabilidadPresupuestos::find()->where(['=', 'id_cuenta', $request['cuenta']])->all(); */ /* PARA LA VALIDACION DE EXISTENCIAS */
        if($request) 
        {
            
            if($request['ingresos'])
            {
                foreach ($request['ingresos'] as $key => $ingreso) 
                {
                    $model = new ContabilidadPresupuestos();
                    $model->year = $request['year'];
                    $model->id_cuenta = $ingreso['cuenta'];
                    $model->cantidad = $ingreso['monto'];
                    $model->save();
                }
                Yii::$app->session->setFlash('success', "dato guardado exitosamente");
            }
            else 
            {
                Yii::$app->session->setFlash('warning', "Ocurrio un error inesperado");
            }
            if($request['gastos'])
            {
                foreach ($request['gastos'] as $key => $gasto) 
                {
                    $model = new ContabilidadPresupuestos();
                    $model->year = $request['year'];
                    $model->id_cuenta = $gasto['cuenta'];
                    $model->cantidad = $gasto['monto'];
                    $model->save();
                }
                Yii::$app->session->setFlash('success', "dato guardado exitosamente");
            }
            else 
            {
                Yii::$app->session->setFlash('warning', "Ocurrio un error inesperado");
            }
             
        }
            return $this->render('presupuestos',[
                "can" => $this->can,
                "presupuestos" => $response,
            ]);                 
    }
    public function actionResultados()
    {
        $response = ContabilidadPresupuestos::presupuestos();

        return $this->render('estados_resultados',[
            'estados' => $response,
        ]);
    }
    public function actionEdosfinancieros()
    {
        $request = Yii::$app->request->post();
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        $fecha_init = strtotime($request['fecha_inicial']);
        $fecha_end = strtotime($request['fecha_final']);

        if ($fecha_init && $fecha_end) 
        {
            $result = ContabilidadPresupuestos::estados($fecha_init,$fecha_end);
            return $result;
        }
        
        Yii::$app->session->setFlash('danger', 'Favor de ingresar las fechas.');  
    }

    public function actionEdosfinancierosPdf($fecha_inicial, $fecha_final,$fecha_inicial2 = null, $fecha_final2 = null)
    {
       
        $variables_0=[];
        $variables_1=[];
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $fecha_init = strtotime($fecha_inicial);
        $fecha_end = strtotime($fecha_final);
        $fecha_init2 = strtotime($fecha_inicial2);
        $fecha_end2 = strtotime($fecha_final2);

        if (isset($fecha_init) && isset($fecha_end)) 
        {
            $result = ContabilidadPresupuestos::estados($fecha_init,$fecha_end);
           
            $variables_0= $result;
            
        }

        if (isset($fecha_init2) && isset($fecha_end2)) 
        {
            $result = ContabilidadPresupuestos::estados($fecha_init2,$fecha_end2);
            $variables_1= $result;
        }
        
        $lengh = 279;
        $width = 215;

        if(isset($variables_1) && isset($variables_0))
        {
           $content = $this->renderPartial('../../../contabilidad/views/estados/comparacion_edos_pdf', ["balanza_general" => $variables_0,'comparacion_edos'=>$variables_1]);
        }
        else
        {
             $content = $this->renderPartial('../../../contabilidad/views/estados/estados_pdf.php', ["balanza_general" => $variables_0]);     
        }

        Yii::$app->session->setFlash('danger', 'Favor de ingresar las fechas.');  
        ini_set('memory_limit', '-1');
        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array($width, $lengh),//Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
             // set mPDF properties on the fly
            'options' => ['title' => 'Ticket de envio'],
             // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>[ 'TICKET #' . $model->id],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft = 24;
        $pdf->marginRight = 24;

        // return the pdf output as per the destination setting
        return $pdf->render();
    }    
}