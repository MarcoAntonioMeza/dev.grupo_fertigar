<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class FancytreeAsset extends AssetBundle
{
    public $sourcePath = '@bower/fancytree/dist';

    public $css = [
        'skin-bootstrap/ui.fancytree.min.css',
    ];

    public $js = [
        'jquery.fancytree.min.js',
        'modules/jquery.fancytree.dnd.js',
        'modules/jquery.fancytree.glyph.js',
        'modules/jquery.fancytree.table.js',
    ];

    public $depends = [
        'yii\jui\JuiAsset',
        'yii\web\JqueryAsset',
    ];
}
