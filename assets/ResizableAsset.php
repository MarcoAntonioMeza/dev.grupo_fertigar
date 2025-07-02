<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class ResizableAsset extends AssetBundle
{
    public $sourcePath = '@bower/col-resizable';

    public $css = [
    ];

    public $js = [
        'colResizable-1.6.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}