<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class BootstrapValidatorAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrapvalidator/dist';

    public $css = [
    ];

    public $js = [
        'js/bootstrapValidator.min.js',
    ];

    public $depends = [
    ];
}
