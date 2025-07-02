<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Historial de accesos';
$this->params['breadcrumbs'][] = 'Administración';
$this->params['breadcrumbs'][] = 'Sistema';
$this->params['breadcrumbs'][] = $this->title;

$bttExport = Yii::$app->name . " - $this->title - " . date('Y-m-d H.i');
$bttUrl    = Url::to(['historial-de-accesos-json-btt']);
?>

<div class="admin-historial-de-acceso-index">
    <div class="btt-toolbar"></div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'user',
                    title: 'Username',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'wrong_password',
                    title: 'Password erroneo',
                    sortable: true,
                    visible: false,
                },
                {
                    field: 'user_name',
                    title: 'Usuario',
                    sortable: true,
                },
                {
                    field: 'ip',
                    title: 'IP cliente',
                    align: "center",
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'access_login',
                    title: 'Hora de acceso',
                    align: "center",
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'access_logout',
                    title: 'Cierre de sesión',
                    align: "center",
                    sortable: true,
                    visible: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'access',
                    title: 'Acceso concedido',
                    align: "center",
                    sortable: true,
                    switchable: false,
                    formatter: btf.ui.checkbox,
                },
            ],
            params = {
                id      : 'historial-de-acceso',
                element : '.admin-historial-de-acceso-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
