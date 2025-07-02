<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class BootboxAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootbox/dist';

    public $css = [
    ];

    public $js = [
        'bootbox.min.js',
    ];

    public $depends = [
    ];
}
