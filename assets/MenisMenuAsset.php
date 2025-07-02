<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class MenisMenuAsset extends AssetBundle
{
    public $sourcePath = '@vendor/onokumus/metismenu';


    public $js = [
        'dist/metisMenu.min.js',
    ];


    public $depends = [
        'yii\web\YiiAsset',
    ];
}
