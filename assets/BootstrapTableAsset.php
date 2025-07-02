<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class BootstrapTableAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-table/dist';

    public $css = [
        'bootstrap-table.min.css',
        //'extensions/reorder-rows/bootstrap-table-reorder-rows.css',
    ];

    public $js = [
        'bootstrap-table.min.js',
        'bootstrap-table-locale-all.min.js',
        'locale/bootstrap-table-es-MX.min.js',
        'extensions/mobile/bootstrap-table-mobile.min.js',
        'extensions/cookie/bootstrap-table-cookie.min.js',
        'extensions/export/bootstrap-table-export.min.js',
        /*
        'extensions/resizable/bootstrap-table-resizable.min.js',
        'extensions/toolbar/bootstrap-table-toolbar.min.js',
        'extensions/reorder-rows/bootstrap-table-reorder-rows.min.js',
        'extensions/reorder-columns/bootstrap-table-reorder-columns.min.js',
        'extensions/print/bootstrap-table-print.min.js',
        */
    ];

    public $depends = [
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\TableExportAsset',
        'app\assets\ResizableAsset',
    ];
}
