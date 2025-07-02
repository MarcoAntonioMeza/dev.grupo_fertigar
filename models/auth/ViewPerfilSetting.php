<?php
namespace app\models\auth;

use Yii;

/**
 * This is the model class for table "view_perfil_setting".
 *
 * @property string $area Área
 * @property string $modulo Módulo
 * @property string $names
 * @property string $grupo Grupo
 * @property string $ver
 * @property string $crear
 * @property string $editar
 * @property string $cancelar
 * @property string $eliminar
 * @property string $description Descripción
 * @property int $orden Orden
 */
class ViewPerfilSetting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'view_perfil_setting';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'area' => 'Área',
            'modulo' => 'Módulo',
            'names' => 'Names',
            'grupo' => 'Grupo',
            'ver' => 'Ver',
            'crear' => 'Crear',
            'editar' => 'Editar',
            'cancelar' => 'Cancelar',
            'eliminar' => 'Eliminar',
            'description' => 'Descripción',
            'orden' => 'Orden',
        ];
    }

}
