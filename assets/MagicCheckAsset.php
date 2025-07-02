<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class MagicCheckAsset extends AssetBundle
{
    public $sourcePath = '@bower/magic-check/css';

    public $css = [
        'magic-check.min.css',
    ];

    public $js = [
    ];


    public $depends = [
        'yii\web\JqueryAsset',
    ];
}