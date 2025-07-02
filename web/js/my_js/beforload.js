// Ajax
    jQuery.each(["put", "delete"], function (i, method) {
        jQuery[ method ] = function (url, data, callback, type) {
            if (jQuery.isFunction(data)) {
                type = type || callback;
                callback = data;
                data = undefined;
            }

            return jQuery.ajax({
                url: url,
                type: method,
                dataType: type,
                data: data,
                success: callback
            });
        };
    });

document.addEventListener("DOMContentLoaded", function () {
    // Para formularios
    document.querySelectorAll("form").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            const submitButton = form.querySelector(".btn-loading");
            if (submitButton) {
                mostrarSpinner(submitButton);
            }
        });
    });

    // Para botones/enlaces fuera de formularios
    document.querySelectorAll(".btn-loading").forEach(function (button) {
        // Evita aplicar doble evento si ya está dentro de un form
        if (!button.closest("form")) {
            button.addEventListener("click", function (e) {
                mostrarSpinner(button);
            });
        }
    });

    function mostrarSpinner(button) {
        if (button.disabled) return; // evita doble clic
        button.disabled = true;
        const originalText = button.innerHTML;
        button.dataset.originalText = originalText;
        button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...`;

        // Revertir después de 3 segundos (solo si es necesario, útil en botones que no redirigen)
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText;
        }, 3000);
    }
});

// Is Int and Float
    function isInt(n){
        return Number(n) === n && n % 1 === 0;
    }

    function isFloat(n){
        return Number(n) === n && n % 1 !== 0;
    }


// formatMoney - Number
    Number.prototype.formatMoney = function(c, d, t){
        var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    };


// format - Date
    Date.prototype.format = function(e){
        var t="";var n=Date.replaceChars;for(var r=0;r<e.length;r++){var i=e.charAt(r);if(r-1>=0&&e.charAt(r-1)=="\\"){t+=i}else if(n[i]){t+=n[i].call(this)}else if(i!="\\"){t+=i}}return t};Date.replaceChars={shortMonths:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],longMonths:["January","February","March","April","May","June","July","August","September","October","November","December"],shortDays:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],longDays:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],d:function(){return(this.getDate()<10?"0":"")+this.getDate()},D:function(){return Date.replaceChars.shortDays[this.getDay()]},j:function(){return this.getDate()},l:function(){return Date.replaceChars.longDays[this.getDay()]},N:function(){return this.getDay()+1},S:function(){return this.getDate()%10==1&&this.getDate()!=11?"st":this.getDate()%10==2&&this.getDate()!=12?"nd":this.getDate()%10==3&&this.getDate()!=13?"rd":"th"},w:function(){return this.getDay()},z:function(){var e=new Date(this.getFullYear(),0,1);return Math.ceil((this-e)/864e5)},W:function(){var e=new Date(this.getFullYear(),0,1);return Math.ceil(((this-e)/864e5+e.getDay()+1)/7)},F:function(){return Date.replaceChars.longMonths[this.getMonth()]},m:function(){return(this.getMonth()<9?"0":"")+(this.getMonth()+1)},M:function(){return Date.replaceChars.shortMonths[this.getMonth()]},n:function(){return this.getMonth()+1},t:function(){var e=new Date;return(new Date(e.getFullYear(),e.getMonth(),0)).getDate()},L:function(){var e=this.getFullYear();return e%400==0||e%100!=0&&e%4==0},o:function(){var e=new Date(this.valueOf());e.setDate(e.getDate()-(this.getDay()+6)%7+3);return e.getFullYear()},Y:function(){return this.getFullYear()},y:function(){return(""+this.getFullYear()).substr(2)},a:function(){return this.getHours()<12?"am":"pm"},A:function(){return this.getHours()<12?"AM":"PM"},B:function(){return Math.floor(((this.getUTCHours()+1)%24+this.getUTCMinutes()/60+this.getUTCSeconds()/3600)*1e3/24)},g:function(){return this.getHours()%12||12},G:function(){return this.getHours()},h:function(){return((this.getHours()%12||12)<10?"0":"")+(this.getHours()%12||12)},H:function(){return(this.getHours()<10?"0":"")+this.getHours()},i:function(){return(this.getMinutes()<10?"0":"")+this.getMinutes()},s:function(){return(this.getSeconds()<10?"0":"")+this.getSeconds()},u:function(){var e=this.getMilliseconds();return(e<10?"00":e<100?"0":"")+e},e:function(){return"Not Yet Supported"},I:function(){var e=null;for(var t=0;t<12;++t){var n=new Date(this.getFullYear(),t,1);var r=n.getTimezoneOffset();if(e===null)e=r;else if(r<e){e=r;break}else if(r>e)break}return this.getTimezoneOffset()==e|0},O:function(){return(-this.getTimezoneOffset()<0?"-":"+")+(Math.abs(this.getTimezoneOffset()/60)<10?"0":"")+Math.abs(this.getTimezoneOffset()/60)+"00"},P:function(){return(-this.getTimezoneOffset()<0?"-":"+")+(Math.abs(this.getTimezoneOffset()/60)<10?"0":"")+Math.abs(this.getTimezoneOffset()/60)+":00"},T:function(){var e=this.getMonth();this.setMonth(0);var t=this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/,"$1");this.setMonth(e);return t},Z:function(){return-this.getTimezoneOffset()*60},c:function(){return this.format("Y-m-d\\TH:i:sP")},r:function(){return this.toString()},U:function(){return this.getTime()/1e3}
    }


// Obtener Edad
    var getEdad = function(string) {
        return ((Date.now() / 1000) - (new Date(string).getTime() / 1000)) / (365 * 24 * 60 * 60);
    }


// HTML Rating
    var html_rating = function(value, size){
        switch(value){
            case 1: rating = ''; break;
            case 2: rating = ''; break;
            case 3: rating = ''; break;
            case 4: rating = ''; break;
            case 5: rating = ''; break;
            default: rating = ''; break;
        }

        return '<div data-content="" class="rating-container rating-gly-star"><div style="width: 100%;" data-content="' + rating + '" class="rating-stars"></div></div>';
    }


// Estado del Servidor
    var nifty_avg = function(avg_url, avg_interval){
        avg_load_html(avg_url);

        setInterval(function(){
            avg_load_html(avg_url);

        }, avg_interval);
    };

    var avg_load_html = function(avg_url){
        var $wg_server = $('#wg-server'),
            elem = {
                $label_cpu_use    : $('.label-cpu-use', $wg_server),
                $progress_bar_cpu : $('.progress-bar-cpu', $wg_server),
                $label_mem_use    : $('.label-mem-use', $wg_server),
                $progress_bar_mem : $('.progress-bar-mem', $wg_server),
            };

        $.get(avg_url + '/avg_load.php', function(json){
            elem.$label_cpu_use.html(json.cpu + '%');
            elem.$progress_bar_cpu.css('width', json.cpu + '%');
            elem.$label_mem_use.html(json.mem + '%');
            elem.$progress_bar_mem.css('width', json.mem + '%');
        }, 'json');
    };

// BootstrapTable - Get height
    var getBttHeight = function() {
        // COMENTE POR EL TEMPLETE INSPINA CAUSABA PROBLEMAS
        //var contents_height = $('.navbar').outerHeight() + $('#page-title').outerHeight() + $('.breadcrumb').outerHeight() + $('#footer').outerHeight(),
        var contents_height = 0,
            bt_table_height = $(window).height() - contents_height + 80,
            bt_table_height = bt_table_height < 500? 500: bt_table_height;

        return bt_table_height;
    }


// BootstrapTable - Resize height
    var resizeBootstrapTable = function($btt) {
        $btt.bootstrapTable('resetView', {'height':getBttHeight()});
    }

// My BootstrapTable Builder
function MyBttBuilder2(params) {
    var plugin   = this;
    plugin.toolbar         = params.element + ' .btt-toolbar';
    plugin.$filters        = $(params.element + ' .btt-toolbar :input, .filter-top :input,#cliente-cliente_id,#proveedor-proveedor_id,.buscador :input');
    plugin.url             = params.url;
    plugin.colorProducto   = params.colorProducto ? true : false;
    plugin.colorCompra      = params.colorCompra ? true : false;
    plugin.autoHeight      = params.autoHeight;
    plugin.$bootstrapTable = $(params.element + ' .bootstrap-table-saldos');
    console.log(plugin.$filters);
    bootstrapTableParamsDefault = {
        search      : true,
        showRefresh : true,
        showColumns : true,
        showToggle  : true,
        pagination  : true,
        icons: {
            paginationSwitchDown: 'fa-caret-square-o-down',
            paginationSwitchUp: 'fa-caret-square-o-up',
            refresh: 'fa-refresh',
            toggleOff: 'fa-toggle-off',
            toggleOn: 'fa-toggle-on',
            columns: 'fa-th-list',
            detailOpen: 'fa-plus',
            detailClose: 'fa-minus',
            fullscreen: 'fa-arrows-alt',
            search: 'fa-search',
            export: 'fa fa-download',
            clearSearch: 'fa-trash'
        },
        sidePagination : 'server',
        showPaginationSwitch : true,
        pageList    : [50, 100, 500, 1000, 2000, 5000, 10000],
        pageSize    : 100,
        sortName    : 'id',
        sortOrder   : 'desc',

        showExport       : true,
        exportTypes      : ['xlsx', 'csv', 'xml'],
        mobileResponsive : true,
        cookie           : true,
        resizable        : true,
        cookieIdTable    : 'ES-bootstrap-table-' + params.id,
        toolbar          : plugin.toolbar,
        queryParams : function(params){
            params['filters'] = plugin.$filters.serialize();

            return params;
        },
        onLoadSuccess : function(row, $element){
            if(plugin.autoHeight !== false)
                resizeBootstrapTable(plugin.$bootstrapTable);
        },

        onSearch: function () {
            this.formatLoadingMessage();
        },
        rowStyle : function (row, index ,params) {

            if (plugin.colorProducto) {
                if (parseInt(row.validate) == 20 ) {
                    if ((new Date( parseInt(row.fecha_autorizar) * 1000))  < new Date() ) {
                        return {
                            classes : "label-danger",
                        }
                    }
                }
            }
            if (plugin.colorCompra) {
                if (parseInt(row.diferencia) != 0 ) {

                    return {
                        classes : "label-danger",
                    }

                }
            }

            return {
                classes : "white",
            }

        }
    };

    bootstrapTableParams = Object.assign(bootstrapTableParamsDefault, params.bootstrapTable);

    plugin.$bootstrapTable.bootstrapTable(bootstrapTableParams);




    // Filtros y agrupación
    plugin.refresh = function(){

        plugin.$bootstrapTable.bootstrapTable('refresh', {'url': plugin.url});
    }





    // Responsivo
    plugin.resize = function(timeout){
        setTimeout(function(){
            resizeBootstrapTable(plugin.$bootstrapTable);
        }, timeout);
    }

    if(plugin.autoHeight !== false){
        $(window).resize(function(){
            //plugin.$bootstrapTable.bootstrapTable('hideLoading');
            resizeBootstrapTable(plugin.$bootstrapTable);
        });

        plugin.resize(1000);
    }


    plugin.$filters
        .on('change', function(){

            plugin.refresh();

        });
    //.trigger('change');


    /*$(window).resize(function(){
        resizeBootstrapTable(plugin.$bootstrapTable);
        console.log("entroo 2");
    });*/


    /*plugin.refresh = function(){
        plugin.$bootstrapTable.bootstrapTable('refresh', {'url': plugin.url});

        setTimeout(function(){
            resizeBootstrapTable(plugin.$bootstrapTable);
        }, 1000);
    }*/

}
    function MyBttBuilder(params) {
        var plugin   = this;
        plugin.toolbar         = params.element + ' .btt-toolbar';
        plugin.$filters        = $(params.element + ' .btt-toolbar :input, .filter-top :input,#cliente-cliente_id,#proveedor-proveedor_id,.buscador :input');
        plugin.url             = params.url;
        plugin.colorProducto   = params.colorProducto ? true : false;
        plugin.colorCompra      = params.colorCompra ? true : false;
        plugin.autoHeight      = params.autoHeight;
        plugin.$bootstrapTable = $(params.element + ' .bootstrap-table');
        console.log(plugin.$filters);
        bootstrapTableParamsDefault = {
            search      : true,
            showRefresh : true,
            showColumns : true,
            showToggle  : true,
            pagination  : true,
            icons: {
              paginationSwitchDown: 'fa-caret-square-o-down',
              paginationSwitchUp: 'fa-caret-square-o-up',
              refresh: 'fa-refresh',
              toggleOff: 'fa-toggle-off',
              toggleOn: 'fa-toggle-on',
              columns: 'fa-th-list',
              detailOpen: 'fa-plus',
              detailClose: 'fa-minus',
              fullscreen: 'fa-arrows-alt',
              search: 'fa-search',
              export: 'fa fa-download',
              clearSearch: 'fa-trash'
            },
            sidePagination : 'server',
            showPaginationSwitch : true,
            pageList    : [50, 100, 500, 1000, 2000, 5000, 10000],
            pageSize    : 100,
            sortName    : 'id',
            sortOrder   : 'desc',

            showExport       : true,
            exportTypes      : ['xlsx', 'csv', 'xml'],
            mobileResponsive : true,
            cookie           : true,
            resizable        : true,
            cookieIdTable    : 'ES-bootstrap-table-' + params.id,
            toolbar          : plugin.toolbar,
            queryParams : function(params){
                params['filters'] = plugin.$filters.serialize();

                return params;
            },
            onLoadSuccess : function(row, $element){
                if(plugin.autoHeight !== false)
                    resizeBootstrapTable(plugin.$bootstrapTable);
            },

            onSearch: function () {
                this.formatLoadingMessage();
            },
            rowStyle : function (row, index ,params) {

                if (plugin.colorProducto) {
                    if (parseInt(row.validate) == 20 ) {
                        if ((new Date( parseInt(row.fecha_autorizar) * 1000))  < new Date() ) {
                            return {
                                classes : "label-danger",
                            }
                        }
                    }
                }
                if (plugin.colorCompra) {
                    if (parseInt(row.diferencia) != 0 ) {

                            return {
                                classes : "label-danger",
                            }

                    }
                }

                return {
                    classes : "white",
                }

            }
        };

        bootstrapTableParams = Object.assign(bootstrapTableParamsDefault, params.bootstrapTable);

        plugin.$bootstrapTable.bootstrapTable(bootstrapTableParams);




        // Filtros y agrupación
        plugin.refresh = function(){

            plugin.$bootstrapTable.bootstrapTable('refresh', {'url': plugin.url});
        }





        // Responsivo
            plugin.resize = function(timeout){
                setTimeout(function(){
                    resizeBootstrapTable(plugin.$bootstrapTable);
                }, timeout);
            }

            if(plugin.autoHeight !== false){
                $(window).resize(function(){
                    //plugin.$bootstrapTable.bootstrapTable('hideLoading');
                    resizeBootstrapTable(plugin.$bootstrapTable);
                });

                plugin.resize(1000);
            }


            plugin.$filters
            .on('change', function(){

               plugin.refresh();

            });
            //.trigger('change');


        /*$(window).resize(function(){
            resizeBootstrapTable(plugin.$bootstrapTable);
            console.log("entroo 2");
        });*/


        /*plugin.refresh = function(){
            plugin.$bootstrapTable.bootstrapTable('refresh', {'url': plugin.url});

            setTimeout(function(){
                resizeBootstrapTable(plugin.$bootstrapTable);
            }, 1000);
        }*/

    }

// Base64 String XML to HTML
    function XMLbase64ToHtml(xml, $element, utf8decode = true) {
        var xml         = utf8decode? decode_utf8(atob((xml))): atob(xml),
            replaceArgs = htmlEntities(xml);

        $element.html(replaceArgs);
    }

    function htmlEntities(str) {
       return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
    }

    function encode_utf8(s) {
      return unescape(encodeURIComponent(s));
    }

    function decode_utf8(s) {
      return decodeURIComponent(escape(s));
    }


// Treeview
    var glyph_opts = {
        map: {
            doc: "glyphicon glyphicon-file",
            docOpen: "glyphicon glyphicon-file",
            checkbox: "glyphicon glyphicon-unchecked",
            checkboxSelected: "glyphicon glyphicon-check",
            checkboxUnknown: "glyphicon glyphicon-share",
            dragHelper: "glyphicon glyphicon-play",
            dropMarker: "glyphicon glyphicon-arrow-right",
            error: "glyphicon glyphicon-warning-sign",
            expanderClosed: "glyphicon glyphicon-menu-right",
            expanderLazy: "glyphicon glyphicon-menu-right",  // glyphicon-plus-sign
            expanderOpen: "glyphicon glyphicon-menu-down",  // glyphicon-collapse-down
            folder: "glyphicon glyphicon-folder-close",
            folderOpen: "glyphicon glyphicon-folder-open",
            loading: "glyphicon glyphicon-refresh glyphicon-spin"
        }
    };


    var show_loader = function(){
        $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
    }

    var hide_loader = function(){
        $('#page_loader').remove();
    }

// BootstrapTable - Format
    var btf = {
        historial_bonos_proveedores:{
            token_pay : function (value) {
                return '<a href="javascript:void(0)" onclick = onOperacionPago("'+ value +'") >'+ value +'</a>';
                },
        },
        historial_bonos:{
            trans_token_pay : function (value) {
                return '<a href="javascript:void(0)" onclick = onOperacionPago("'+ value +'") >'+ value +'</a>';
            },
        },
        abonos_saldos:{
            trans_token_pay : function (value) {
                return '<a href="#" onclick = open_ticket("'+ value +'") >'+ value +'</a>';
            },
            money : function (value) {
                if(!isNaN(value)){
                    value = parseFloat(value);

                    return '$' + value.formatMoney(2, '.', ',');
                }
            },
            datetime : function (value) {
                return value && value != 0 ? new Date(value * 1000).format("Y-m-d h:i a"): '';
            },
            metodo_pago : function(value){
                if(value == 10) return '<span style="color:#177F75;font-weight: 900;">Efectivo</span>';

                if(value == 20) return '<span style="font-weight: 900;">Cheque</span>';

                if(value == 30) return '<span style="font-weight: 900;">Tranferencia</span>';

                if(value == 40) return '<span style="font-weight: 900;">Tarjeta de credito</span>';

                if(value == 50) return '<span style="font-weight: 900;">Tarjeta de debito</span>';

                if(value == 60) return '<span style="font-weight: 900;">Deposito</span>';

                if(value == 70) return '<span style="color:#D23641;font-weight: 900;">Credito</span>';

                if(value == 80) return '<span style="font-weight: 900;">Otro</span>';
            },
        },
        status : {
            opt_a : function (value) {
                if(value == 10) return '<span style="color:#177F75;font-weight: 900;">Habilitada</span>';

                if(value == 1) return '<span style="color:#FF8362;font-weight: 900;">Deshabilitada</span>';

                if(value == 0) return '<span style="color:#D23641;font-weight: 900;">Eliminada</span>';

                else return value;
            },
            opt_o : function (value) {
                if(value == 10) return '<span style="color:#177F75;font-weight: 900;">Habilitado</span>';

                if(value == 1) return '<span style="color:#FF8362;font-weight: 900;">Deshabilitado</span>';

                if(value == 0) return '<span style="color:#D23641;font-weight: 900;">Eliminado</span>';

                else return value;
            },
            opt_check : function (value) {
                if(value == 10) return '<i class="fa fa-check-square-o" aria-hidden="true"></i>';

                if(value == 1) return '<i class="fa fa-times" aria-hidden="true"></i>';

                if(value == 0) return '<i class="fa fa-times" aria-hidden="true"></i>';

                if(value == null) return '<i class="fa fa-times" aria-hidden="true"></i>';

                else return value;
            },
            carga : function (value) {
                if(value == 30) return '<span style="color:#D23641;font-weight: 900;">PROCESO</span>';

                if(value == 20) return '<span style="color:#FF8362;font-weight: 900;">RUTA</span>';

                if(value == 10) return '<span style="color:#177F75;font-weight: 900;">TERMINADO</span>';

                else return value;
            },
        },
        producto : {
            tipo : function(value){
                if(value == 10) return '<span style="font-weight: 900;">FRESCO</span>';

                if(value == 20) return '<span style="font-weight: 900;">CONGELADO</span>';
            },
            unidad : function(value){
                if(value == 10) return '<span style="font-weight: 900;">Piezas</span>';

                if(value == 20) return '<span style="font-weight: 900;">Kilos</span>';
            },
            inv : function(value){
                if(value == 10) return '<i class="fa fa-check-square-o" aria-hidden="true"></i>';

                if(value == 20) return '<i class="fa fa-times" aria-hidden="true"></i>';
            },
            validate : function (value) {

                if(value == 20) return '<span style="color:#FF8362;font-weight: 900;">SIN VALIDAR</span>';

                if(value == 10) return '<span style="color:#177F75;font-weight: 900;">VALIDADO</span>';

                else return value;
            },
        },
        operacion : {
            url_link_operacion : function(value){
                return (value!=null) ?'<a href="#" style="text-decoration: underline; font-size:14px; font-weight:600" onclick = url_open_link("'+ value +'") >'+ value +'</a>':"";
            }
        },
        credito : {
            tipo : function(value){
                if(value == 10) return '<span style="font-weight: 900;">TPV</span>';

                if(value == 20) return '<span style="font-weight: 900;">PROVEEDOR</span>';
            },
            status : function(value){
                if(value == 10) return '<span style="color:#FF8362;font-weight: 900;">VIGENTE</span>';

                if(value == 20) return '<span style="color:#D23641;font-weight: 900;">CANCELADO</span>';

                if(value == 30) return '<span style="color:#177F75;font-weight: 900;">PAGADO</span>';

                if(value == 40) return '<span style="color:#D23641;font-weight: 900;">POR PAGAR</span>';
            },
            vencido : function(value){
                return '<span style="color:#FF8362;font-weight: 900;">VENCIDO</span>';
            },
            
            title_money : function(value){
                if(!isNaN(value)){
                    value = parseFloat(value);
                    return '<p style="font-size:14px; font-weight:bold;" class="text-warning">$' + value.formatMoney(2, '.', ',')+'</p>';
                }
                return ;
            },
            url_link_pago : function(value){
                return (value!=null) ?'<a href="#" style="text-decoration: underline; font-size:10px; font-weight:600" onclick = onOperacionPago("'+ value +'") >'+ value +'</a>':"";
            }
        },
        cobroabono : {
            tipo : function(value){
                if(value == 10) return '<span style="font-weight: 900;">VENTA</span>';

                if(value == 20) return '<span style="font-weight: 900;">COMPRA</span>';

                if(value == 30) return '<span style="font-weight: 900;">CREDITO</span>';

                if(value == 40) return '<span style="font-weight: 900;">REEMBOLSO</span>';
            },
            metodo_pago : function(value){
                if(value == 10) return '<span style="color:#177F75;font-weight: 900;">Efectivo</span>';

                if(value == 20) return '<span style="font-weight: 900;">Cheque</span>';

                if(value == 30) return '<span style="font-weight: 900;">Tranferencia</span>';

                if(value == 40) return '<span style="font-weight: 900;">Tarjeta de credito</span>';

                if(value == 50) return '<span style="font-weight: 900;">Tarjeta de debito</span>';

                if(value == 60) return '<span style="font-weight: 900;">Deposito</span>';

                if(value == 70) return '<span style="color:#D23641;font-weight: 900;">Credito</span>';

                if(value == 80) return '<span style="font-weight: 900;">Otro</span>';
            },
        },
        sucursal : {
            tipo : function(value){
                if(value == 10) return '<span style="font-weight: 900;">ALMACEN</span>';

                if(value == 20) return '<span style="font-weight: 900;">SUCURSAL</span>';
            },
        },
        tranformacion : {
            tipo : function(value){
                if(value == 20) return '<span style="font-weight: 900;">TRANFORMACION A PRODUCTO</span>';

                if(value == 30) return '<span style="font-weight: 900;">TRANFORMACION MERMA</span>';

                if(value == 40) return '<span style="font-weight: 900;">TRANFORMACION VENDIDO</span>';
            },
            motivo : function(value){
                if(value == 20) return '<span style="font-weight: 900;">TRANSFORMAR</span>';

                if(value == 30) return '<span style="font-weight: 900;">MERMA</span>';

                if(value == 40) return '<span style="font-weight: 900;">CORTESIA</span>';
            },
        },
        tpv : {
            status : function(value){
                if(value == 10) return '<span style="font-weight: 900;">TERMINADO</span>';

                if(value == 20) return '<span style="font-weight: 900;">PRE-CAPTURA</span>';

                if(value == 30) return '<span style="font-weight: 900;">PRE-VENTA</span>';

                if(value == 40) return '<span style="font-weight: 900;">PROCESO</span>';

                if(value == 50) return '<span style="font-weight: 900;">PROCESO - VERIFICACION</span>';

                if(value == 60) return '<span style="font-weight: 900;">VERIFICADO</span>';

                if(value == 1) return '<span style="font-weight: 900;">CANCELADO</span>';
            },

            tipo : function(value){
                if(value == 10) return '<span style="font-weight: 900;">GENERAL</span>';

                if(value == 20) return '<span style="font-weight: 900;">MENUDEO</span>';

                if(value == 30) return '<span style="font-weight: 900;">MAYOREO</span>';
            },

        },

        inv : {
            tipo : function(value){
                if(value == 10) return '<span style="font-weight: 900;color:#177F75;">ENTRADA</span>';

                if(value == 20) return '<span style="font-weight: 900;color:#D23641;">SALIDA</span>';

                if(value == 30) return '<span style="font-weight: 900;color:#D23641;">DEVOLUCION</span>';
            },
            tipo_operacion : function(value){
                if(value == 10) return '<span style="font-weight: 900;color: #f8ac59">AJUSTE DE INVENTARIO PARCIAL</span>';

                if(value == 20) return '<span style="font-weight: 900;color: #1ab394">AJUSTE DE INVENTARIO COMPLETO</span>';
            },
            tipo_movimiento : function(value){
                if(value == 10) return '<span style="color:#177F75;font-weight: 900;">ENTRADA</span>';

                if(value == 20) return '<span style="color:#D23641;font-weight: 900;">SALIDA</span>';
            },
            operacion : function(value){
                if(value == 10) return '<span style="font-weight: 900;">VENTA</span>';

                if(value == 15) return '<span style="font-weight: 900;">VENTA - RUTA</span>';

                if(value == 20) return '<span style="font-weight: 900;">TRASPASO</span>';

                if(value == 30) return '<span style="font-weight: 900;">TRANFORMACIÓN</span>';

                if(value == 40) return '<span style="font-weight: 900;">REPARTO</span>';

                if(value == 50) return '<span style="font-weight: 900;">AJUSTE</span>';

                if(value == 60) return '<span style="font-weight: 900;">AJUSTE [PREVENTA]</span>';

            },
            operacion_cantidad : function(value, row){
                if (parseInt(row.motivo) == 10 )
                    return '<span style="color:#177F75;font-weight: 900;">&#43;'+ value +'</span>';


                if (parseInt(row.motivo) == 20)
                    return '<span style="color:#D23641;font-weight: 900;">&#45;'+ value +'</span>';

            },
            compra_status : function(value){
                if(value == 1) return '<span style="font-weight: 900;">CANCELADO</span>';

                if(value == 10) return '<span style="font-weight: 900;">PAGADA</span>';

                if(value == 20) return '<span style="font-weight: 900;">PROCESO</span>';

                if(value == 30) return '<span style="font-weight: 900;">POR PAGAR</span>';

                if(value == 40) return '<span style="font-weight: 900; color:#177F75;">TERMINADO</span>';
            },
            status : function(value){
                if(value == 10) return '<span style="font-weight: 900; color: green;">TERMINADO</span>';

                if(value == 20) return '<span style="font-weight: 900; color: orange;">PROCESO</span>';

                if(value == 30) return '<span style="font-weight: 900;">CANCELADO</span>';
            },
            solicitud : function(value){
                if(value == 10) return '<span style="font-weight: 900;">SOLICITUD</span>';

                if(value == 20) return '<span style="font-weight: 900;">PROCESO</span>';

                if(value == 30) return '<span style="font-weight: 900;">REVISIÓN</span>';

                if(value == 40) return '<span style="font-weight: 900;color:#177F75;">TERMINADO</span>';

                if(value == 1) return '<span style="font-weight: 900;">CANCELADO</span>';
            },

            incidencias : function(value){
                if(value == 10) return '<span style="font-weight: 900;">PROCESO</span>';

                if(value == 30) return '<span style="font-weight: 900;color:#177F75;">TERMINADO</span>';

            },
        },
        color : {
            green : function( value ){
                return '<span style="color:#177F75;font-weight: 900;">' + value + '</span>';
            },
            bold : function( value ){
                return '<span style="font-weight: bold;">' + value + '</span>';
            }
        },
        time : {
            date : function (value) {
                return value && value != 0 ? new Date(value * 1000).format("Y-m-d"): '';
            },
            datetime : function (value) {
                return value && value != 0 ? new Date(value * 1000).format("Y-m-d h:i a"): '';
            },
            /*
            millis : function (value) {
                return value? ((Math.round(value / 10)) / 100) + ' s'  : '';
            },
            date_dia : function (value) {
                return value? new Date(value * 1000).format("d"): '';
            },
            date_mes : function (value) {
                value = parseInt(value? new Date(value * 1000).format("m"): '');

                switch(value){
                    case 1:  return 'Enero';
                    case 2:  return 'Febrero';
                    case 3:  return 'Marzo';
                    case 4:  return 'Abril';
                    case 5:  return 'Mayo';
                    case 6:  return 'Junio';
                    case 7:  return 'Julio';
                    case 8:  return 'Agosto';
                    case 9:  return 'Septiembre';
                    case 10: return 'Octubre';
                    case 11: return 'Noviembre';
                    case 12: return 'Diciembre';
                }
            },
            date_ano : function (value) {
                return value? new Date(value * 1000).format("Y"): '';
            },
            datetime2 : function (value) {
                return value? new Date(value * 1000).format("h:i a / Y-m-d"): '';
            },
            time : function (value) {
                return value? new Date(value * 1000).format("h:i a"): '';
            },
            segToTime : function (value) {
                if(value == null)
                    return '';

                var horas, minutos, segundos, string_time;

                if(value >= 3600){
                    horas = parseInt(value / 3600);
                    value = value - (horas * 3600);

                    string_time = horas + "h";
                }

                if(value >= 60){
                    minutos = parseInt(value / 60);
                    value   = value - (minutos * 60);

                    string_time = (string_time? string_time + ' ': '') + minutos + "m";
                }

                if(!horas && !minutos){
                    string_time = value + "s";
                }

                return string_time;
            },
            dias : function (value) {
                return value + (value > 1 || value == 0? ' días': ' día');
            },
            */

            //'updated_sucursal_id',


            minutos : function (value) {
                return value? value + ' Min': '';
            },
            dia_semana : function (value) {
                value = parseInt(value);

                switch(value){
                    case 1:  return 'Lunes';
                    case 2:  return 'Martes';
                    case 3:  return 'Miercoles';
                    case 4:  return 'Jueves';
                    case 5:  return 'Viernes';
                    case 6:  return 'Sabado';
                    case 7:  return 'Domingo';
                }
            },
            mes : function (value) {
                value = parseInt(value);

                switch(value){
                    case 1:  return 'Enero';
                    case 2:  return 'Febrero';
                    case 3:  return 'Marzo';
                    case 4:  return 'Abril';
                    case 5:  return 'Mayo';
                    case 6:  return 'Junio';
                    case 7:  return 'Julio';
                    case 8:  return 'Agosto';
                    case 9:  return 'Septiembre';
                    case 10: return 'Octubre';
                    case 11: return 'Noviembre';
                    case 12: return 'Diciembre';
                }
            },
            timezone : function (value) {
                switch (parseInt(value)) {
                    case -5: return "Tiempo del Sureste: UTC -5";
                    case -6: return "Tiempo del Centro: UTC –6 (UTC –5 en verano)";
                    case -7: return "Tiempo del Pacífico: UTC–7 (UTC–6 en verano)";
                    case -8: return "Tiempo del Noroeste: UTC–8 (UTC–7 en verano)";
                }
            },
        },

        gastos:{
            datetime : function (value) {
                return value && value != 0 ? new Date(value * 1000).format("Y-m-d h:i a"): '';
            },
            tipo : function (value) {
                if(value == 10) return '<span style="font-weight: 900;">SOLICITUD</span>';
                if(value == 20) return '<span style="font-weight: 900;">CREDITO</span>';
                if(value == 30) return '<span style="font-weight: 900;">RETIRO / EFECTIVO</span>';
                if(value == 40) return '<span style="font-weight: 900;">GASTO</span>';
                if(value == 50) return '<span style="font-weight: 900;">CANCELACION VENTA</span>';
            },
            status : function (value) {
                if(value == 10) return '<span style="color:#57dc71;font-weight: 900;">SUCCESS</span>';
                if(value == 1) return '<span style="color:#CC0000;font-weight: 900;">CANCEL</span>';
            },
            money : function (value) {
                if(!isNaN(value)){
                    value = parseFloat(value);

                    return '$' + value.formatMoney(2, '.', ',');
                }
            },
        },
        conta : {
            token_pay : function (value) {
                return (value!=null) ?'<a href="#" onclick = direccionar("'+ value +'") >'+ value +'</a>':"";
            },
            money_color : function (value) {
                if(!isNaN(value)){
                    value = parseFloat(value);

                    return value > 0 ? '<span style="color: #e7b805;font-weight: 800;text-decoration: underline;">$' + value.formatMoney(2, '.', ',')+ '</span>' : '$' + value.formatMoney(2, '.', ',') ;
                }
            },
            money_underline : function (value) {
                if(!isNaN(value)){
                    value = parseFloat(value);

                    return '<span style="font-weight: 900;text-decoration:underline;color:#000">'+ '$' + value.formatMoney(2, '.', ',') +'</span>';
                }
            },
            money : function (value) {
                if(!isNaN(value)){
                    value = parseFloat(value);

                    return '$' + value.formatMoney(2, '.', ',');
                }
            },
            moneyDanger : function (value) {
                if(!isNaN(value)){
                    value = parseFloat(value);

                    return '<span style="color:#D23641;font-weight: 900;"> $' + value.formatMoney(2, '.', ',') + '</span>';
                }
            },
            porcentaje : function (value) {
                return value > 0? (value * 1) + ' %': '';
            },
            number : function (value) {
                return value? (value * 1): 0;
            },
            moneyDeuda : function (value) {
                if(!isNaN(value)){
                    value = parseFloat(value);

                    return '<span class="text-danger">$' + value.formatMoney(2, '.', ',') + "</span>";
                }
            },
        },
        boolean : {
            sino : function (value) {
                if(value == 10) return '<span style="font-weight: 900; color: #ffd44d">SI</span>';
                if(value == 20) return '<span style="font-weight: 900; color: #243747">NO</span>';
            },
        },
        ui : {
            /*
            barcode : function (value) {
                return value.substr(0, 1) + '****' + value.substr(5, 2);
            },
            tel_ext : function(value, row) {
                return '<a href="tel:' + row.tel + '" class="text-primary">' + row.tel + '</a>' + (row.tel_ext? ' Ext. ' + row.tel_ext: '');
            },
            direccion : function(value, row) {
                return row.direccion + (row.num_ext? ' No. ' + row.num_ext: '') + (row.num_int? ' Int. ' + row.num_int: '') + ', ' + row.colonia + (row.cp? ', C.P. ' + row.cp: '');
            },
            almacen_principal : function(value, row) {
                return row.almacen_principal? row.almacen_principal + ' [' + row.almacen_principal_uid + ']': '';
            },
            almacen_destino : function(value, row) {
                return row.almacen_destino? row.almacen_destino + ' [' + row.almacen_destino_uid + ']': '';
            },
            */
            pres_u_de_uso : function (value, row) {
                if(row.presentacion != null || row.unidades != null){
                    var presentacion  = row.unidades <= 1? row.presentacion: row.presentacion_plural,
                        unidad_de_uso = row.unidades <= 1? row.unidad_de_uso: row.unidad_de_uso_plural,
                        pres_u_de_uso = presentacion != null? presentacion: '';

                    pres_u_de_uso += row.presentacion != null || row.unidades != null? ' ': '';
                    pres_u_de_uso += unidad_de_uso? (row.unidades * 1) + ' ' + unidad_de_uso: '';

                    return pres_u_de_uso;
                }
            },
            checkbox : function (value) {
                return value > 0? '<span style="display:none">Si</span><i class="fa fa-check text-primary"></i>': '';
            },
            rating : function (value) {
                return html_rating(parseInt(value));
            },
            edad : function (value) {
                return value? value + ' años': '';
            },
            veces : function (value) {
                if(value == 0)
                    return 'Ninguna';

                if(value == 1)
                    return '1 vez';

                return value + ' veces';
            },
            hace_dias : function (value) {
                if(value){
                    if(value == 0)
                        return 'Hoy';

                    return 'hace ' + value + (value > 1? ' días': ' día');
                }
            },
            tel : function(value) {
                return '<a href="tel:' + value + '" class="text-primary">' + value + '</a>';
            },
            mailto : function(value) {
                return value != null? '<a href="mailto:' + value + '" class="text-primary">' + value + '</a>': '';
            },
        },
        user : {
            /*
            full_name : function(value, row) {
                return row.nombre + ' ' + row.apellidos;
            },
            user_admin : function(value, row) {
                return row.admin_id? row.admin + ' [' + row.admin_id + ']': '';
            },
            asignado : function(value, row) {
                return row.asignado_id? row.asignado + ' [' + row.asignado_id + ']': '';
            },
            asignado_a : function(value, row) {
                return row.asignado_a + (row.asignado_a_id? ' [' + row.asignado_a_id + ']': '');
            },
            encargado : function(value, row) {
                return row.encargado_id? row.encargado + ' [' + row.encargado_id + ']': '';
            },
            user_nombre : function(value, row) {
                return row.user_id? row.user_nombre + ' [' + row.user_id + ']': '';
            },
            user_destino_uid : function(value, row) {
                return row.user_destino_uid? row.user_destino_name + ' [' + row.user_destino_uid + ']': '';
            },
            cliente : function(value, row) {
                return row.cliente_id? row.cliente + ' [' + row.cliente_id + ']': '';
            },
            cliente_nombre : function(value, row) {
                return row.cliente_id? row.cliente_nombre + ' [' + row.cliente_id + ']': '';
            },
            created_by_uid : function(value, row) {
                return row.created_by_uid? row.created_by_user + ' [' + row.created_by_uid + ']': '';
            },
            */
            user_name : function(value, row) {
                return row.user_id? row.user_name + ' [' + row.user_id + ']': '';
            },
            created_by : function(value, row) {
                return row.created_by? row.created_by_user + ' [' + row.created_by + ']': '';
            },
            updated_by : function(value, row) {
                return row.updated_by? row.updated_by_user + ' [' + row.updated_by + ']': '';
            },
            sexo : function (value) {
                switch (parseInt(value)) {
                    case 10: return "Hombre";
                    case 20: return "Mujer";
                }
            },
        },
        trc : function (value) {
            switch (value) {
                case '1':  return 'Compra';
                case '2':  return 'Compra cancelada';
                case '3':  return 'Compra eliminada';
                case '4':  return 'Venta';
                case '5':  return 'Venta cancelada';
                case '6':  return 'Venta eliminada';
                case '7':  return 'Ajuste manual';
                case '8':  return 'Ajuste manual cancelado';
                case '9':  return 'Ajuste manual eliminado';
                case '10': return 'Traspaso';
                case '11': return 'Traspaso cancelado';
                case '12': return 'Traspaso eliminado';
                default:   return value;
            }
        },
    }

