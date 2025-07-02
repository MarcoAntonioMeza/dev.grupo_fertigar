<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/my_js/afterload.js',
        'js/toastr/toastr.min.js',
        'js/jquery.mask.js',
    ];

    public $css = [
        'css/style.css',
        'css/animate.css',
        //'css/esystems/theme.css',
        'css/my_css/helpers.css',
        'css/my_css/ui.css',
        'css/my_css/views.css',
        'css/toastr/toastr.min.css',
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $depends = [
        'app\assets\EsysAsset',
        'app\assets\NiftyAsset',
    ];
}
