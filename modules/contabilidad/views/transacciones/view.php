<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\contabilidad\ContabilidadTransaccion;
use app\models\contabilidad\ContabilidadTransaccionDetail;


$this->title = 'T-#: '. ContabilidadTransaccion::$tipoList[$model->tipo] .' - '. ContabilidadTransaccion::$motivoList[$model->motivo];
$this->params['breadcrumbs'][] = ['label' => 'CONTABILIDAD'];
$this->params['breadcrumbs'][] = ['label' => 'TRANSACCIONES', 'url' => ['index']];
?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <form action="" method="POST" id="form-asientos-contables">
                    <button type="button" class="btn btn-success" style="padding: 10px 15px;" onclick="GuardarAsientos()"> GUARDAR CONFIGURACIÓN </button>
                    <h3 class="panel-title" style="color:gray">Cuenta contable</h3>
                    <input type="text" hidden value="<?php echo $model->id ?>" name="parent_id">
                    <div class="col-md-3">
                        <p style="font-size:14px">TIPO DE POLIZA</p>
                        <?= Html::dropDownList('poliza_type', null, ContabilidadTransaccionDetail::$tipoPoliza, [ "prompt" => "--- SELECT ---","style" => "font-size:24px;font-weight:700; height: 50%;", "id" => "poliza_type_id", "class" => "form-control"  ]) ?>
                    </div>
                    <div id="listas" style="margin: 10px;">
                        <div class="row">
                            <div class="col-sm-3">
                                <label for="tag">CUENTA</label>
                                <input type="text" hidden id="id_cuenta_0" name="id_cuenta[0]">
                                <input  type="text" class="form-control" placeholder="Escribe aquí..." id="cuenta_0" autocomplete="off" name="cuentas[0]" list="search_list" onkeyup="autocomp(this.value,0)" oninput="get_id_cuenta(0)">
                                    <datalist id="search_list" ></datalist>
                            </div>
                            <div class="col-sm-3">
                                <label for="campos[]">CARGO</label>
                                <input type="number" class="form-control" placeholder="%" id="cargo_0" name="cargos[0]" value="0" min="0" max="100">
                            </div>
                            <div class="col-sm-3">
                                <label for="campos[]">ABONO</label>
                                <input type="number" class="form-control" placeholder="%" id="abono_0" name="abonos[0]" value="0" min="0" max="100">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-sm-3">
                                <label for="tag">CUENTA</label>
                                <input type="text" hidden id="id_cuenta_1" name="id_cuenta[1]">
                                <input type="text" class="form-control" placeholder="Escribe aquí..." id="cuenta_1" autocomplete="off" name="cuentas[1]" list="search_list" onkeyup="autocomp(this.value,1)" oninput="get_id_cuenta(1)">
                                <datalist id="search_list" >
                                </datalist>
                            </div>
                            <div class="col-sm-3">
                                <label for="campos[]">CARGO</label>
                                <input type="number" class="form-control" placeholder="%" id="cargo_1" name="cargos[1]" value="0">
                            </div>
                            <div class="col-sm-3">
                                <label for="campos[]">ABONO</label>
                                <input type="number" class="form-control" placeholder="%" id="abono_1" name="abonos[1]" value="0">
                            </div>
                        </div>
                        <br>
                    </div>
                    <span id="alerta_sobrecargo" style="margin-left: 26%; color:red;"></span><br>
                    <span id="alerta_sobreabono" style="margin-left: 26%; color:red;"></span>
                </form>
                <a class="btn btn-success" id="add_field" style="padding:10px 15px;"><i class="fa fa-plus-square fa-6" style="margin-right:5px";></i>Adicionar cuenta </a>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

function get_id_cuenta(param){

   var val=$('#cuenta_'+param).val();
   var ejemplo = $('#search_list').find('option[value="'+val+'"]').data('ejemplo');
   var id_input = 'id_cuenta_'+param;
   var formulario = document.getElementById(id_input);
   
    if(ejemplo != undefined){
        formulario.value = ejemplo;
    }
}
</script>

<script type="text/javascript">
    var campos_max          = 10;   //max de 10 campos
    var count = 2;
    var x = 0;

    $('#add_field').click (function(e) {
            e.preventDefault();     //prevenir nuevos clicks
            if (x < campos_max) {
                    $('#listas').append(`<div>\
                    <div class="row">
                            <div class="col-sm-3">
                                <label for="tag">CUENTA</label>   
                                <input type="text" hidden id="id_cuenta_`+count+`" name="id_cuenta[`+count+`]">   
                                <input type="text" class="form-control" placeholder="Escribe aquí..." id="cuenta_`+ count +`" name="cuentas[`+count+`]" list="search_list" onkeyup="autocomp(this.value,`+ count +`)" oninput="get_id_cuenta(`+count+`)">  
                                    <datalist id="search_list">
                                    </datalist>         
                            </div>
                            <div class="col-sm-3">
                                <label for="campos[]">CARGO</label>
                                <input type="number" class="form-control" placeholder="%" id="cargo_`+ count +`" name="cargos[`+count+`]" value="0">
                            </div>
                            <div class="col-sm-3">
                                <label for="campos[]">ABONO</label>
                                <input type="number" class="form-control" placeholder="%" id="abono_`+ count +`" name="abonos[`+count+`]" value="0">
                            </div> 
                        </div>\ 
                                <a href="#" class="remover_campo  btn btn-danger" style="margin-left:1150px; margin-top:-55px;">Eliminar cuenta <i class="fa fa-trash" style="margin-left:5px;"></i></a>\
                            </div>`);
                    x++;
                    count++;
            }
    });
    // Remover o div anterior
    $('#listas').on("click",".remover_campo",function(e) {
            e.preventDefault();
            $(this).parent('div').remove();
            x--;
    });
</script>

<script type="text/javascript">
    var VAR_URL_PATH = $('body').data('url-root');

        function autocomp(auto,id)
        {
            var id_campo = 'search_list';
            var contenedor = document.getElementById(id_campo);

            $.ajax({
                url: VAR_URL_PATH + "/contabilidad/transacciones/cuentas-ajax",
                data:{'busqueda':auto},
                type:'post',
                success: function (response)
                {
                    contenedor.innerHTML = "";
                    for (let index in response)
                    {
                        if (response)
                        {
                            contenedor.innerHTML+=`
                            <option data-ejemplo="`+response[index].id+`"  value="`+response[index].nombre+`">`+'['+response[index].code+']'+` `+response[index].nombre+`</option>
                            `;
                        }
                    }
                },
            });
        }

    function GuardarAsientos()
    {
        var form = document.getElementById('form-asientos-contables');
        let suma_cargo=0;
        let suma_abono=0;
        let info = new FormData(form);
        var cargos = [];
        var abonos = [];
        var verificacion=0;
            for (var i = 0; i>=0 && i<count ; i++) {
                /* console.log(info.get('cargos['+i+']')); */
            cargos.push(parseFloat(info.get('cargos['+i+']')));
            }
            for(var i=0 ;i<cargos.length;i++){
                suma_cargo += cargos[i];
            }
            for (var i = 0; i>=0 && i<count ; i++) {
                /* console.log(info.get('cargos['+i+']')); */
            abonos.push(parseFloat(info.get('abonos['+i+']')));
            }
            for(var i=0 ;i<abonos.length;i++){
                suma_abono += abonos[i];
            }
        if (suma_abono>100)
        {
        let titulo_abono = document.getElementById('alerta_sobreabono');
        titulo_abono.innerHTML= "";
        titulo_abono.textContent = "el total de los abonos no puede ser mayor a 100";
        }
        else
        {
        verificacion++;
        }

        if (suma_cargo>100)
        {
            let titulo_cargo = document.getElementById('alerta_sobrecargo');
                titulo_cargo.innerHTML= "";
                titulo_cargo.textContent = "el total de los cargos no puede ser mayor a 100";
        }
        else
        {
        verificacion++; 
        }
        if(verificacion == 2){
            $.ajax({
                url: VAR_URL_PATH + "contabilidad/transacciones/asientos-contables",
                data:info,
                type:'post',
                processData: false,
                contentType: false,
                success: function (response) {
                    if(response.code==202){
                         toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            showMethod: 'slideDown',
                            timeOut: 5000
                        };

                        toastr.success("SE REGISTRO CORRECTAMENTE LA OPERACION", "TRANSACCION");
                        window.location.href= VAR_URL_PATH+ 'contabilidad/transacciones/index';
                    }else{
                         toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            showMethod: 'slideDown',
                            timeOut: 5000
                        };
                        toastr.warning("VERIFICA TU INFORMACIÓN, INTENTA NUEVAMENTE", "TRANSACCION");
                    }
                }
            });
        }
    }
</script>