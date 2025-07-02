<?php
    use yii\helpers\Html;
    $this->title = 'BALANZA DE COMPROBACION';
    $this->params['breadcrumbs'][] = 'CONTABILIDAD'; 
    $this->params['breadcrumbs'][] = 'FINANZAS'; 
    $this->params['breadcrumbs'][] = 'BALANZA DE COMPROBACION';
?>
<style>
        a:hover
        {
            color:white;
        }
        .input_date
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
            <div class="contabilidad-balance-index">
            <div class="form-group row">
            <?= Html::beginForm(['index'], 'post', ['enctype' => 'multipart/form-data']) ?>
            <?php /* Html::a('<i class="fa fa-plus"></i> GENERAR ESTADO DE RESULTADOS ', ['resultados'], ['class' => 'btn btn-success add', 'style' => 'padding:10px; font-size:15px; margin-left:15px;']) */ ?>
            <?= Html::button("GENERAR ESTADO DE RESULTADOS", ["class" => "btn  btn-lg  btn-success",'data-target' => '#modal-alert', 'data-toggle'=> 'modal','id' => 'btnLoadEvidencia' ]) ?>

                <div class="col-lg-12"><h3>BALANZA DE COMPROBACION DEL: <input type="date" name="fecha_inicial" id="f_i" class="input_date" value="<?=$fecha_inicial?>" style="margin:0 20px;">AL<input type="date" name="fecha_final" id="f_f" class="input_date" value="<?=$fecha_final?>" style="margin:0 20px;"><?= Html::button("GENERAR", ["class" => "btn  btn-lg boton_search",'data-target' => '#modal-alert', 'data-toggle'=> 'modal','id' => 'btnLoadEvidencia' ]) ?><?= Html::button("GENERAR PDF", ["class" => "btn  btn-lg  btn-warning",'data-target' => '#modal-alert', 'data-toggle'=> 'modal','id' => 'btnLoadEvidencia' ]) ?><?php /* Html::submitButton('GENERAR', ['class' => 'boton_search'])?> <?= Html::a('<i class="fa fa-plus"></i> GENERAR PDF ', ['index', 'reporte' => 1], ['class' => 'btn btn-warning add','target' =>'_blank']) */ ?></h3></div>
                <?= Html::endForm() ?>
            </div>
                <table class="table table-hover">
                    <thead style ="background-color:#dcdcdc">
                        <tr>
                        <th scope="col">#</th>
                        <th scope="col">CUENTA</th>
                        <th scope="col">CARGO</th>
                        <th scope="col">ABONO</th>
                        <th scope="col">DEUDOR</th>
                        <th scope="col">ACREEDOR</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php foreach($responseArray as $key => $cuenta):?>
                        <tr>
                        <td><?= $cuenta['codigo']?></td>
                        <td><?= $cuenta['cuenta']?></td>
                        <td>$<?= $cuenta['historico_cargo']?></td>
                        <td>$<?= $cuenta['historico_abono']?></td>
                        <td>$<?= $cuenta['deudor']?></td>
                        <td>$<?= $cuenta['acreedor']?></td>
                        </tr>
                        <?php endforeach;?>
                        <tr>
                        <td></td>
                        <td style="text-align: right; font-weight:bold;">TOTAL: </td>
                        <td style="font-weight:bold;">$<?= $total_cargos?></td>
                        <td style="font-weight:bold;">$<?= $total_abonos?></td>
                        <td style="font-weight:bold;">$<?= $total_deudor?></td>
                        <td style="font-weight:bold;">$<?= $total_acreedor?></td>
                        </tr>
                    </tbody>
                </table>
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

    <script>
        $(document).ready(function() {
            fecha_actual = new Date();
            año = fecha_actual.getFullYear();
            fecha_init = document.getElementById('f_i');
            fecha_end  = document.getElementById('f_f');
            if(fecha_init.value == '' || fecha_init.value == null && fecha_end.value == '' || fecha_end.value == null)
            {
                fecha_init.value = año +'-01-01';
                fecha_end.value = año +'-12-31';
            }
        });
    </script>





