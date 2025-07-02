<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class EsysPerfilesAsset extends AssetBundle
{
    public $sourcePath = '@my_assets/esystems';

    public $css = [
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
        'esysPerfiles.jquery.js',
    ];

    public $depends = [
    ];
}
