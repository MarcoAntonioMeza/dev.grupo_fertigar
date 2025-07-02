<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class IoniconsAsset extends AssetBundle
{
    public $sourcePath = '@bower/ionicons/docs';

    public $css = [
        'css/ionicons.min.css',
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
    ];

    public $depends = [
    ];
}
