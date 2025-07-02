<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class MainAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'index-main/js/jquery.js',
        'index-main/js/bootstrap.min.js',
        'index-main/js/smoothscroll.js',
        'index-main/js/isotope.js',
        'index-main/js/imagesloaded.min.js',
        'index-main/js/nivo-lightbox.min.js',
        'index-main/js/jquery.backstretch.min.js',
        'index-main/js/wow.min.js',
        'index-main/js/custom.js',
    ];

    public $css = [
        'index-main/css/animate.min.css',
		'index-main/css/bootstrap.min.css',
		'index-main/css/font-awesome.min.css',
        'index-main/css/et-line-font.css',
        'index-main/css/nivo-lightbox.css',
        'index-main/nivo_themes/default/default.css',
        'index-main/css/style.css',
        'index-main/nivo_themes/default/default.css',
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];
}
