<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class HighchartsAsset extends AssetBundle
{
    public $sourcePath = '@bower/highcharts';

    public $css = [
        //'css/highcharts.css',
        'css/themes/sand-signika.css',

    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
        'highcharts.js',
        //'modules/accessibility.js',
        'modules/exporting.js',
    ];


    public $depends = [
        'yii\web\YiiAsset',
    ];
}
