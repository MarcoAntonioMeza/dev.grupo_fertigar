<?php
/* @var $this yii\web\View */

use app\models\auth\AuthItem;
use yii\helpers\Html;

$this->title = 'Permisos de usuario';
?>

<div class="site-permisos">
    <!-- ************************** -->
    <!-- Permisos del usuario logueado -->
    <!-- ************************** -->
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-5 col-sm-offset-1">
                    <table class="table table-hover">
                        <tr>
                            <th>Perfil</th>
                            <th class="text-center">Permitido</th>
                        </tr>
                    <?php foreach (AuthItem::getItems(['type' => 1]) as $item_name): ?>
                        <tr>
                            <td><?= $item_name->name ?></td>
                            <td class="text-center"><?= Yii::$app->user->can($item_name->name)? '<i class="fa fa-check"></i>': '' ?></td>
                        </tr>
                    <?php endforeach ?>
                    </table>
                </div>
                <div class="col-sm-5 col-sm-offset-1">
                    <table class="table table-hover">
                        <tr>
                            <th>Permiso</th>
                            <th class="text-center">Permitido</th>
                        </tr>
                    <?php foreach (AuthItem::getItems(['type' => 2]) as $item_name): ?>
                        <tr>
                            <td><?= $item_name->name ?></td>
                            <td class="text-center"><?= Yii::$app->user->can($item_name->name)? '<i class="fa fa-check"></i>': '' ?></td>
                        </tr>
                    <?php endforeach ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
