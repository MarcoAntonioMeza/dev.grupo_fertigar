<?php
    use yii\helpers\Url;
    use yii\helpers\Html;
    use app\assets\BootstrapTableAsset;
    use app\models\contabilidad\ContabilidadTransaccionDetail;
    use app\models\contabilidad\ContabilidadCuenta;

    BootstrapTableAsset::register($this);

    $this->title = 'TRANSACCIONES';
    $this->params['breadcrumbs'][] = 'CONTABILIDAD'; 
    $this->params['breadcrumbs'][] = ['label' => 'TRANSACCIONES', 'url' => ['detail?id='.$model[0]['contabilidad_transaccion_id']]];
    $this->params['breadcrumbs'][] = 'editar transaccion: '.$model[0]['contabilidad_transaccion_id'];



?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <form action="" method="POST" id="form-asientos">
                    <button type="button" class="btn btn-success" style="padding: 10px 15px;" onclick="GuardarAsientos()"> GUARDAR CONFIGURACIÓN <?=$model[0]['contabilidad_transaccion_id']?> </button>
                    <h3 class="panel-title" style="color:gray">Cuenta contable</h3>
                    <input type="text" hidden value="<?php echo $model[0]['contabilidad_transaccion_id'] ?>" name="parent_id" id="parent_id">
                    <div class="col-md-3">
                        <p style="font-size:14px">TIPO DE POLIZA</p>
                        <select class="form-control" name="poliza_type">
                            <option class="dropdown-item" value = '10' <?php if($cuentas[0]['data']->tipo_poliza==10){?> selected <?php }?>>INGRESO</option>
                            <option class="dropdown-item" value = '20' <?php if($cuentas[0]['data']->tipo_poliza==20){?> selected <?php }?>>EGRESO</option>
                            <option class="dropdown-item" value = '30' <?php if($cuentas[0]['data']->tipo_poliza==30){?> selected <?php }?>>CHEQUES</option>
                            <option class="dropdown-item" value = '40' <?php if($cuentas[0]['data']->tipo_poliza==40){?> selected <?php }?>>POLIZA DE DIARIO</option>
                        </select>
                    </div>
                    <div id="listas" style="margin: 10px;">
                    <?php foreach($cuentas as $key => $config){?>
                    <div class="row">
                            <div class="col-sm-3">
                                <label for="tag">CUENTA</label>
                                <input type="text" hidden id="id_cuenta_<?php echo $key?>" name="id_cuenta[<?php echo $key?>]" value="<?=$config['nombre']->id?>">
                                <input  type="text" class="form-control" placeholder="Escribe aquí..." id="cuenta_<?php echo $key?>" autocomplete="off" name="cuentas[<?php echo $key?>]" list="search_list" onkeyup="autocomp(this.value,<?php echo $key?>)" oninput="get_id_cuenta(<?php echo $key?>)" value="<?php echo $config['nombre']->nombre;?>">
                                    <datalist id="search_list" ></datalist>
                            </div>
                            <div class="col-sm-3">
                                <label for="campos[]">CARGO</label>
                                <input type="number" class="form-control" placeholder="%" id="cargo_<?php echo $key?>" name="cargos[<?php echo $key?>]" value="<?php echo $config['data']->cargo?>" min="0" max="100">
                            </div>
                            <div class="col-sm-3">
                                <label for="campos[]">ABONO</label>
                                <input type="number" class="form-control" placeholder="%" id="abono_<?php echo $key?>" name="abonos[<?php echo $key?>]" value="<?php echo $config['data']->abono?>" min="0" max="100">
                            </div>
                            <a href="#" class="remover_campo  btn btn-danger" style="margin-left:1150px; margin-top:-55px;">Eliminar cuenta <i class="fa fa-trash" style="margin-left:5px;"></i></a>
                        
                        </div>
                        <br>
                        <?php }?>
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
                    $('#listas').append(`
                    <div>\
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
        var form = document.getElementById('form-asientos');
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

            let id = document.getElementById('parent_id');
           
            $.ajax({
                url: VAR_URL_PATH + "contabilidad/transacciones/update?id=" +id.value,
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

                        toastr.success("SE REGISTRARON LOS CAMBIOS CORRECTAMENTE", "TRANSACCION");
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