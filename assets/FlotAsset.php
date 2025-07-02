<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class FlotAsset extends AssetBundle
{
    public $sourcePath = '@bower/flot';

    public $css = [
        //'css/highcharts.css',
    ];

    public $js = [

        'source/jquery.flot.js',
        'source/jquery.flot.tooltip.min.js',
        'source/jquery.flot.spline.js',
        'source/jquery.flot.resize.js',
        'source/jquery.flot.pie.js',
        'source/jquery.flot.symbol.js',
        'source/jquery.flot.time.js',



    ];



}
