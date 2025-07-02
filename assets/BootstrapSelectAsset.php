<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class BootstrapSelectAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-select/dist';

    public $css = [
        'css/bootstrap-select.min.css',
    ];

    public $js = [
        'js/bootstrap-select.min.js',
        'js/i18n/defaults-es_ES.js',
    ];

    public $depends = [
        'yii\bootstrap4\BootstrapPluginAsset',
    ];
}
