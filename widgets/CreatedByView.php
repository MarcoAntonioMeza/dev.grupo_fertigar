<?php
namespace app\widgets;

use yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @author Arturo Corro <arturo@elitesystems.mx>
 */
class CreatedByView extends Widget
{
    public $model;

    public function init()
    {
        parent::init();

        /*
        if ($this->message === null) {
            $this->message = 'Hello World';
        }
        */
    }

    public function run()
    {
        $created_by = $this->model->created_by?
            Html::a($this->model->createdBy->nombre . ' '. $this->model->createdBy->apellidos . ' [' . $this->model->created_by . ']', ['/admin/user/view', 'id' => $this->model->created_by], ['class' => 'text-primary']):
            null;

        $updated_by = isset($this->model->updated_by) && $this->model->updated_by?
            Html::a($this->model->updatedBy->nombre . ' '. $this->model->updatedBy->apellidos . ' [' . $this->model->updated_by . ']', ['/admin/user/view', 'id' => $this->model->updated_by], ['class' => 'text-primary']):
            null;

        return  DetailView::widget([
            'model' => $this->model,
            'attributes' => [
                'created_at:datetime',
                [
                    'attribute' => 'created_by',
                    'format'    => 'raw',
                    'value'     => $created_by,
                ],
                'updated_at:datetime' ,
                [
                    'attribute' => 'updated_by',
                    'format'    => 'raw',
                    'value'     => $updated_by,
                ],
            ]
        ]);
    }

}
