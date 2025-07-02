<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class NiftyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        //'//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700',
        //'css/open-sans.css',
        //'css/nifty.min.css',
        //'css/demo/nifty-demo-icons.min.css',
        'font-awesome/css/font-awesome.css',
        //'css/nifty_icon_premium/line-icons/premium-line-icons.min.css',
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
        'js/jquery.metisMenu.js',
        'js/jquery.slimscroll.min.js',
        'js/inspinia.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\PaceAsset',
        'app\assets\BootstrapSelectAsset',
        'app\assets\FastClickAsset',
        'app\assets\FontAwesomeAsset',
        'app\assets\IoniconsAsset',
        'app\assets\MdiAsset',
        'app\assets\MagicCheckAsset',
        'app\assets\AnimateAsset',
        //'app\assets\MenisMenuAsset',
    ];
}
