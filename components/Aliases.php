<?php
namespace app\components;

use Yii;
use yii\base\Component;

/**
 * Component where you can define your aliases.
 * 
 * This component is bootstrap-ed in your web.php configuration file.
 * It is good to make aliases here so we can use predefined aliases 
 * and other settings made by application configuration.
 *
 * @author Nenad Zivkovic <nenad@freetuts.org>
 * @since 2.3.0 <improved template version>
 */
class Aliases extends Component
{
    public function init() 
    {
        Yii::setAlias('@bower', 	Yii::getAlias('@vendor').'/bower-asset/');
        Yii::setAlias('@npm', 		Yii::getAlias('@vendor').'/npm-asset/');
        Yii::setAlias('@my_assets', Yii::getAlias('@vendor').'/my_assets/');
    }
}