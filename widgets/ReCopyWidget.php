<?php
namespace app\widgets;

use Yii;
use yii\base\Component;
use yii\base\Widget;
use yii\web\View;
use yii\helpers\Html;
use app\assets\ReCopyAppAsset;

class ReCopyWidget extends Widget {

    public $targetClass = 'clone'; //Target CSS class target for duplicate
    public $limit = 0; //The number of allowed copies. Default: 0 is unlimited
    public $addButtonId; // Add button id. Set id differently if this widget is called multiple times per page.
    public $addButtonLabel = 'Add more'; //Add button text.
    public $addButtonCssClass = 'btn btn-success'; //Add button CSS class.
    public $removeButtonLabel = 'Remove'; //Remove button text
    public $removeButtonCssClass = 'btn  btn-danger  recopy-remove'; //Remove button CSS class.

    public $excludeSelector; //A jQuery selector used to exclude an element and its children
    public $copyClass; //A class to attach to each copy
    public $clearInputs; //Boolean Option to clear each copies text input fields or textarea
    public $style = [];
    private $_assetsUrl;

    /**
     * Initializes the widgets
     */
    public function init() {
        parent::init();
        if ($this->_assetsUrl === null) {
            $assetsDir = Yii::getAlias('@webroot'). DIRECTORY_SEPARATOR . 'js'. DIRECTORY_SEPARATOR . 'reCopy-js';  //change assets directory path as per your requirement.
            $this->_assetsUrl = Yii::$app->assetManager->publish($assetsDir);
        }

        $this->addButtonId = trim($this->addButtonId);
        if(empty($this->addButtonId))
            $this->addButtonId='recopy-add';

        if($this->limit)
            $this->limit= (is_numeric($this->limit) && $this->limit > 0) ? (int)ceil($this->limit) : 0;

    }

    /**
     * Execute the widgets
     */
    public function run() {
        if($this->limit==1) return ;

		ReCopyAppAsset::register($this->getView());

        $this->getView()->registerJs('
                $(function(){
                    var removeLink = \'<td><a class="'.$this->removeButtonCssClass.'" href="#" onclick="$(this).parent().parent().remove(); return false;">'.$this->removeButtonLabel.'</a></td>\';
                    $("a#'.$this->addButtonId.'").relCopy({'.implode(', ', array_filter([
                        empty($this->excludeSelector) ? '' : 'excludeSelector: "'.$this->excludeSelector.'"',
                        empty($this->limit) ? '': 'limit: '.$this->limit,
                        empty($this->copyClass) ? '' : 'copyClass: "'.$this->copyClass.'"',
                        $this->clearInputs === true ? 'clearInputs: true' : '',
                        $this->clearInputs === false ? 'clearInputs: false' : '',
                        'append: removeLink',
                    ])).'});
                });', View::POS_END);

            echo Html::a($this->addButtonLabel, '#', [
                'id' => $this->addButtonId,
                'rel' => '.'.$this->targetClass,
                'class' => $this->addButtonCssClass,
				'style' => $this->style]
            );
    }
}//end class
