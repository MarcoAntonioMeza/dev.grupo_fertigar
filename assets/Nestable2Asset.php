<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class Nestable2Asset extends AssetBundle
{
    public $sourcePath = '@bower/nestable2';

    public $css = [
        'dist/jquery.nestable.min.css',
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
        'dist/jquery.nestable.min.js',
    ];

    public $depends = [
    ];
}
