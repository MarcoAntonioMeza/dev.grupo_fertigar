<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class MdiAsset extends AssetBundle
{
    public $sourcePath = '@bower/mdi';

    public $css = [
        'css/materialdesignicons.min.css',
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
    ];

    public $depends = [
    ];
}
