<?php
namespace app\modules\admin\controllers;

use Yii;

class DashboardController extends \app\controllers\AppController
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
