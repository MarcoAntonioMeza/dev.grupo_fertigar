<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class BootstrapWizardAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-wizard';

    public $css = [
    ];

    public $js = [
        'jquery.bootstrap.wizard.min.js',
    ];

    public $depends = [
    	'app\assets\WizardAsset'
    ];
}
