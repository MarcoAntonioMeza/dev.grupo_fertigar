<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ReCopyAppAsset extends AssetBundle
{
    public $basePath = '@webroot/js/reCopy-js';
    public $baseUrl = '@web/js/reCopy-js';
    public $css = [

    ];
    public $js = [
		'reCopy.js',
    ];
    public $jsOptions = [
         'position' => \yii\web\View::POS_HEAD
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}
//<?= app\widgets\ReCopyWidget::widget(['targetClass'=>'clone', 'addButtonLabel' => 'AGREGAR DIRECCION', 'removeButtonLabel' => 'Eliminar' ]) ?>