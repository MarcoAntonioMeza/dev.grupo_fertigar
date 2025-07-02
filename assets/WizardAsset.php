<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class WizardAsset extends AssetBundle
{
	public $baseUrl = '@web';
	public $basePath = '@webroot';

    public $css = [
        'wizard/css/jquery.steps.css',
    ];

    public $js = [
        'wizard/js/jquery.steps.min.js',
    ];

    public $depends = [
    ];
}
