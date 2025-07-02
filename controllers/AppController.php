<?php
namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

/**
 * AppController extends Controller and implements the behaviors() method
 * where you can specify the access control ( AC filter + RBAC ) for your controllers and their actions.
 */
class AppController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     * Here we use RBAC in combination with AccessControl filter.
     *
     * @return array
     */
    public function behaviors()
    {
         return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    /*************************
                    * Site
                    *************************/
                            [
                                'controllers' => ['site'],
                                'actions' => ['index', 'acerca-de', 'permisos', 'error'],
                                'allow' => true,
                            ],
                        
                            [
                                'controllers' => ['inventario/operacion-entrada-incidencia'],
                                'actions' => ['index','operacion-entrada-incidencia-json-btt','get-operacion-incidencia','post-operacion-omitir','post-operacion-guardar'],
                                'allow' => true,
                                'roles' => ['@'],
                            ],
                    /*************************
                    * Admin
                    *************************/
                        // Dashboard
                            [
                                'controllers' => ['admin/dashboard'],
                                'actions' => ['index'],
                                'allow' => true,
                                'roles' => ['dashboardAdmin'],
                            ],

                            [
                                'controllers' => ['admin/user'],
                                'actions' => ['login-user'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],
                        // Dashboard
                            [
                                'controllers' => ['admin/user'],
                                'actions' => ['reporte-venta-producto-ajax','reporte-venta-cliente-ajax'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],


                        // Usuarios
                            [
                                'controllers' => ['admin/user'],
                                'actions' => ['login', 'signup', 'activate-account', 'request-password-reset', 'reset-password'],
                                'allow' => true,
                                'roles' => ['?'],
                            ],
                            [
                                'controllers' => ['admin/user'],
                                'actions' => ['logout', 'change-password', "mi-perfil",'search-folio'],
                                'allow' => true,
                                'roles' => ['@'],
                            ],
                            [
                                'controllers' => ['admin/search'],
                                'actions' => ['load-script'],
                                'allow' => true,
                                'roles' => ['@'],
                            ],
                            [
                                'controllers' => ['admin/user'],
                                'actions' => ['index', 'users-json-btt', 'view', 'historial-cambios','user-ajax','enable-acceso-app','desabled-acceso-app'],
                                'allow' => true,
                                'roles' => ['userView'],
                            ],
                            [
                                'controllers' => ['admin/user'],
                                'actions' => ['create'],
                                'allow' => true,
                                'roles' => ['userCreate'],
                            ],
                            [
                                'controllers' => ['admin/user'],
                                'actions' => ['update'],
                                'allow' => true,
                                'roles' => ['userUpdate'],
                            ],
                            [
                                'controllers' => ['admin/user'],
                                'actions' => ['delete'],
                                'allow' => true,
                                'roles' => ['userDelete'],
                            ],
                        // Perfiles
                            [
                                'controllers' => ['admin/perfil'],
                                'actions' => ['index','perfiles-json-btt', 'view'],
                                'allow' => true,
                                'roles' => ['perfilUserView'],
                            ],
                            [
                                'controllers' => ['admin/perfil'],
                                'actions' => ['create'],
                                'allow' => true,
                                'roles' => ['perfilUserCreate'],
                            ],
                            [
                                'controllers' => ['admin/perfil'],
                                'actions' => ['update'],
                                'allow' => true,
                                'roles' => ['perfilUserUpdate'],
                            ],
                            [
                                'controllers' => ['admin/perfil'],
                                'actions' => ['delete'],
                                'allow' => true,
                                'roles' => ['perfilUserDelete'],
                            ],
                        // Listas desplegables
                            [
                                'controllers' => ['admin/listas-desplegables'],
                                'actions'     => ['index', 'listas', 'items', 'tabla'],
                                'allow'       => true,
                                'roles'       => ['listaDesplegableView'],
                            ],
                            [
                                'controllers' => ['admin/listas-desplegables'],
                                'actions'     => ['create-ajax'],
                                'allow'       => true,
                                'roles'       => ['listaDesplegableCreate'],
                            ],
                            [
                                'controllers' => ['admin/listas-desplegables'],
                                'actions'     => ['update-ajax', 'sort-ajax'],
                                'allow'       => true,
                                'roles'       => ['listaDesplegableUpdate'],
                            ],
                            [
                                'controllers' => ['admin/listas-desplegables'],
                                'actions'     => ['delete-ajax'],
                                'allow'       => true,
                                'roles'       => ['listaDesplegableDelete'],
                            ],
                        // Configuraciones
                            [
                                'controllers' => ['admin/setting'],
                                'actions' => ['parametros', 'parametos-json-btt'],
                                'allow' => true,
                                'roles' => ['parametrosView'],
                            ],
                            [
                                'controllers' => ['admin/setting'],
                                'actions' => ['parametros-update'],
                                'allow' => true,
                                'roles' => ['parametrosUpdate'],
                            ],
                        // Configuraciones del sitio
                            [
                                'controllers' => ['admin/configuracion'],
                                'actions' => ['configuracion-update'],
                                'allow' => true,
                                'roles' => ['configuracionSitio'],
                            ],
                            [
                                'controllers' => ['admin/configuracion'],
                                'actions' => ['precio-libra-ajax'],
                                'allow' => true,
                                'roles' => ['@'],
                            ],

                        // Historial de acceso
                            [
                                'controllers' => ['admin/historial-de-acceso'],
                                'actions' => ['index', 'historial-de-accesos-json-btt'],
                                'allow' => true,
                                'roles' => ['historialAccesosUser'],
                            ],

                            [
                                'controllers' => ['admin/version'],
                                'actions' => ['list'],
                                'allow' => true,
                                'roles' => ['theCreator'],
                            ],


                    /*************************
                    * Catalgos
                    *************************/
                        // Clientes
                            [
                                'controllers' => ['crm/cliente'],
                                'actions' => ['index', 'clientes-json-btt', 'view', 'historial-cambios','cliente-ajax','get-history-operacion','ventas-cliente-json-btt','get-token-ventas','get-history-pago','pagos-detail-json-btt'],
                                'allow' => true,
                                'roles' => ['clienteView'],
                            ],
                            [
                                'controllers' => ['crm/cliente'],
                                'actions' => ['create'],
                                'allow' => true,
                                'roles' => ['clienteCreate'],
                            ],
                            [
                                'controllers' => ['crm/cliente'],
                                'actions' => ['update'],
                                'allow' => true,
                                'roles' => ['clienteUpdate'],
                            ],
                            [
                                'controllers' => ['crm/cliente'],
                                'actions' => ['historico-ventas-json-btt'],
                                'allow' => true,
                                'roles' => ['historicoCliente'],
                            ],
                            [
                                'controllers' => ['crm/cliente'],
                                'actions' => ['delete'],
                                'allow' => true,
                                'roles' => ['clienteDelete'],
                            ],


                        // Proveedor

                            [
                                'controllers' => ['crm/proveedor'],
                                'actions' => ['index', 'proveedores-json-btt', 'view', 'historial-cambios','get-history-operacion','compras-proveedor-json-btt','get-history-pago','pagos-detail-json-btt'],
                                'allow' => true,
                                'roles' => ['proveedorView'],
                            ],
                            [
                                'controllers' => ['crm/proveedor'],
                                'actions' => ['create'],
                                'allow' => true,
                                'roles' => ['proveedorCreate'],
                            ],
                            [
                                'controllers' => ['crm/proveedor'],
                                'actions' => ['update','get-direccion-ajax'],
                                'allow' => true,
                                'roles' => ['proveedorUpdate'],
                            ],
                            [
                                'controllers' => ['crm/proveedor'],
                                'actions' => ['delete'],
                                'allow' => true,
                                'roles' => ['proveedorDelete'],
                            ],


                        // Productos

                            [
                                'controllers' => ['productos/producto'],
                                'actions' => ['index', 'productos-json-btt', 'view','validar-producto','get-promedio-compra'],
                                'allow' => true,
                                'roles' => ['productoView'],
                            ],
                            [
                                'controllers' => ['productos/producto'],
                                'actions' => ['create','producto-ajax'],
                                'allow' => true,
                                'roles' => ['productoCreate'],
                            ],
                            [
                                'controllers' => ['productos/producto'],
                                'actions' => ['update','producto-ajax'],
                                'allow' => true,
                                'roles' => ['productoUpdate'],
                            ],
                            [
                                'controllers' => ['productos/producto'],
                                'actions' => ['delete'],
                                'allow' => true,
                                'roles' => ['productoDelete'],
                            ],

                        // Sucursal

                            [
                                'controllers' => ['sucursales/sucursal'],
                                'actions' => ['index', 'sucursales-json-btt', 'view', 'historial-cambios','imprimir-qr'],
                                'allow' => true,
                                'roles' => ['sucursalView'],
                            ],
                            [
                                'controllers' => ['sucursales/sucursal'],
                                'actions' => ['create'],
                                'allow' => true,
                                'roles' => ['sucursalCreate'],
                            ],
                            [
                                'controllers' => ['sucursales/sucursal'],
                                'actions' => ['update'],
                                'allow' => true,
                                'roles' => ['sucursalUpdate'],
                            ],
                            [
                                'controllers' => ['sucursales/sucursal'],
                                'actions' => ['delete'],
                                'allow' => true,
                                'roles' => ['sucursalDelete'],
                            ],
                    /*************************
                    * Credito
                    *************************/

                        //CREDITO
                            [
                                'controllers' => ['creditos/credito'],
                                'actions' => ['index', 'creditos-json-btt', 'view', 'historial-cambios','get-history-operacion','producto-ajax','get-producto','get-token-ventas','post-gasto-create','get-history-pago','get-compra-venta','pagos-detail-json-btt'],
                                'allow' => true,
                                'roles' => ['creditoView'],
                            ],
                            [
                                'controllers' => ['creditos/credito'],
                                'actions' => ['create'],
                                'allow' => true,
                                'roles' => ['creditoCreate'],
                            ],
                            [
                                'controllers' => ['creditos/credito'],
                                'actions' => ['update'],
                                'allow' => true,
                                'roles' => ['creditoUpdate'],
                            ],
                            [
                                'controllers' => ['creditos/credito'],
                                'actions' => ['cancel','delete-abono'],
                                'allow' => true,
                                'roles' => ['creditoCancel'],
                            ],

                        // COBRO A CLIENTE
                            [
                                'controllers' => ['creditos/credito'],
                                'actions' => ['register-pay-cliente','cliente-ajax','get-credito-cliente','post-credito-create','imprimir-credito','get-history-operacion-cliente','get-producto','get-history-operacion-cliente-global','get-saldos-ruta'],
                                'allow' => true,
                                'roles' => ['creditoPayCliente'],
                            ],

                            [
                                'controllers' => ['creditos/credito'],
                                'actions' => ['register-pay-proveedor','proveedor-ajax','get-credito-proveedor','post-credito-create','imprimir-credito-proveedor','get-history-operacion-proveedor','get-history-operacion-proveedor-global','get-producto','get-compra-venta'],
                                'allow' => true,
                                'roles' => ['creditoPayProveedor'],
                            ],


                    /*************************
                    * Caja
                    *************************/

                        // CAJA
                            [
                                'controllers' => ['caja/apertura-y-cierre'],
                                'actions' => ['index','get-compra-venta' ,'apertura-cierre-json-btt', 'view','imprimir-recibo', 'imprimir-reporte','imprimir-credito','apertura-cierre-operacion-detail-json-btt','apertura-cierre-operacion-detail-otras-json-btt'],
                                'allow' => true,
                                'roles' => ['cajaCierreApertura'],
                            ],
                            [
                                'controllers' => ['caja/apertura-y-cierre'],
                                'actions' => ['arqueo'],
                                'allow' => true,
                                'roles' => ['cajaArqueo'],
                            ],
                            [
                                'controllers' => ['caja/cobro-abono'],
                                'actions' => ['index','cobro-abonos-json-btt'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],

                    /*************************
                    * TPV
                    *************************/

                        // PRE CAPTURA
                            [
                                'controllers' => ['tpv/pre-captura'],
                                'actions' => ['index', 'ventas-json-btt','view','imprimir-acuse-pdf','save-conversion-producto','producto-ajax'],
                                'allow' => true,
                                'roles' => ['precapturaView'],
                            ],
                            [
                                'controllers' => ['tpv/pre-captura'],
                                'actions' => ['create','search-producto-id','producto-ajax','venta-detalle-ajax','update-precio'],
                                'allow' => true,
                                'roles' => ['precapturaCreate'],
                            ],
                            [
                                'controllers' => ['tpv/pre-captura'],
                                'actions' => ['producto-ajax'],
                                'allow' => true,
                                'roles' => ['productoView'],
                            ],
                            [
                                'controllers' => ['tpv/pre-captura'],
                                'actions' => ['update','search-producto','search-producto-nombre','update-precio'],
                                'allow' => true,
                                'roles' => ['precapturaUpdate'],
                            ],
                            [
                                'controllers' => ['tpv/pre-captura'],
                                'actions' => ['cancel'],
                                'allow' => true,
                                'roles' => ['precapturaCancel'],
                            ],

                        // PRE VENTA [APP]

                            [
                                'controllers' => ['tpv/pre-venta'],
                                'actions' => ['index', 'ventas-json-btt','view'],
                                'allow' => true,
                                'roles' => ['precapturaView'],
                            ],
                            [
                                'controllers' => ['tpv/pre-venta'],
                                'actions' => ['index-comanda-autorizacion','update-preventa','get-detail-preventa-almacen','post-autorizar-preventa','producto-ajax','get-valida-inventario-almacen','get-comanda-abierta'],
                                'allow' => true,
                                'roles' => ['verificacionPreventaAccess'],
                            ],
                            [
                                'controllers' => ['tpv/pre-venta'],
                                'actions' => ['cancel'],
                                'allow' => true,
                                'roles' => ['precapturaCancel'],
                            ],

                        // VENTA

                            [
                                'controllers' => ['tpv/venta'],
                                'actions' => ['index', 'ventas-json-btt', 'view','imprimir-ticket','imprimir-ticket-retiro','imprimir-credito','get-token-ventas','ruta-view','imprimir-acuse-pdf','cliente-ajax','venta-info','imprimir-ticket-entrega'],
                                'allow' => true,
                                'roles' => ['ventaView'],
                            ],

                            [
                                'controllers' => ['tpv/venta'],
                                'actions' => ['pre-venta','search-producto-id','producto-ajax','get-pre-venta','apertura-caja-create','get-cierre-caja-monto','get-arqueo-caja','apertura-caja-update','get-credito-cliente','post-credito-create','post-credito-create','get-devolucion-valid','retiro-efectivo-caja','registro-gasto-caja','get-cuentas-abierta','producto-ajax','get-producto'],
                                'allow' => true,
                                'roles' => ['ventaCreate'],
                            ],

                            [
                                'controllers' => ['tpv/venta'],
                                'actions' => ['update','search-producto','search-producto-nombre'],
                                'allow' => true,
                                'roles' => ['ventaUpdate'],
                            ],

                            [
                                'controllers' => ['tpv/venta'],
                                'actions' => ['cancel-venta','get-notas-multiple','post-cancelacion-venta','update-venta-ruta'],
                                'allow' => true,
                                'roles' => ['ventaCancel'],
                            ],

                    /*************************
                    * Invetario
                    *************************/
                        // Arqueo de inventario

                            [
                                'controllers' => ['inventario/operacion'],
                                'actions' => ['index','ajuste-inventario-json-btt','create','producto-ajax','view','update','operacion-detalle-ajax','ajuste-inventario-json-btt','ajustar-inventario','get-producto-ajustar-inventario','post-producto-ajuste-inventario','set-inventario-operador','cancel'],
                                'allow' => true,
                                'roles' => ['operacionAjusteInventario'],
                            ],
                            [
                                'controllers' => ['inventario/operacion'],
                                'actions' => ['view-ajuste','imprimir-acuse-pdf','load-inventario','get-producto-inventario','post-producto-inventario','send-inventario','producto-ajax','imprimir-acuse-operacion'],
                                'allow' => true,
                                'roles' => ['@'],
                            ],

                        // Arqueo de inventario

                            [
                                'controllers' => ['inventario/arqueo-inventario'],
                                'actions' => ['index', 'arqueo-inventario-json-btt', 'view','save-inventario-producto','arqueo-inventario-productos','save-inventario-all','get-operacion-detail','historial-movimientos-json-btt','redirect-operacion-view','imprimir-operacion'],
                                'allow' => true,
                                'roles' => ['inventario'],
                            ],


                        // BALANCE

                            [
                                'controllers' => ['inventario/balance-inventario'],
                                'actions' => ['index', 'balance-inventario-json-btt'],
                                'allow' => true,
                                'roles' => ['inventarioBalance'],
                            ],


                        //Tranformación

                            [
                                'controllers' => ['inventario/devolucion'],
                                'actions' => ['tranformacion','get-inventario-sucursal','create-transformacion','tranformacion-json-btt','tranformacion-merma-json-btt','tranformacion-view','get-producto'],
                                'allow' => true,
                                'roles' => ['tranformacion'],
                            ],

                        // Devolucion


                            [
                                'controllers' => ['inventario/devolucion'],
                                'actions' => ['index', 'devoluciones-json-btt', 'view','imprimir-etiqueta'],
                                'allow' => true,
                                'roles' => ['devolucionView'],
                            ],

                            [
                                'controllers' => ['inventario/devolucion'],
                                'actions' => ['create','get-venta'],
                                'allow' => true,
                                'roles' => ['devolucionCreate'],
                            ],

                    /*************************
                    * Operaciones
                    *************************/
                        // Entrada y Salidas

                            [
                                'controllers' => ['inventario/entradas-salidas'],
                                'actions' => ['index', 'entradas-salidas-json-btt', 'view','imprimir-etiqueta','imprimir-reporte','get-operacion-detail'],
                                'allow' => true,
                                'roles' => ['entradasalidaView'],
                            ],
                            [
                                'controllers' => ['inventario/entradas-salidas'],
                                'actions' => ['create','search-producto','get-compra','get-abastecimiento-disponible','get-abastecimiento'],
                                'allow' => true,
                                'roles' => ['entradasalidaCreate'],
                            ],
                            [
                                'controllers' => ['inventario/entradas-salidas'],
                                'actions' => ['cancel'],
                                'allow' => true,
                                'roles' => ['entradasalidaCancel'],
                            ],


                    /*************************
                    * Compras
                    *************************/
                            [
                                'controllers' => ['compras/compra'],
                                'actions' => ['index', 'compras-json-btt','view','save-confirmacion','lista-compra-tienda','lista-compra-cedis','view-modal'],
                                'allow' => true,
                                'roles' => ['compraView'],
                            ],
                            [
                                'controllers' => ['compras/compra'],
                                'actions' => ['cancel'],
                                'allow' => true,
                                'roles' => ['compraCancel'],
                            ],


                     /*************************
                    * Reporte
                    *************************/
                        // Entrada
                            [
                                'controllers' => ['reportes/reporte-entrada'],
                                'actions' => ['index', 'entradas-json-btt','view'],
                                'allow' => true,
                                'roles' => ['reporteEntrada'],
                            ],

                        // Abastecimiento
                            [
                                'controllers' => ['reportes/reporte-abastecimiento'],
                                'actions' => ['index', 'abastecimiento-json-btt','view'],
                                'allow' => true,
                                'roles' => ['reporteAbastecimiento'],
                            ],

                        // Surtir
                            [
                                'controllers' => ['reportes/reporte-surtir'],
                                'actions' => ['index', 'surtir-json-btt','view'],
                                'allow' => true,
                                'roles' => ['reporteSurtio'],
                            ],

                        // Producto
                            [
                                'controllers' => ['reportes/reporte-producto'],
                                'actions' => ['index', 'productos-json-btt'],
                                'allow' => true,
                                'roles' => ['reporteProducto'],
                            ],

                        // Carga Unidad
                            [
                                'controllers' => ['reportes/reporte-carga-unidad'],
                                'actions' => ['index', 'carga-unidad-json-btt'],
                                'allow' => true,
                                'roles' => ['reporteCargaUnidad'],
                            ],

                        // Venta - Producto
                            [
                                'controllers' => ['reportes/reporte-venta'],
                                'actions' => ['producto','venta-producto-json-btt','reporte-venta-producto-ajax'],
                                'allow' => true,
                                'roles' => ['reporteVentaProducto'],
                            ],

                        // Venta - Cliente
                            [
                                'controllers' => ['reportes/reporte-cliente-venta'],
                                'actions' => ['cliente','venta-cliente-json-btt','reporte-venta-cliente-ajax'],
                                'allow' => true,
                                'roles' => ['reporteVentaCliente'],
                            ],

                        //  Cliente - saldo
                            [
                                'controllers' => ['reportes/reporte-cliente-saldo'],
                                'actions' => ['index','cliente-saldo-json-btt','reporte-cliente-saldo-ajax'],
                                'allow' => true,
                                'roles' => ['reporteVentaClienteSaldo'],
                            ],

                        //  Proveedor - saldo
                            [
                                'controllers' => ['reportes/reporte-proveedor-saldo'],
                                'actions' => ['index','proveedor-saldo-json-btt','reporte-proveedor-saldo-ajax'],
                                'allow' => true,
                                'roles' => ['reporteVentaProveedorSaldo'],
                            ],


                        //  REPORTE POR RUTA
                            [
                                'controllers' => ['reportes/reporte-centro-negocio'],
                                'actions' => ['index','reporte-centro-negocio-ajax'],
                                'allow' => true,
                                'roles' => ['reporteCentroNegocio'],
                            ],

                        //  REPORTE POR GASTOS
                            [
                                'controllers' => ['reportes/reporte-gastos'],
                                'actions' => ['index','reporte-gastos-ajax','gasto-json-btt'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],
                    /*************************
                    * Almacen
                    *************************/
                            [
                                'controllers' => ['almacenes/almacen'],
                                'actions' => ['index', 'sucursales-json-btt', 'view', 'historial-cambios'],
                                'allow' => true,
                                'roles' => ['almacenView'],
                            ],
                            [
                                'controllers' => ['almacenes/almacen'],
                                'actions' => ['create'],
                                'allow' => true,
                                'roles' => ['almacenCreate'],
                            ],
                            [
                                'controllers' => ['almacenes/almacen'],
                                'actions' => ['update'],
                                'allow' => true,
                                'roles' => ['almacenUpdate'],
                            ],
                            [
                                'controllers' => ['almacenes/almacen'],
                                'actions' => ['delete'],
                                'allow' => true,
                                'roles' => ['almacenDelete'],
                            ],

                    /*************************
                    * Logistica
                    *************************/
                            [
                                'controllers' => ['logistica/ruta'],
                                'actions' => ['index', 'ruta-json-btt', 'view','imprimir-acuse-pdf','imprimir-saldo-pdf','download-embarque-pdf','get-precaptura-sucursal','enviar-ruta-traspaso','ruta-reporte-inventario','producto-ajax','save-ajuste-ruta','imprimir-pagare-pdf','imprimir-cuenta-pdf','habilitar-reparto','add-reparto','get-precapturas-inventario','update-preventa','remove-preventa','abrir-reparto','enviar-reparto','get-pedido','download-pedido-pdf','get-producto','get-metodo-pago-venta','get-liquidacion-venta','get-liquidacion-cobro','post-liquidacion-cobro'],
                                'allow' => true,
                                'roles' => ['repartoAccess'],
                            ],

                            [
                                'controllers' => ['logistica/ruta'],
                                'actions' => ['create','get-precaptura-sucursal','enviar-ruta-traspaso'],
                                'allow' => true,
                                'roles' => ['repartoAccess'],
                            ],

                            [
                                'controllers' => ['logistica/ruta'],
                                'actions' => ['delete'],
                                'allow' => true,
                                'roles' => ['repartoAccess'],
                            ],
                     /*************************
                    * CONTABILIDAD - CATALOGO
                    *************************/
                            [
                                'controllers' => ['contabilidad/claves'],
                                'actions' => ['index','catalogos-conta-json-btt','subcuenta','add-subcuenta','view','create','update','delete'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],
                            [
                                'controllers' => ['contabilidad/transacciones'],
                                'actions' => ['index','transacciones-conta-json-btt','view','process_data.php','cuentas-ajax','asientos-contables','detail','update'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],
                            [
                                'controllers' => ['contabilidad/polizas'],
                                'actions' => ['index','verificacion-polizas-json-btt','polizas-json-btt','create','view','new-manual','asientos-contables', 'verificacion-corte-poliza','get-polizas-corte','post-create-verificacion','verificacion-view','imprimir-reporte'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],
                            [
                                'controllers' => ['contabilidad/estados'],
                                'actions' => ['index','presupuestos','resultados', 'edosfinancieros','edosfinancieros-pdf', 'imprimir-balanza'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],

                     //*end Contabilidad
                            [
                                'controllers' => ['administracion/caja-catalogo'],
                                'actions' => ['create'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],
                            [
                                'controllers' => ['administracion/caja-catalogo'],
                                'actions' => ['update'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],
                            [
                                'controllers' => ['administracion/caja-catalogo'],
                                'actions' => ['cancel'],
                                'allow' => true,
                                'roles' => ['admin'],
                            ],





                ], // rules
            ], // access
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout'      => ['post'],
                    'create-ajax' => ['post'],
                    'update-ajax' => ['post'],
                    'sort-ajax'   => ['put'],
                    'cancel-ajax' => ['post'],
                    'delete-ajax' => ['delete'],
                ],
            ], // verbs
        ]; // return
    } // behaviors

} // AppController
