<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class FastClickAsset extends AssetBundle
{
    public $sourcePath = '@bower/fastclick/lib';

    public $css = [
    ];

    public $js = [
        'fastclick.js',
    ];

    public $depends = [
    ];
}
