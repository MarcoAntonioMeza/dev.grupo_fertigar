<?php
    use yii\helpers\Html;
    $this->title = 'ESTADOS FINANCIEROS';
    $this->params['breadcrumbs'][] = 'CONTA...';  
    $this->params['breadcrumbs'][] = 'finan...';  
    $this->params['breadcrumbs'][] = ['label' => 'BALANZA DE COMPROBACION', 'url' => ['index']];
    $this->params['breadcrumbs'][] = 'ESTADOS DE RESULTADOS';
?>

<style>
        a:hover
        {
            color:white;
        }
        .input_date
        {
            padding: 2px 12px;
        }
        .boton_search
        {
            background-color: #2E8B57;
            border-radius: 4px;
            border:none;
            color: white;
            padding: 5px 24px;
            margin-left: 6px;    ;
        }
        .boton_search:hover
        {
            background-color: #8FBC8F;
        }
        .boton_generar
        {
            display: flex;
            background-color: blueviolet;
            border-radius: 4px;
            border:none;
            color: white;
            padding: 12px 34px;
            margin-left: 6px; 
            font-weight: bold;
            font-size: 11px;
        }
        .boton_generar:hover
        {
            background-color: lightskyblue;
        }
    </style>

    <div class="row">    
        <div class="ibox col-md-6">
            <div class="ibox-content" style="margin: 0px 12px; ">
                <div class="contabilidad-balance-index">
                    <div class="form-group row">
                        <form action="" id= "estados">
                            <div class="row" style="margin-left: 12px;">
                                <p style="font-size:9px;font-weight: bold;color: #000;"><strong class="lbl_check_tipo"></strong>
                                    
                                    ¿Desea Comparar Meses?
                                    <label class="switch">
                                        <input type="checkbox"  id="edos_comp" onfocus="ocultar()">
                                        <span class="slider round"></span>
                                    </label>
                                </p> 
                                <p>
                                    <a class='boton_generar' onclick='reporteURL()' target="_blank" id="btn_reporte" >GENERAR PDF<i class="fa fa-plus"></i></a>
                                 </p>
                            </div>
                            <div class="col-lg-12">
                                <h5>1 ESTADO DE RESULTADOS DEL:<input type="date" name="fecha_inicial" id="f_i1" class="input_date" value="" style="margin:0 5px;">AL
                                <input type="date" name="fecha_final" id="f_f1" class="input_date" value="" style="margin:0 5px;"> 
                                    <a class="boton_search" onclick="rellenarTabla()">Generar</a>
                                </h5>
                            </div>
                        </form>
                    </div>
                    <table class="table table-hover">
                        <thead style ="background-color:#dcdcdc">
                            <tr>
                            <th style="text-align:center; background-color: green; border-right:white 5px solid; ">INGRESOS</th>
                            <th colspan="3" style="text-align: center;border-right:white 5px solid;">LAPSO SELECCIONADO</th>
                            <th colspan="3" style="text-align: center;">ACUMULADO</th>
                            </tr>
                        </thead>
                        <tbody  id="tabla_ingresos">
                        <tr>
                                <td style="text-align: center; font-weight:bold">--CUENTA--</td>
                                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                                <td style="text-align: center; font-weight:bold">REAL</td>
                                <td style="text-align: center; font-weight:bold">VARIACION</td>
                                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                                <td style="text-align: center; font-weight:bold">REAL</td>
                                <td style="text-align: center; font-weight:bold">VARIACION</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-hover">
                        <thead style ="background-color:#dcdcdc">
                            <tr>
                            <th style="text-align:center; background-color: red; border-right:white 5px solid; ">GASTOS</th>
                            <th colspan="3" style="text-align: center;border-right:white 5px solid;">LAPSO SELECCIONADO</th>
                            <th colspan="3" style="text-align: center;">ACUMULADO</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_gastos">
                    <!-- #php foreach($responseArray as $key => $cuenta): -->
                            <tr>
                                <td style="text-align: center; font-weight:bold">--CUENTA--</td>
                                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                                <td style="text-align: center; font-weight:bold">REAL</td>
                                <td style="text-align: center; font-weight:bold">VARIACION</td>
                                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                                <td style="text-align: center; font-weight:bold">REAL</td>
                                <td style="text-align: center; font-weight:bold">VARIACION</td>
                            </tr>
                            <!-- #php endforeach; -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ibox col-md-6" id = "edos_box"  style = "display: none">
            <div class="ibox-content" style="margin: 0px 12px; ">
                <div class="contabilidad-balance-index">
                    <div class="form-group row" style="margin-top: 60px;">
                        <form action="" id= "estados_2">
                            <div class="col-lg-12">
                                <h5>2 ESTADO DE RESULTADOS DEL:<input type="date" name="fecha_inicial" id="f_i2" class="input_date" value="" style="margin:0 5px;">AL
                                    <input type="date" name="fecha_final" id="f_f2" class="input_date" value="" style="margin:0 5px;"> 
                                    <a class="boton_search" onclick="rellenarTabla2()">Generar</a>
                                </h5>
                            </div>
                        </form>
                    </div>
                    <table class="table table-hover">
                        <thead style ="background-color:#dcdcdc">
                            <tr>
                            <th style="text-align:center; background-color: green; border-right:white 5px solid; ">INGRESOS</th>
                            <th colspan="3" style="text-align: center;border-right:white 5px solid;">LAPSO SELECCIONADO</th>
                            <th colspan="3" style="text-align: center;">ACUMULADO</th>
                            </tr>
                        </thead>
                        <tbody  id="tabla_ingresos_2">
                        <tr>
                                <td style="text-align: center; font-weight:bold">--CUENTA--</td>
                                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                                <td style="text-align: center; font-weight:bold">REAL</td>
                                <td style="text-align: center; font-weight:bold">VARIACION</td>
                                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                                <td style="text-align: center; font-weight:bold">REAL</td>
                                <td style="text-align: center; font-weight:bold">VARIACION</td>
                            </tr>
                        </tbody>

                    </table>
                    <table class="table table-hover">
                        <thead style ="background-color:#dcdcdc">
                            <tr>
                            <th style="text-align:center; background-color: red; border-right:white 5px solid; ">GASTOS</th>
                            <th colspan="3" style="text-align: center;border-right:white 5px solid;">LAPSO SELECCIONADO</th>
                            <th colspan="3" style="text-align: center;">ACUMULADO</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_gastos_2">
                    <!-- #php foreach($responseArray as $key => $cuenta): -->
                            <tr>
                                <td style="text-align: center; font-weight:bold">--CUENTA--</td>
                                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                                <td style="text-align: center; font-weight:bold">REAL</td>
                                <td style="text-align: center; font-weight:bold">VARIACION</td>
                                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                                <td style="text-align: center; font-weight:bold">REAL</td>
                                <td style="text-align: center; font-weight:bold">VARIACION</td>
                            </tr>
                            <!-- #php endforeach; -->
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
        
<script>

var array_fecha_1 = [];
var array_fecha_2 = [];

function reporteURL(){
    var VAR_URL_PATH = $('body').data('url-root');
    var boton = document.getElementById('btn_reporte');
    var fecha_i1 = document.getElementById('f_i1');
    var fecha_f1 = document.getElementById('f_f1');
    var fecha_i2 = document.getElementById('f_i2');
    var fecha_f2 = document.getElementById('f_f2');
    
    var elm = document.getElementById('edos_comp');

    if (elm.checked) {

         boton.href = VAR_URL_PATH='edosfinancieros-pdf?'+'fecha_inicial='+fecha_i1.value+'&fecha_final='+fecha_f1.value+'&fecha_inicial2='+fecha_i2.value+'&fecha_final2='+fecha_f2.value;
        
    }
    else{

        boton.href = VAR_URL_PATH='edosfinancieros-pdf?'+'fecha_inicial='+fecha_i1.value+'&fecha_final='+fecha_f1.value;  

    }

}

    function ocultar(e){
        var elm = document.getElementById('edos_comp');
        var fi= document.getElementById('f_i2');
        var ff = document.getElementById('f_f2');
        

        elm.addEventListener('click', function(){
            if(elm.checked){
                document.getElementById('edos_box').style.display = 'block';
                if(fi.value != '' && ff.value!=''){
                    rellenarTabla2();
                }
                
            } else{
                document.getElementById('edos_box').style.display = 'none';
                array_fecha_2 = [];
            }
        });
    }

    function generar_reporte(){
        var VAR_URL_PATH = $('body').data('url-root');
        var checkbox = document.getElementById('edos_comp');

        if (array_fecha_1!='') {
        var tabla_1 = array_fecha_1;
        console.log(tabla_1);
        }

       
        if(checkbox.checked){
            if(array_fecha_2!=''){
                var tabla_2 = array_fecha_2;
            }
        }else{
            
            var tabla_2 = null;

        }

        if (tabla_1!='' && tabla_1!=null) {

            $.ajax({
            url: VAR_URL_PATH + "/contabilidad/estados/imprimir-balanza",
            data:{'tabla':tabla_1,'comparativa':tabla_2},
            enctype: 'multipart/form-data',
            method: "POST",
            success: function (response) {
                },
                statusCode: {
                    404: function() {
                        alert('web not found');
                    }
                }
            });
            
        }else{
            console.log('Primero debe realizar una busqueda');
        }


    }

    function rellenarTabla(){
        var VAR_URL_PATH = $('body').data('url-root');
        var form = document.getElementById('estados');
        let info = new FormData(form);
        $.ajax({
        url: VAR_URL_PATH + "/contabilidad/estados/edosfinancieros",
        data:info,
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        method: "POST",
        success: function (response) {
            
            if (response==null) {
                location.reload();
            }
            if (response=='') {
                location.reload();
            }
            array_fecha_1 = response;
            let tablai = document.getElementById('tabla_ingresos');
            tablai.innerHTML = '';
            let tablag = document.getElementById('tabla_gastos');
            tablag.innerHTML = '';
            var encabezado = `
            <tr>
                <td style="text-align: center; font-weight:bold">--CUENTA--</td>
                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                <td style="text-align: center; font-weight:bold">REAL</td>
                <td style="text-align: center; font-weight:bold">VARIACION</td>
                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                <td style="text-align: center; font-weight:bold">REAL</td>
                <td style="text-align: center; font-weight:bold">VARIACION</td>
            </tr>`;
            $("#tabla_ingresos").append(encabezado);

            for(var i=0; i<response.length; i++){
              
                //operaciopnes 
                let real = 0;
                let variacion = 0;
                let acumulado = 0;

                for (let j = 0; j < response[i].balanza.length; j++) {
                    real = real + response[i].balanza[j].montos;
                }

                variacion = response[i].cantidad - real;

                var tr = `<tr>
                    <td>`+response[i].cuenta['code']+' '+response[i].cuenta['nombre']+`</td>
                    <td>`+response[i].cantidad.toFixed(2)+`</td>
                    <td>`+real+`</td>
                    <td>`+variacion.toFixed(2)+`</td>
                    <td>`+response[i].cantidad.toFixed(2)+`</td>
                    <td>`+response[i].acumulado.toFixed(2)+`</td>
                    <td>`+acumulado+`</td>
                </tr>`;

                  //condicionales
                  if (response[i].cuenta['code']>=400 && response[i].cuenta['code']<500) {
                    $("#tabla_ingresos").append(tr);
                }
                if(response[i].cuenta['code']>=600 && response[i].cuenta['code']<700){
                    $("#tabla_gastos").append(tr);
                }

            }
            },
            statusCode: {
                404: function() {
                    alert('web not found');
                }
            }
        });
    }

    function rellenarTabla2(){
        var VAR_URL_PATH = $('body').data('url-root');
        var form = document.getElementById('estados_2');
        let info = new FormData(form);
        $.ajax({
        url: VAR_URL_PATH + "/contabilidad/estados/edosfinancieros",
        data:info,
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        method: "POST",
        success: function (response) {

            if (response==null) {
                location.reload();
            }
            if (response=='') {
                location.reload();
            }
            
            array_fecha_2 = response;
            let tablai = document.getElementById('tabla_ingresos_2') 
            tablai.innerHTML = ''; 
            let tablag = document.getElementById('tabla_gastos_2');
            tablag.innerHTML = '';
            var encabezado = `
            <tr>
                <td style="text-align: center; font-weight:bold">--CUENTA--</td>
                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                <td style="text-align: center; font-weight:bold">REAL</td>
                <td style="text-align: center; font-weight:bold">VARIACION</td>
                <td style="text-align: center; font-weight:bold">PRESUPUESTO</td>
                <td style="text-align: center; font-weight:bold">REAL</td>
                <td style="text-align: center; font-weight:bold">VARIACION</td>
            </tr>`;
            $("#tabla_ingresos_2").append(encabezado);

            for(var i=0; i<response.length; i++){
                //operaciopnes 
                let real = 0;
                let variacion = 0;
                let acumulado = 0;

                for (let j = 0; j < response[i].balanza.length; j++) {
                    real = real + response[i].balanza[j].montos;
                }

                variacion = response[i].cantidad - real;

                var tr = `<tr>
                    <td>`+response[i].cuenta['code']+' '+response[i].cuenta['nombre']+`</td>
                    <td>`+response[i].cantidad.toFixed(2)+`</td>
                    <td>`+real+`</td>
                    <td>`+variacion.toFixed(2)+`</td>
                    <td>`+response[i].cantidad.toFixed(2)+`</td>
                    <td>`+response[i].acumulado.toFixed(2)+`</td>
                    <td>`+acumulado+`</td>
                </tr>`;
                
                //condicionales
                if (response[i].cuenta['code']>=400 && response[i].cuenta['code']<500) {
                    $("#tabla_ingresos_2").append(tr);
                }
                if(response[i].cuenta['code']>=600 && response[i].cuenta['code']<700){
                    $("#tabla_gastos_2").append(tr);
                }

            }
            },
            statusCode: {
                404: function() {
                    alert('web not found');
                }
            }
        });
    }
</script>