<?php
namespace app\components;

use Yii;
use yii\helpers\Url;
use yii\base\Component;
use yii\helpers\Html;
use yii\widgets\Menu;
use yii\widgets\ActiveForm;
use app\models\Esys;
use app\models\user\User;
use app\models\inv\InventarioOperacion;


class NiftyComponent extends Component{
	private $menuItems;

    public function __construct($config = [])
    {
        // ... initialization before configuration is applied

        parent::__construct($config);
    }


     public function init()
    {
        parent::init();

		if(!isset(Yii::$app->user->identity)){
			$this->menuItems[] = ['label' =>  Yii::$app->name , 'options' => ['class' => 'list-header']];
			$this->menuItems[] = ['label' => '<i class="fa fa-lock"></i><span class="menu-title">Iniciar sesión</span>', 'url' => ['/admin/user/login']];
			$this->menuItems[] = ['label' => '<i class="fa fa-lock"></i><span class="menu-title">¿Olvidaste tu contraseña?</span>', 'url' => ['/admin/user/request-password-reset']];

		}else{
			/*****************************
			* CATALOGOS
			*****************************/
			#$incidencia= [];
			#if(Yii::$app->user->identity->id == 1 || Yii::$app->user->identity->id == 5 )
			#	$incidencia[] = ['label' => '<i class="fa fa-info-circle "></i><span class="nav-label">INCIDENCIAS </span> '. ( User::getNotificacionIncidenciaTraspaso() > 0 ?  '<span class="label float-right" style="background: #de0540; color: #fff;">'. User::getNotificacionIncidenciaTraspaso() .'</span>' : '' ).' ',  'url' => ['/inventario/operacion-entrada-incidencia/index'],  'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];
			

			$itemsCrm = [];

			if(Yii::$app->user->can('clienteView'))
				$itemsCrm[] = ['label' => 'Clientes', 'url' => ['/crm/cliente/index']];

			if(Yii::$app->user->can('proveedorView'))
				$itemsCrm[] = ['label' => 'Proveedores', 'url' => ['/crm/proveedor/index']];

			if(Yii::$app->user->can('sucursalView'))
				$itemsCrm[] = ['label' => ' Sucursales', 'url' => ['/sucursales/sucursal/index'] ];


			if(Yii::$app->user->can('productoView'))
				$itemsCrm[] = ['label' => 'Productos', 'url' => ['/productos/producto/index'] ];


			if(Yii::$app->user->can('clienteView') ||  Yii::$app->user->can('proveedorView') || Yii::$app->user->can('sucursalView') || Yii::$app->user->can('productoView'))
					$crm[] = ['label' => '<i class="fa fa-id-card"></i><span class="nav-label">Catálogos</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $itemsCrm ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/**
			 *  CREDITOS
			 * */

			$creditoItem = [];
			$credito 	 = [];

			if(Yii::$app->user->can('creditoView'))
				$creditoItem[] = ['label' => 'Creditos', 'url' => ['/creditos/credito/index'] ];

			if(Yii::$app->user->can('creditoPayCliente'))
				$creditoItem[] = ['label' => 'ABONO A CLIENTE', 'url' => ['/creditos/credito/register-pay-cliente'] ];

			if(Yii::$app->user->can('creditoPayProveedor'))
				$creditoItem[] = ['label' => 'ABONO A PROVEEDOR', 'url' => ['/creditos/credito/register-pay-proveedor'] ];


			if(Yii::$app->user->can('creditoView') || Yii::$app->user->can('creditoPayCliente') || Yii::$app->user->can('creditoPayProveedor') )
				$credito[] = ['label' => '<i class="fa fa-credit-card"></i><span class="nav-label">Creditos</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $creditoItem ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];

			/**
			 *  VENTAS
			 * */

			$tpvPrecaptura = [];

			#if(Yii::$app->user->can('precapturaView'))
			#	$tpvPrecaptura[] = ['label' => ' Pre ventas', 'url' => ['/tpv/pre-captura/index'] ];

			#if(Yii::$app->user->can('precapturaView'))
			#	$tpvPrecaptura[] = ['label' => 'Pre ventas APP', 'url' => ['/tpv/pre-venta/index'] ];

			if(Yii::$app->user->can('ventaView'))
				$tpvPrecaptura[] = ['label' => 'Ventas', 'url' => ['/tpv/venta/index'] ];




			if(Yii::$app->user->can('precapturaView') || Yii::$app->user->can('ventaView'))
				$tpv[] = ['label' => '<i class="fa fa-shopping-cart"></i><span class="nav-label">Ventas</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $tpvPrecaptura ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/**
			 *  CAJA TPV
			 * */

			$cajaItem = [];

				if(Yii::$app->user->can('cajaCierreApertura'))
					$cajaItem[] = ['label' => 'Apertura y Cierre de caja', 'url' => ['/caja/apertura-y-cierre/index']];


				if(Yii::$app->user->can('admin'))
					$cajaItem[] = ['label' => 'COBROS Y ABONOS', 'url' => ['/caja/cobro-abono/index']];

				/*if(Yii::$app->user->can('cajaArqueo'))
					$cajaItem[] = ['label' => 'Arqueo de caja', 'url' => ['/caja/apertura-y-cierre/arqueo']];*/

			if(Yii::$app->user->can('cajaCierreApertura'))
				$caja[] = ['label' => '<i class="fa fa-handshake-o"></i><span class="nav-label">Caja TVP</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $cajaItem ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/**
			 *  COMPRA
			 * */

			if(Yii::$app->user->can('compraView') )
				$compra[] = ['label' => '<i class="fa fa-shopping-cart"></i><span class="nav-label">Compras</span>',  'url' => ['/compras/compra/index'],  'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];
				

			/**
			 *  INVENTARIO
			 * */

			$inventarioItem = [];

			#if(Yii::$app->user->can('operacionAjusteInventario'))
			#	$inventarioItem[] = ['label' => 'OPERACIÓN', 'url' => ['/inventario/operacion/index']];

			if(Yii::$app->user->can('inventario'))
				$inventarioItem[] = ['label' => 'CONSULTA', 'url' => ['/inventario/arqueo-inventario/index']];



			if(Yii::$app->user->can('inventarioBalance'))
				$inventarioItem[] = ['label' => 'BALANCE', 'url' => ['/inventario/balance-inventario/index']];


			$devolucion = [];
			if(Yii::$app->user->can('devolucionView'))
				$devolucion[] = ['label' => '<i class="fa fa fa-refresh"></i><span class="nav-label">Devolución</span>', 'url' => ['/inventario/devolucion/index']];



			if(Yii::$app->user->can('inventario') || Yii::$app->user->can('inventarioBalance') || Yii::$app->user->can('devolucionView'))
				$inventario[] = ['label' => '<i class="fa fa-th-large"></i><span class="nav-label">Inventario</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $inventarioItem ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/**
			 *  REPORTE SUB REPORTE OPERACION
			 * */

			$reporteItem = [];
			$reporteSubOperacionItem = [];

			if(Yii::$app->user->can('reporteEntrada'))
				$reporteSubOperacionItem[] = ['label' => 'Entrada', 'url' => ['/reportes/reporte-entrada/index']];

			if(Yii::$app->user->can('reporteProducto'))
				$reporteSubOperacionItem[] = ['label' => 'Productos [COMPRAS]', 'url' => ['/reportes/reporte-producto/index']];

			if(Yii::$app->user->can('reporteCargaUnidad'))
				$reporteSubOperacionItem[] = ['label' => 'Carga Unidad', 'url' => ['/reportes/reporte-carga-unidad/index']];

			if(Yii::$app->user->can('reporteAbastecimiento'))
				$reporteSubOperacionItem[] = ['label' => 'Abastecimientos', 'url' => ['/reportes/reporte-abastecimiento/index']];

			if(Yii::$app->user->can('reporteSalida'))
				$reporteSubOperacionItem[] = ['label' => 'Surtir', 'url' => ['/reportes/reporte-salida/index']];

			if(Yii::$app->user->can('reporteSurtio'))
				$reporteSubOperacionItem[] = ['label' => 'Surtir', 'url' => ['/reportes/reporte-surtir/index']];

			if(Yii::$app->user->can('reporteProducto') || Yii::$app->user->can('reporteEntrada') || Yii::$app->user->can('reporteSalida') || Yii::$app->user->can('reporteTraspaso'))
				$reporteItem[] = ['label' => 'OPERACIÓN<span class="fa arrow"></span>', 'url' => '#', 'items' => $reporteSubOperacionItem ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/**
			 *  REPORTE SUB REPORTE VENTA
			 * */

			$reporteSubVentaItem = [];
			if(Yii::$app->user->can('reporteVentaProducto'))
				$reporteSubVentaItem[] = ['label' => 'Productos', 'url' => ['/reportes/reporte-venta/producto']];

			if(Yii::$app->user->can('reporteVentaCliente'))
				$reporteSubVentaItem[] = ['label' => 'Ventas', 'url' => ['/reportes/reporte-cliente-venta/cliente']];

			if(Yii::$app->user->can('reporteVentaClienteSaldo'))
				$reporteSubVentaItem[] = ['label' => 'Cliente', 'url' => ['/reportes/reporte-cliente-saldo/index']];

			if(Yii::$app->user->can('reporteVentaProveedorSaldo'))
				$reporteSubVentaItem[] = ['label' => 'Proveedor', 'url' => ['/reportes/reporte-proveedor-saldo/index']];

			if(Yii::$app->user->can('reporteCentroNegocio'))
				$reporteSubVentaItem[] = ['label' => 'Centro negocio', 'url' => ['/reportes/reporte-centro-negocio/index']];

            if(Yii::$app->user->can('admin'))
                $reporteSubVentaItem[] = ['label' => 'Gastos', 'url' => ['/reportes/reporte-gastos/index']];

			if(Yii::$app->user->can('reporteVentaProducto') || Yii::$app->user->can('reporteVentaCliente') || Yii::$app->user->can('reporteVentaClienteSaldo') || Yii::$app->user->can('reporteVentaProveedorSaldo') || Yii::$app->user->can('reporteGastos') )
				$reporteItem[] = ['label' => 'ADMINISTRACION<span class="fa arrow"></span>', 'url' => '#', 'items' => $reporteSubVentaItem ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/**
			 *  REPORTE
			 * */

			if(Yii::$app->user->can('reporteProducto') || Yii::$app->user->can('reporteEntrada') || Yii::$app->user->can('reporteSalida') || Yii::$app->user->can('reporteTraspaso'))
				$reporte[] = ['label' => '<i class="fa fa-clipboard"></i><span class="nav-label">Reportes</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $reporteItem ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/**
			 *  ALMACEN
			 * */
			$alamcenItem = [];

				if(Yii::$app->user->can('traspasos'))
					$alamcenItem[] = ['label' => 'Entradas y Salidas', 'url' => ['/inventario/entradas-salidas/index']];

				if(Yii::$app->user->can('almacenView'))
					$alamcenItem[] = ['label' => 'CEDIS', 'url' => ['/almacenes/almacen/index']];

			if(Yii::$app->user->can('almacenView') || Yii::$app->user->can('traspasos') )
				$almacen[] = ['label' => '<i class="fa fa-th-large"></i><span class="nav-label">CEDIS</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $alamcenItem ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			$logisticaItem = [];
			if(Yii::$app->user->can('repartoAccess'))
					$logisticaItem[] = ['label' => 'CARGA DE RUTAS', 'url' => ['/logistica/ruta/index']];

			if(Yii::$app->user->can('repartoAccess'))
				$logistica[] = ['label' => '<i class="fa fa-map"></i><span class="nav-label">Logistica</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $logisticaItem ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/*****************************
			* CONTABILIDAD
			*****************************/
			/*
			$itemsConta=[];
			$contabilidad = [];

			if(Yii::$app->user->can('admin'))
				$itemsConta[] = ['label' => 'CATALOGO', 'url' => ['/contabilidad/claves/index']];

			if(Yii::$app->user->can('admin'))
				$itemsConta[] = ['label' => 'TRANSACCIONES', 'url' => ['/contabilidad/transacciones/index']];

			if(Yii::$app->user->can('admin'))
				$itemsConta[] = ['label' => 'POLIZAS', 'url' => ['/contabilidad/polizas/index']];

			$items_estados = [];

				if(Yii::$app->user->can('admin'))
				$items_estados[] = ['label' => 'PRESUPUESTOS', 'url' => ['/contabilidad/estados/presupuestos']];

				if(Yii::$app->user->can('admin'))
				$items_estados[] = ['label' => 'ESTADOS FINANCIEROS', 'url' => ['/contabilidad/estados/index']];

			if(Yii::$app->user->can('admin'))
				$itemsConta[] = ['label' => '<span class="nav-label">FINANZAS</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $items_estados ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];

			if(Yii::$app->user->can('admin')|| Yii::$app->user->can('admin') || Yii::$app->user->can('admin'))
				$contabilidad[] = ['label' => '<i class="fa fa-tasks"></i><span class="nav-label">Contabilidad</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $itemsConta ,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			*/

			/*****************************
			* Administración
			*****************************/
			$admin = [];

			if(Yii::$app->user->can('userView'))
				$admin[] = ['label' => '<i class="fa fa-users"></i><span class="nav-label">Usuarios</span>', 'url' => ['/admin/user/index']];


			$adminConfig = [];

			if(Yii::$app->user->can('perfilUserView'))
				$adminConfig[] = ['label' => 'Perfiles de usuarios', 'url' => ['/admin/perfil/index']];

			if(Yii::$app->user->can('listaDesplegableView'))
				$adminConfig[] = ['label' => 'Listas desplegables', 'url' => ['/admin/listas-desplegables/index']];

			if(Yii::$app->user->can('configuracionSitio'))
				$adminConfig[] = ['label' => 'Configuracion del sitio', 'url' => ['/admin/configuracion/configuracion-update']];

			if(!empty($adminConfig))
				$admin[] = ['label' => '<i class="fa fa-cogs"></i><span class="nav-label">Configuraciones </span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $adminConfig,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			$adminSistema = [];

			if(Yii::$app->user->can('historialAccesosUser'))
				$adminSistema[] = ['label' => 'Historial de accesos', 'url' => ['/admin/historial-de-acceso/index']];

			if(Yii::$app->user->can('theCreator'))
				$adminSistema[] = ['label' => 'Versiones / Updates ', 'url' => ['/admin/version/list']];

			if(!empty($adminSistema))
				$admin[] = ['label' => '<i class="fa fa-database"></i><span class="nav-label">Sistema</span> <span class="fa arrow"></span>', 'url' => '#', 'items' => $adminSistema,'submenuTemplate' => "\n<ul class='nav nav-second-level'>\n{items}\n</ul>\n"];


			/*****************************
			* Menú Items
			*****************************/
				#if(!empty($incidencia)){
				#	foreach ($incidencia as $key => $item) {
				#		$this->menuItems[] = $item;
				#	}
				#}
				
				if(!empty($crm)){
					foreach ($crm as $key => $item) {
						$this->menuItems[] = $item;
					}
				}

				if(!empty($credito)){
					foreach ($credito as $key => $item) {
						$this->menuItems[] = $item;
					}
				}

				if(!empty($tpv)){
					foreach ($tpv as $key => $item) {
						$this->menuItems[] = $item;
					}
				}

				/*if(!empty($productos)){
					foreach ($productos as $key => $item) {
						$this->menuItems[] = $item;
					}
				}*/

				if(!empty($caja)){
					foreach ($caja as $key => $item) {
						$this->menuItems[] = $item;
					}
				}

				
				if( Yii::$app->user->can('admin') || (User::getNotificacionInventario() ? count(User::getNotificacionInventario()) : 0 ) < 1  ){
					if(!empty($inventario)){
						foreach ($inventario as $key => $item) {
							$this->menuItems[] = $item;
						}
					}
				}

				if(!empty($devolucion)){
					foreach ($devolucion as $key => $item) {
						$this->menuItems[] = $item;
					}
				}

				if(!empty($compra)){
					foreach ($compra as $key => $item) {
						$this->menuItems[] = $item;
					}
				}

				if(!empty($reporte)){
					foreach ($reporte as $key => $item) {
						$this->menuItems[] = $item;
					}
				}

			 	if(!empty($logistica)){
					foreach ($logistica as $key => $item) {
						$this->menuItems[] = $item;
					}
				}


				if(!empty($almacen)){
					foreach ($almacen as $key => $item) {
						$this->menuItems[] = $item;
					}
				}

				#if(!empty($contabilidad)){
				#	foreach ($contabilidad as $key => $item) {
				#		$this->menuItems[] = $item;
				#	}
				#}


				if(!empty($admin)){
					foreach ($admin as $key => $item) {
						$this->menuItems[] = $item;
					}
				}
		}

    }


	/*********************************
	/ Navigation Bar - Elements Template
	/********************************/
		public function get_notification_dropdown(){
			if(!isset(Yii::$app->user->identity))
				return false;
			ob_start();
			?>
			<div class="navbar-header">
                <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
            </div>
            <ul class="nav navbar-top-links navbar-right">

                <li>
                    <span class="m-r-sm text-muted welcome-message">Bienvenido a <?= Yii::$app->name ?>.</span>
                </li>
		<!-- 
                <li class="dropdown">
				    <a href="<?= Url::to(['/apk/APP-ARROYO-V1.0.13.apk']) ?>" download>
				        <i class="fa fa-android"  style="font-size:54px;color: #57dc71;"></i>
				        <p style="font-size:10px; font-weight:700; color:#000; text-align:center">V.1.0.13</p>
				    </a>
				</li>

				<?php if (Yii::$app->user->can('verificacionPreventaAccess')): ?>
	                <li class="dropdown">
	                	<a class="dropdown-toggle count-info"  href="<?= Url::to(["/tpv/pre-venta/index-comanda-autorizacion"]) ?>">
	                    	<i class="fa fa-shopping-cart"  id="icon-proceso-verificacion" style="font-size:54px; "></i><span class="label " style="color: #fff;" id="lbl-proceso-verificacion-count">0</span>
	                    </a>
	                </li>
				<?php endif ?>
				-->

                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                        <i class="fa fa-bell"  style="font-size:54px; <?= (User::getNotificacionInventario() ? count(User::getNotificacionInventario()) : 0 ) +  ( User::getNotificacionInventarioRevision() ? count(User::getNotificacionInventarioRevision()) : 0 ) > 0 ? 'color:#cbb70e' : ''  ?>"></i>  <span class="label " style="<?= ( User::getNotificacionInventario() ? count(User::getNotificacionInventario()) : 0) +  (User::getNotificacionInventarioRevision() ? count(User::getNotificacionInventarioRevision()) : 0 ) > 0 ? 'background: #cbb70e; color: #fff;' : ''  ?>"><?= ( User::getNotificacionInventario() ? count(User::getNotificacionInventario()) : 0) +  (User::getNotificacionInventarioRevision() ? count(User::getNotificacionInventarioRevision()) : 0 ) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-alerts">
                    	<?php foreach (User::getNotificacionInventario() as $key => $item_notificacion): ?>
	                    	<li>
	                            <a href="<?= Url::to(["/inventario/operacion/view-ajuste", "id" => $item_notificacion->id ]) ?>" class="dropdown-item">
	                                <div>
	                                	<div class="row">
	                                		<div class="col-sm-3">
	                                			<i class="fa fa-cubes fa-fw" style="font-size:34px; color:#cbb70e"></i>
	                                		</div>
	                                		<div class="col-sm-9">
	                                     		<p>SOLICITUD DE AJUSTE DE INVENTARIO <strong><?= InventarioOperacion::$tipoList[$item_notificacion->tipo] ?></strong></p>
	                                		</div>
	                                	</div>
	                                    <span class="float-right text-muted small"><?= Esys::hace_tiempo_en_texto($item_notificacion->created_at) ?></span>
	                                </div>
	                            </a>
	                        </li>
                    	<?php endforeach ?>

                    	<?php if (Yii::$app->user->can('operacionAjusteInventario')): ?>

                    		<?php foreach (User::getNotificacionInventarioRevision() as $key => $item_notificacion): ?>
	                    	<li>
	                            <a href="<?= Url::to(["/inventario/operacion/view", "id" => $item_notificacion->id ]) ?>" class="dropdown-item">
	                                <div>
	                                	<div class="row">
	                                		<div class="col-sm-3">
	                                			<i class="fa fa-cubes fa-fw" style="font-size:34px; color:#cbb70e"></i>
	                                		</div>
	                                		<div class="col-sm-9">
	                                     		<p>SOLICITUD DE AJUSTE DE INVENTARIO <strong><?= InventarioOperacion::$tipoList[$item_notificacion->tipo] ?></strong></p>
	                                		</div>
	                                	</div>
	                                    <span class="float-right text-muted small"><?= Esys::hace_tiempo_en_texto($item_notificacion->updated_at) ?></span>
	                                </div>
	                            </a>
	                        </li>
                    	<?php endforeach ?>

                    	<?php endif ?>
                    </ul>
                </li>

                <li>
                    <?= Html::a('<i class="fa fa-sign-out"></i> Cerrar sesión', ['/admin/user/logout'],['data-method' => 'post']) ?>
                </li>
            </ul>
			<?php
			return ob_get_clean();
		}

		public function get_mega_dropdown(){
			if(!isset(Yii::$app->user->identity))
				return false;

			ob_start();
			?>
			<li class="mega-dropdown">
				<a href="#" class="mega-dropdown-toggle">
					<i class="fa fa-th-large fa-lg"></i>
				</a>
				<div class="dropdown-menu mega-dropdown-menu">
					<div class="clearfix">

					</div>
				</div>
			</li>

			<?php

			return ob_get_clean();
		}

		public function get_language_selector(){
			ob_start();
			?>
			<!--Language selector-->
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<!--End language selector-->
			<?php

			return ob_get_clean();
		}

		public function get_user_dropdown(){
			if(!isset(Yii::$app->user->identity))
				return false;

			ob_start();
			?>

            <li id="dropdown-user" class="dropdown">
                <a href="#" data-toggle="dropdown" class="dropdown-toggle text-right">
                    <span class="pull-right">
                        <i class="demo-pli-male ic-user"></i>
                    </span>
                    <div class="username hidden-xs"><?= Yii::$app->user->identity->email ?></div>
                </a>

                <div class="dropdown-menu dropdown-menu-md dropdown-menu-right panel-default">

                    <!-- User dropdown menu -->
                    <ul class="head-list">
						<li>
                            <?= Html::a('<i class="demo-pli-male icon-lg icon-fw"></i> Mi perfil', ['/admin/user/mi-perfil']) ?>
						</li>
						<li>
                            <?= Html::a('<i class="demo-psi-lock-2 icon-lg icon-fw"></i> Cambiar contraseña', ['/admin/user/change-password']) ?>
						</li>
						<li>
                            <?= Html::a('<i class="fa fa-code icon-fw"></i> Acerca de . . .', ['/site/about']) ?>
						</li>
                    </ul>

                    <!-- Dropdown footer -->
                    <div class="pad-all text-right">
	                    <?= Html::a('<i class="fa fa-sign-out fa-fw"></i> Cerrar sesión', ['/admin/user/logout'], [
	                        	'class' => 'btn btn-primary',
	                        	'data-method' => 'post'
	                        	]) ?>
                    </div>
                </div>
			</li>
			<?php

			return ob_get_clean();
		}

		public function get_aside(){
			if(!isset(Yii::$app->user->identity))
				return false;

			ob_start();
			?>
				<!--Nav tabs-->
				<!--================================-->
				<ul class="nav nav-tabs nav-justified">
					<li class="active">
						<a href="#demo-asd-tab-1" data-toggle="tab">
							<i class="demo-pli-speech-bubble-7"></i>
						</a>
					</li>
					<li>
						<a href="#demo-asd-tab-2" data-toggle="tab">
							<i class="demo-pli-information icon-fw"></i> Report
						</a>
					</li>
					<li>
						<a href="#demo-asd-tab-3" data-toggle="tab">
							<i class="demo-pli-wrench icon-fw"></i> Settings
						</a>
					</li>
				</ul>
				<!--================================-->
				<!--End nav tabs-->



				<!-- Tabs Content -->
				<!--================================-->
				<div class="tab-content">

					<!--First tab (Contact list)-->
					<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
					<div class="tab-pane fade in active" id="demo-asd-tab-1">
						<p class="pad-hor mar-top text-semibold text-main">
							<span class="pull-right badge badge-warning">3</span> Family
						</p>

						<!--Family-->
						<div class="list-group bg-trans">
							<a href="#" class="list-group-item">
								<div class="media-left pos-rel">
								<!--
									<img class="img-circle img-xs" src="img/profile-photos/2.png" alt="Profile Picture">
								-->
									<i class="badge badge-success badge-stat badge-icon pull-left"></i>
								</div>
								<div class="media-body">
									<p class="mar-no">Stephen Tran</p>
									<small class="text-muted">Availabe</small>
								</div>
							</a>
							<a href="#" class="list-group-item">
								<div class="media-left pos-rel">
								<!--
									<img class="img-circle img-xs" src="img/profile-photos/7.png" alt="Profile Picture">
								-->
								</div>
								<div class="media-body">
									<p class="mar-no">Brittany Meyer</p>
									<small class="text-muted">I think so</small>
								</div>
							</a>
							<a href="#" class="list-group-item">
								<div class="media-left pos-rel">
								<!--
									<img class="img-circle img-xs" src="img/profile-photos/1.png" alt="Profile Picture">
								-->
									<i class="badge badge-info badge-stat badge-icon pull-left"></i>
								</div>
								<div class="media-body">
									<p class="mar-no">Jack George</p>
									<small class="text-muted">Last Seen 2 hours ago</small>
								</div>
							</a>
							<a href="#" class="list-group-item">
								<div class="media-left pos-rel">
								<!--
									<img class="img-circle img-xs" src="img/profile-photos/4.png" alt="Profile Picture">
								-->
								</div>
								<div class="media-body">
									<p class="mar-no">Donald Brown</p>
									<small class="text-muted">Lorem ipsum dolor sit amet.</small>
								</div>
							</a>
							<a href="#" class="list-group-item">
								<div class="media-left pos-rel">
								<!--
									<img class="img-circle img-xs" src="img/profile-photos/8.png" alt="Profile Picture">
								-->
									<i class="badge badge-warning badge-stat badge-icon pull-left"></i>
								</div>
								<div class="media-body">
									<p class="mar-no">Betty Murphy</p>
									<small class="text-muted">Idle</small>
								</div>
							</a>
							<a href="#" class="list-group-item">
								<div class="media-left pos-rel">
								<!--
									<img class="img-circle img-xs" src="img/profile-photos/9.png" alt="Profile Picture">
								-->
									<i class="badge badge-danger badge-stat badge-icon pull-left"></i>
								</div>
								<div class="media-body">
									<p class="mar-no">Samantha Reid</p>
									<small class="text-muted">Offline</small>
								</div>
							</a>
						</div>

						<hr>
						<p class="pad-hor text-semibold text-main">
							<span class="pull-right badge badge-success">Offline</span> Friends
						</p>

						<!--Works-->
						<div class="list-group bg-trans">
							<a href="#" class="list-group-item">
								<span class="badge badge-purple badge-icon badge-fw pull-left"></span> Joey K. Greyson
							</a>
							<a href="#" class="list-group-item">
								<span class="badge badge-info badge-icon badge-fw pull-left"></span> Andrea Branden
							</a>
							<a href="#" class="list-group-item">
								<span class="badge badge-success badge-icon badge-fw pull-left"></span> Johny Juan
							</a>
							<a href="#" class="list-group-item">
								<span class="badge badge-danger badge-icon badge-fw pull-left"></span> Susan Sun
							</a>
						</div>


						<hr>
						<p class="pad-hor mar-top text-semibold text-main">News</p>

						<div class="pad-hor">
							<p class="text-muted">Lorem ipsum dolor sit amet, consectetuer
								<a data-title="45%" class="add-tooltip text-semibold" href="#">adipiscing elit</a>, sed diam nonummy nibh. Lorem ipsum dolor sit amet.
							</p>
							<small class="text-muted"><em>Last Update : Des 12, 2014</em></small>
						</div>


					</div>
					<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
					<!--End first tab (Contact list)-->


					<!--Second tab (Custom layout)-->
					<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
					<div class="tab-pane fade" id="demo-asd-tab-2">

						<!--Monthly billing-->
						<div class="pad-all">
							<p class="text-semibold text-main">Billing &amp; reports</p>
							<p class="text-muted">Get <strong>$5.00</strong> off your next bill by making sure your full payment reaches us before August 5, 2016.</p>
						</div>
						<hr class="new-section-xs">
						<div class="pad-all">
							<span class="text-semibold text-main">Amount Due On</span>
							<p class="text-muted text-sm">August 17, 2016</p>
							<p class="text-2x text-thin text-main">$83.09</p>
							<button class="btn btn-block btn-success mar-top">Pay Now</button>
						</div>


						<hr>

						<p class="pad-hor text-semibold text-main">Additional Actions</p>

						<!--Simple Menu-->
						<div class="list-group bg-trans">
							<a href="#" class="list-group-item"><i class="demo-pli-information icon-lg icon-fw"></i> Service Information</a>
							<a href="#" class="list-group-item"><i class="demo-pli-mine icon-lg icon-fw"></i> Usage Profile</a>
							<a href="#" class="list-group-item"><span class="label label-info pull-right">New</span><i class="demo-pli-credit-card-2 icon-lg icon-fw"></i> Payment Options</a>
							<a href="#" class="list-group-item"><i class="demo-pli-support icon-lg icon-fw"></i> Message Center</a>
						</div>


						<hr>

						<div class="text-center">
							<div><i class="demo-pli-old-telephone icon-3x"></i></div>
							Questions?
							<p class="text-lg text-semibold text-main"> (415) 234-53454 </p>
							<small><em>We are here 24/7</em></small>
						</div>
					</div>
					<!--End second tab (Custom layout)-->
					<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


					<!--Third tab (Settings)-->
					<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
					<div class="tab-pane fade" id="demo-asd-tab-3">
						<ul class="list-group bg-trans">
							<li class="pad-top list-header">
								<p class="text-semibold text-main mar-no">Account Settings</p>
							</li>
							<li class="list-group-item">
								<div class="pull-right">
									<input class="toggle-switch" id="demo-switch-1" type="checkbox" checked>
									<label for="demo-switch-1"></label>
								</div>
								<p class="mar-no">Show my personal status</p>
								<small class="text-muted">Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</small>
							</li>
							<li class="list-group-item">
								<div class="pull-right">
									<input class="toggle-switch" id="demo-switch-2" type="checkbox" checked>
									<label for="demo-switch-2"></label>
								</div>
								<p class="mar-no">Show offline contact</p>
								<small class="text-muted">Aenean commodo ligula eget dolor. Aenean massa.</small>
							</li>
							<li class="list-group-item">
								<div class="pull-right">
									<input class="toggle-switch" id="demo-switch-3" type="checkbox">
									<label for="demo-switch-3"></label>
								</div>
								<p class="mar-no">Invisible mode </p>
								<small class="text-muted">Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. </small>
							</li>
						</ul>


						<hr>

						<ul class="list-group pad-btm bg-trans">
							<li class="list-header"><p class="text-semibold text-main mar-no">Public Settings</p></li>
							<li class="list-group-item">
								<div class="pull-right">
									<input class="toggle-switch" id="demo-switch-4" type="checkbox" checked>
									<label for="demo-switch-4"></label>
								</div>
								Online status
							</li>
							<li class="list-group-item">
								<div class="pull-right">
									<input class="toggle-switch" id="demo-switch-5" type="checkbox" checked>
									<label for="demo-switch-5"></label>
								</div>
								Show offline contact
							</li>
							<li class="list-group-item">
								<div class="pull-right">
									<input class="toggle-switch" id="demo-switch-6" type="checkbox" checked>
									<label for="demo-switch-6"></label>
								</div>
								Show my device icon
							</li>
						</ul>



						<hr>

						<p class="pad-hor text-semibold text-main mar-no">Task Progress</p>
						<div class="pad-all">
							<p>Upgrade Progress</p>
							<div class="progress progress-sm">
								<div class="progress-bar progress-bar-success" style="width: 15%;"><span class="sr-only">15%</span></div>
							</div>
							<small class="text-muted">15% Completed</small>
						</div>
						<div class="pad-hor">
							<p>Database</p>
							<div class="progress progress-sm">
								<div class="progress-bar progress-bar-danger" style="width: 75%;"><span class="sr-only">75%</span></div>
							</div>
							<small class="text-muted">17/23 Database</small>
						</div>

					</div>
					<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
					<!--Third tab (Settings)-->
				</div>
			<?php

			return ob_get_clean();
		}


	/*********************************
	/ MAIN NAVIGATION - Elements Template
	/********************************/
		public function get_profile_widget(){
			if(!isset(Yii::$app->user->identity))
				return false;

			ob_start();
			?>

			<li class="nav-header">
                <div class="dropdown profile-element">
                    <?= Html::img(User::getAvatar(), ["class" => "rounded-circle", "alt" => "avatar", "style" => "width:90px"]) ?>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="block m-t-xs font-bold"><?= Yii::$app->user->identity->username ?></span>
                        <span class="text-muted text-xs block"><?= Yii::$app->user->identity->email ?> <b class="caret"></b></span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li>
                        	 <?= Html::a('Mi perfil', ['/admin/user/mi-perfil'],['class' => 'dropdown-item']) ?>
                        </li>
                        <li>
                        	 <?= Html::a('Cambiar contraseña', ['/admin/user/change-password'],['class' => 'dropdown-item']) ?>
                        </li>
                        <li>
                        	<?= Html::a('Cerrar sesión', ['/admin/user/logout'],['class' => 'dropdown-item','data-method' => 'post']) ?>
                        </li>
                    </ul>
                </div>
                <div class="logo-element">
                    PE
                </div>
            </li>
			<?php

			return ob_get_clean();
		}

		public function get_shortcut_buttons(){
			ob_start();
			/*
			?>
			<div id="mainnav-shortcut">
				<ul class="list-unstyled">
					<?php if(Yii::$app->user->can('flexzoneAdmin') || Yii::$app->user->can('cafeteriaAdmin')): ?>
					<li class="col-xs-4" data-content="Usuarios internos">
						<?= Html::a('<i class="fa fa-users"></i>', ['/admin/user/index'], ["id" => "shortcut-usuarios", "class" => "shortcut-grid"]) ?>
					</li>
					<?php endif?>

					<?php if(Yii::$app->user->can('flexzoneComprasGastos') || Yii::$app->user->can('flexzoneVentas') || Yii::$app->user->can('flexzoneFacturacion')): ?>
					<li class="col-xs-4" data-content="Clientes">
						<?= Html::a('<i class="fa fa-child"></i>', ['/flexzone/cliente/index'], ["id" => "shortcut-clientes", "class" => "shortcut-grid"]) ?>
					</li>
					<?php endif?>

					<?php if(Yii::$app->user->can('flexzoneVentas')): ?>
					<li class="col-xs-4" data-content="Nueva ventas">
						<?= Html::a('<i class="fa fa-shopping-cart"></i>', ['/flexzone/venta/create'], ["id" => "shortcut-ventas", "class" => "shortcut-grid"]) ?>
					</li>
					<?php endif?>

					<?php if(Yii::$app->user->can('flexzoneAccesos')): ?>
					<li class="col-xs-4" data-content="Comprobar membresía">
						<?= Html::a('<i class="fa fa-credit-card"></i>', ['/flexzone/venta/comprobar-membresia'], ["id" => "shortcut-comprobar-membresia", "class" => "shortcut-grid"]) ?>
					</li>
					<?php endif?>
				</ul>
			</div>
			<?php
			*/

			return ob_get_clean();
		}

		public function get_menu(){
            return  Menu::widget([
                'options'         => ['class' => 'nav metismenu', 'id' => 'side-menu'],
                'encodeLabels'    => false,
                'activateParents' => true,
                'activeCssClass'  => 'active',
                'items'           => $this->menuItems == null? ['label' => '']: $this->menuItems,
            ]);
		}

		public function get_widget(){
			if(!Yii::$app->user->can('recursosServidor'))
				return false;

			ob_start();
			?>
			<div class="mainnav-widget" style="width:100%">

				<div id="wg-server" class="hide-small mainnav-widget-content" style="padding: 10px 15px;">
					<ul class="nav metismenu">
						<li >Estado del servido</li>
						<li >
							<span class="label label-primary pull-right label-cpu-use">0</span>
							<p style="color:#fff">Uso de CPU</p>
							<div class="progress progress-mini">
								<div class="progress-bar progress-bar-cpu progress-bar-primary">
									<span class="sr-only label-cpu-use">0</span>
								</div>
							</div>
						</li>

						<li >
							<span class="label label-purple label-mem-use pull-right">0</span>
							<p style="color:#fff">Uso de Memoria</p>
							<div class="progress progress-mini">
								<div class="progress-bar progress-bar-mem progress-bar-purple">
									<span class="sr-only label-mem-use"></span>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>

			<script>
				$(document).ready(function(){
					var avg_url	  = '<?= Yii::getAlias('@web') ?>',
						avg_interval = <?= Yii::$app->params['settings']['avg_interval'] ?>;

					nifty_avg(avg_url, avg_interval);
				});
			</script>
			<?php

			return ob_get_clean();
		}

}
