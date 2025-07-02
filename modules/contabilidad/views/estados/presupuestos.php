<?php
    use yii\helpers\Html;
    $this->title = 'PRESUPUESTOS';
    $this->params['breadcrumbs'][] = 'CONTABILIDAD'; 
    $this->params['breadcrumbs'][] = 'FINANZAS'; 
    $this->params['breadcrumbs'][] = 'PRESUPUESTOS';
    ?>

<style>
        a:hover
        {
            color:white;
        }
        .input_number
        {
            padding: 6px 48px;
        }
        .boton_search
        {
            background-color: #2E8B57;
            border-radius: 4px;
            border:none;
            color: white;
            padding: 10px 48px;
            margin-left: 24px;    ;
        }
        .boton_search:hover
        {
            background-color: #8FBC8F;
        }
    </style>
<div class="ibox">
        <div class="ibox-content">
            <div class="contabilidad-presupuestos">
            <?= Html::beginForm(['presupuestos'], 'post', ['enctype' => 'multipart/form-data']) ?>
            <div class="form-group row">
                <div class="col-lg-12"><h3> SELECCIONE EL EJERCICIO: <input type="number" name="year" id="year" class="input_date" min= "2022" max="3000"  value= "2023" style="margin:0 20px;"><?php /* Html::submitButton('GUARDAR PRESUPUESTO ANUAL', ['class' => 'boton_search']) */?> <?= Html::button("GUARDAR PRESUPUESTO ANUAL", ["class" => "btn  btn-lg  boton_search",'data-target' => '#modal-alert', 'data-toggle'=> 'modal','id' => 'btnLoadEvidencia' ]) ?></h3></div>
            </div>
                <table class="table table-hover">
                    <thead style ="background-color:#dcdcdc">
                        <tr>
                        <th class="col-lg-2" style="text-align:center; background-color: #48F3FA; border-right:white 5px solid; font-size:14px">INGRESOS</th>
                        <th class="col-lg-2" style="text-align: center; border-right:white 5px solid; font-size:14px">ASIGNA EL PRESUPUESTO ANUAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($presupuestos[0]['ingresos'] as $key => $ingresos) :?>
                        <tr>
                            <td style="text-align: center; font-weight:bold"><?= $ingresos->code . ' '. $ingresos->nombre?></td>
                            <td> <center> <?= Html::input('number', 'ingresos['.$key.'][cuenta]', $presupuestos[0]['ingresos'][$key]['id'] , ['class' => 'input_number', 'type'=> 'hidden']) ?> <?= Html::input('number', 'ingresos['.$key.'][monto]', '0', ['class' => 'input_number','min' => '0','style' => 'font-weight:bold; font-size:14px; text-align:right;']) ?></center></td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
                <table class="table table-hover">
                    <thead style ="background-color:#dcdcdc">
                        <tr>
                        <th class="col-lg-2" style="text-align:center; background-color:#F07105; border-right:white 5px solid; font-size:14px">GASTOS</th>
                        <th class="col-lg-2" style="text-align: center; border-right:white 5px solid; font-size:14px">ASIGNA EL PRESUPUESTO ANUAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($presupuestos[0]['gastos'] as $key => $gastos) :?>
                            <tr>
                            <td style="text-align: center; font-weight:bold"><?=$gastos->code . ' '.$gastos->nombre?></td>
                            <td> <center> <?= Html::input('number', 'gastos['.$key.'][cuenta]', $presupuestos[0]['gastos'][$key]['id'] , ['class' => 'input_number', 'type'=> 'hidden']) ?> <?= Html::input('number', 'gastos['.$key.'][monto]', '0', ['class' => 'input_number', 'min' => '0','style' => 'font-weight:bold; font-size:14px; text-align:right;s']) ?></center></td>
                        </tr>
                            <?php endforeach;?>
                    </tbody>
                </table>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>


<div class="fade modal inmodal " id="modal-alert"    role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" style="padding-top: 15%;" >
        <div class="modal-content">
            <div style="padding: 25PX;" class="ibox-content">
                 <div class="row">
                    <div class="col-sm-12 text-center">
                        <h2 class="text_load lbl_tipo"> !AVISO¡ DATOS INSUFICIENTES, INTENTE NUEVAMENTE </h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>