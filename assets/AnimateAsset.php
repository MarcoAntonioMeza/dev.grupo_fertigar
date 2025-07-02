<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class AnimateAsset extends AssetBundle
{
    public $sourcePath = '@bower/animate.css';

    public $css = [
        'animate.min.css',
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
    ];

    public $depends = [
    ];
}
