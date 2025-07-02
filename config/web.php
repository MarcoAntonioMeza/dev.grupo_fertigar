<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'panelGrupoFertigar',
    'name'      => "Grupo Fertigar",
    'version'   => '0.1.0',
    'language'  => 'es-MX',
    'timeZone'  => 'America/Mexico_City',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'app\components\Aliases'],
    /*'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],*/
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'crm' => [
            'class' => 'app\modules\crm\Module',
        ],
        'creditos' => [
            'class' => 'app\modules\creditos\Module',
        ],
        'productos' => [
            'class' => 'app\modules\productos\Module',
        ],
        'caja' => [
            'class' => 'app\modules\caja\Module',
        ],
        'inventario' => [
            'class' => 'app\modules\inventario\Module',
        ],
        'tpv' => [
            'class' => 'app\modules\tpv\Module',
        ],
        'sucursales' => [
            'class' => 'app\modules\sucursales\Module',
        ],
        'logistica' => [
            'class' => 'app\modules\logistica\Module',
        ],
        'compras' => [
            'class' => 'app\modules\compras\Module',
        ],
        'reportes' => [
            'class' => 'app\modules\reportes\Module',
        ],
        'almacenes' => [
            'class' => 'app\modules\almacenes\Module',
        ],
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'contabilidad' => [
            'class'=>'app\modules\contabilidad\Module'
        ]
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'nteSskI2Fd9kGJzWokwYRhv37Wx8oH5V',

            /* IMPLEMENTACION PARA WEB SERVICE*/
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
            /**/
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'jsOptions' => [
                        'position' => \yii\web\View::POS_HEAD
                    ],
                    'js' => [
                        'jquery.min.js',
                    ],
                ],
                'yii\bootstrap4\BootstrapAsset' => [
                    'css' => [
                        'css/bootstrap.min.css',
                    ]
                ],
                'yii\bootstrap4\BootstrapPluginAsset' => [
                    'js' => [
                        'js/bootstrap.min.js',
                    ]
                ],
                'kartik\form\ActiveFormAsset' => [
                    'bsDependencyEnabled' => false // do not load bootstrap assets for a specific asset bundle
                ],
            ],
        ],
        'nifty'=> [
            'class' => 'app\components\NiftyComponent',
        ],
        'user' => [
            'identityClass' => 'app\models\user\UserIdentity',
            'enableAutoLogin' => true,
            'loginUrl'      => array('admin/user/login'),
        ],
        /*'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],*/
        'session' => [
            'class' => 'yii\web\Session',
            'savePath' => '@app/runtime/session'
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache',
        ],
         'formatter' => [
            'class'           => 'yii\i18n\Formatter',
            'dateFormat'      => 'php:Y-m-d',
            'datetimeFormat'  => 'php:Y-m-d h:i a',
            'timeFormat'      => 'php:h:i a',
            'defaultTimeZone' => 'America/Mexico_City',
            'locale'          => 'es-MX',
        ],
        /*'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/translations',
                    'sourceLanguage' => 'es-MX',
                ],
                'yii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/translations',
                    'sourceLanguage' => 'es-MX'
                ],
            ],
        ],*/
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => 'pescadosymariscosarroyo.com',
                'username'   => 'sistema@pescadosymariscosarroyo.com',
                'password'   => 'LS:sistema*123',
                'port'       => 25,
                'encryption' => false,
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            //'suffix' => '.html',
            'rules' => [
                '' => 'site/index',
                '<action:\w+>'=>'site/<action>',
            ],
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
