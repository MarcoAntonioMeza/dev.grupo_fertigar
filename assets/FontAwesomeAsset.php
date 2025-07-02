<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@bower/font-awesome';

    public $css = [
    	'css/font-awesome.min.css'
    ];

    public $js = [
    ];

    public $depends = [
    ];
}
