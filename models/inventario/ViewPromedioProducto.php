<?php
namespace app\models\inventario;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use app\models\user\User;
use app\models\esys\EsysCambioLog;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysDireccion;

class ViewPromedioProducto extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_tres_ultimos_productos';
    }


}
