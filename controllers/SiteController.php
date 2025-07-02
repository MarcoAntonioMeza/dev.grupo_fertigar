<?php
namespace app\controllers;

use Yii;

class SiteController extends AppController
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if(!isset(Yii::$app->user->identity))
            return $this->renderPartial('index-main');
        else
            return $this->render('index');
    }

    public function actionPermisos()
    {
        return $this->render('permisos');
    }

    public function actionAcercaDe()
    {
        return $this->render('acerca-de');
    }
}
