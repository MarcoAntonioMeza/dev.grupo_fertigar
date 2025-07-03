<?php

namespace app\models\producto;

use Yii;
use app\models\user\User;
use yii\helpers\ArrayHelper;
use app\models\sucursal\Sucursal;
use app\models\proveedor\Proveedor;
use app\models\esys\EsysListaDesplegable;
use yii\web\UploadedFile;


/**
 * This is the model class for table "producto".
 *
 * @property int $id ID
 * @property string $clave Clave
 * @property string $nombre Nombre
 * @property string|null $descripcion Descripcion

 * @property int $categoria_id Categoria ID
 * @property int|null $proveedor_id Proveedor ID
 * @property int $almacen_id Almacen ID
 * @property int $seccion_id Seccion
 * @property int|null $inventariable Inventariable
 * @property float $costo Costo
 * @property float $precio_publico Precio publico
 * @property float $precio_mayoreo Precio mayoreo
 * @property float|null $precio_menudeo Precio menudeo
 * @property int|null $descuento Descuento
 * @property int|null $stock_minimo Stock minimo
 * @property int $status Estatus
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 *
 * @property Sucursal $almacen
 * @property EsysListaDesplegable $categoria
 * @property User $createdBy
 * @property Proveedor $proveedor
 * @property EsysListaDesplegable $seccion
 * @property User $updatedBy
 * @property Unidadsat $unidadMedida
 */
class Producto extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 1;

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Inhabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];


    const TIPO_SUBPRODUCTO   = 20;
    const TIPO_PRODUCTO      = 10;

    public static $tipoProductoList = [
        self::TIPO_PRODUCTO => 'PRODUCTO',
        self::TIPO_SUBPRODUCTO   => 'SUB PRODUCTO',
        //self::STATUS_DELETED  => 'Eliminado'
    ];
/*

    const PERTENECE_MOJARRA_FRESCA      = 10;
    const PERTENECE_CAMARON_FRESCO      = 20;
    const PERTENECE_RESTOS_PESCADO      = 30;
    const PERTENECE_RESTOS_MARISCO      = 40;
    const PERTENECE_PESCADO_CONGELADO   = 50;
    const PERTENECE_MARISCO_CONGELADO   = 60;
    const PERTENECE_ESPECIALIDADES      = 70;

    public static $perteneceList = [
        self::PERTENECE_MOJARRA_FRESCA      => 'MOJARRA FRESCA',
        self::PERTENECE_CAMARON_FRESCO      => 'CAMARÓN FRESCO',
        self::PERTENECE_RESTOS_PESCADO      => 'RESTO DE PESCADOS FRESCOS',
        self::PERTENECE_RESTOS_MARISCO      => 'RESTO DE MARISCOS FRESCOS',
        self::PERTENECE_PESCADO_CONGELADO   => 'PESCADOS CONGELADOS',
        self::PERTENECE_MARISCO_CONGELADO   => 'MARISCO CONGELADOS',
        self::PERTENECE_ESPECIALIDADES      => 'ESPECIALIDADES',
    ];*/



    #const TIPO_FRESCO = 10;
    #const TIPO_CONGELADO = 20;
#
#
    #public static $tipoList = [
    #    self::TIPO_FRESCO   => 'Fresco',
    #    self::TIPO_CONGELADO => 'Congelado',
    #    //self::STATUS_DELETED  => 'Eliminado'
    #];
#
#
    #const MEDIDA_PZ = 10;
    #const MEDIDA_KILO = 20;
#
#
    #public static $medidaList = [
    #    self::MEDIDA_PZ   => 'Piezas',
    #    self::MEDIDA_KILO => 'Kilogramos',
    #    //self::STATUS_DELETED  => 'Eliminado'
    #];

    const INV_SI = 10;
    const INV_NO = 20;

    const IS_APP_ON = 10;

    const VALIDATE_ON   = 10;
    const VALIDATE_OFF  = 20;

    public static $validateList = [
        self::VALIDATE_ON   => 'VALIDADO',
        self::VALIDATE_OFF => 'SIN VALIDAR',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    public static $invList = [
        self::INV_SI   => 'SI',
        self::INV_NO => 'NO',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    public $imageFile;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'producto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clave', 'nombre', 'categoria_id', 'precio_publico', 'status'], 'required'],
            [['descripcion'], 'string'],
            [['unidad_medida_id', 'categoria_id', 'inventariable', 'descuento', 'stock_minimo', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'is_subproducto', 'sub_producto_id', 'sub_cantidad_equivalente', 'is_app', 'validate', 'validate_user_by', 'validate_create_at'], 'integer'],
            [[
                'costo',
                'precio_publico',
                'precio_mayoreo',
                'precio_menudeo',
                'peso_aprox',
                'precio_sub',
                'comision_publico' ,
                'comision_mayoreo',
                'comision_sub',
                'iva',
                'ieps',
            ], 'number'],
            [['imageFile'], 'file', 'extensions' => 'png, jpg'],
            [['clave'], 'string', 'max' => 8, 'min' => 8],
            [['nombre'], 'string', 'max' => 150],
            [['clave'], 'unique'],
            [['is_app'], 'default', 'value' => self::VALIDATE_OFF],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['categoria_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['unidad_medida_id'], 'exist', 'skipOnError' => true, 'targetClass' => Unidadsat::className(), 'targetAttribute' => ['unidad_medida_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'precio_sub' => 'Precio Subdistribuidor',
            'comision_sub' => 'Comisión Subdistribuidor',
            'precio_publico' => 'Precio Publico',
            'comision_publico' => 'Comisión Publico',
            'precio_mayoreo' => 'Precio Mayoreo',
            'comision_mayoreo' => 'Comisión Mayoreo',
            'iva' => 'IVA',
            'ieps' => 'IEPS',
            'clave' => 'Clave',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            
            'peso_aprox' => 'Peso Aproximado por unidad {kg}',
            
            'imageFile' => 'Imagen',
            
            'categoria_id' => 'Categoria',
            'proveedor_id' => 'Proveedor',
            'almacen_id' => 'Almacen',
            'seccion_id' => 'Sección',
            'sub_cantidad_equivalente' => 'Cantidad',
            'sub_producto_id' => 'Sub producto',
            'is_subproducto' => '¿ Sub producto ?',
            'inventariable' => '¿ Inventariable ?',
            'costo' => 'COSTO PROMEDIO',
            'precio_publico' => 'Precio Publico',
            'precio_mayoreo' => 'Precio Mayoreo',
            'precio_menudeo' => 'Precio Menudeo',
            'validate_user_by' => 'Validado por:',
            'descuento' => 'Descuento',
            'stock_minimo' => 'Stock Minimo',
            'status' => 'Estatus',
            'unidad_medida_id' => 'Unidad Medida',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getItems()
    {
        $model = self::find()
            ->select(['id', 'nombre', 'clave'])
            ->where(["and", ['=', 'status', self::STATUS_ACTIVE], ['<>', 'is_subproducto', self::TIPO_SUBPRODUCTO]])
            ->orderBy('nombre');

        return ArrayHelper::map($model->all(), 'id', function ($value) {
            return $value->nombre . ' [' . $value->clave . ']';
        });
    }

    /**
     * Gets query for [[Almacen]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlmacen()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'almacen_id']);
    }

    /**
     * Gets query for [[Categoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'categoria_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }


    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubProducto()
    {
        return $this->hasOne(Producto::className(), ['id' => 'sub_producto_id']);
    }

    /**
     * Gets query for [[Proveedor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProveedor()
    {
        return $this->hasOne(Proveedor::className(), ['id' => 'proveedor_id']);
    }

    /**
     * Gets query for [[Proveedor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getValidadoPor()
    {
        return $this->hasOne(User::className(), ['id' => 'validate_user_by']);
    }

    /**
     * Gets query for [[Seccion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeccion()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'seccion_id']);
    }



    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }


    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnidadMedida()
    {
        return $this->hasOne(Unidadsat::className(), ['id' => 'unidad_medida_id']);
    }



    public function upload()
    {
        $this->imageFile->saveAs('uploads/' . $this->clave . '.' . $this->imageFile->extension);
        return true;
    }

    public static function generateClave()
    {
        $clave = "";
        $is_true = true;

        while ($is_true) {

            $caracteres = "1234567890";
            $clave      = "";
            $longitud   = 8;

            for ($i = 0; $i < $longitud; $i++) {

                $clave .= $caracteres[rand(0, strlen($caracteres) - 1)];
            }

            $valid = Producto::findOne(["clave" => $clave]);

            if (!isset($valid->id))
                $is_true = false;
        }

        return $clave;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : $this->created_by;
            } else {

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            }

            if ($this->imageFile && $this->upload()) {
                $this->avatar = $this->clave . '.' . $this->imageFile->extension;
            }

            return true;
        } else
            return false;
    }
}
