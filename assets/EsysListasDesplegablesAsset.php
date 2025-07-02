<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class EsysListasDesplegablesAsset extends AssetBundle
{
    public $sourcePath = '@my_assets/esystems';

    public $css = [
    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
        'esysListasDesplegables.jquery.js',
    ];

    public $depends = [
        'app\assets\HtmlSortableAsset',
    ];
}
