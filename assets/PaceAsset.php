<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class PaceAsset extends AssetBundle
{
    public $sourcePath = '@bower/pace';

    public $css = [
        'themes/blue/pace-theme-flash.css',
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
        'pace.min.js',
    ];

    public $depends = [
    ];
}
