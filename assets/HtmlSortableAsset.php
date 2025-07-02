<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class HtmlSortableAsset extends AssetBundle
{
    public $sourcePath = '@bower/html.sortable/dist';

    public $css = [
    ];

    public $js = [
        'html5sortable.min.js',
    ];


    public $depends = [
        'yii\web\JqueryAsset',
    ];
}