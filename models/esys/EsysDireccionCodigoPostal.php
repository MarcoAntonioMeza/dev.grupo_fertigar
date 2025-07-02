<?php

namespace app\models\esys;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "esys_direccion_codigo_postal".
 *
 * @property int $id ID
 * @property int $codigo_postal Codigo postal
 * @property string $colonia Colonia
 * @property string $tipo_colonia Tipo de colonia
 * @property string $zona Zona
 * @property int $municipio_id Municipio ID
 * @property int $estado_id Estado ID
 *
 * @property EsysDireccion[] $esysDireccions
 */
class EsysDireccionCodigoPostal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'esys_direccion_codigo_postal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo_postal', 'municipio_id', 'estado_id'], 'integer'],
            [['colonia'], 'string', 'max' => 150],
            [['tipo_colonia'], 'string', 'max' => 100],
            [['zona'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo_postal' => 'Codigo postal',
            'colonia' => 'Colonia',
            'tipo_colonia' => 'Tipo de colonia',
            'zona' => 'Zona',
            'municipio_id' => 'Municipio ID',
            'estado_id' => 'Estado ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEsysDireccions()
    {
        return $this->hasMany(EsysDireccion::className(), ['codigo_postal_id' => 'id']);
    }


    public static function getEstadoMunicipiosColonia($params = [])
    {
        $params['codigo_postal'] = array_key_exists('codigo_postal', $params)? $params['codigo_postal']: false;
        $params['estado_id']     = array_key_exists('estado_id', $params)? $params['estado_id']: false;
        $params['municipio_id']  = array_key_exists('municipio_id', $params)? $params['municipio_id']: false;

        $EsysDireccionCodigoPostal = (new Query())
            ->select([
                'esys_direccion_codigo_postal.id',
                'codigo_postal',
                'colonia',
                'estado.id_2 as estado_id',
                'estado.singular as estado',
                'municipio.id_2 as municipio_id',
                'municipio.singular as municipio',
            ])
            ->from(self::tableName())
            ->innerjoin('esys_lista_desplegable as estado', "estado.id_2  = `esys_direccion_codigo_postal`.`estado_id` and estado.label = 'crm_estado'")
            ->innerjoin('esys_lista_desplegable as municipio', "municipio.id_2 = `esys_direccion_codigo_postal`.municipio_id and  municipio.param1 =  `esys_direccion_codigo_postal`.`estado_id` and municipio.label = 'crm_municipio' ");

        if($params['codigo_postal'])
            $EsysDireccionCodigoPostal->andWhere(['codigo_postal' => $params['codigo_postal']]);

        if($params['estado_id'])
            $EsysDireccionCodigoPostal->andWhere(['estado_id' => $params['estado_id']]);

        if($params['municipio_id'])
            $EsysDireccionCodigoPostal->andWhere(['municipio_id' => $params['municipio_id']]);


        return $EsysDireccionCodigoPostal->all();
    }

      public static function getColonia($params = [])
    {
        $params['codigo_postal'] = array_key_exists('codigo_postal', $params)? $params['codigo_postal']: false;


        $EsysDireccionCodigoPostal = (new Query())
            ->select([
                'id',
                'colonia',
            ])
            ->from(self::tableName());

        if($params['codigo_postal'])
            $EsysDireccionCodigoPostal->andWhere(['codigo_postal' => $params['codigo_postal']]);


        return ArrayHelper::map($EsysDireccionCodigoPostal->all(), 'id', 'colonia');
    }

    public function getEstado()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id_2' => 'estado_id'])->where(['label' => 'crm_estado']);
    }

    public function getMunicipio()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id_2' => 'municipio_id', 'param1'=> 'estado_id'])->where(['label' => 'crm_municipio']);
    }


}
