<?php
namespace app\modules\v1\controllers;

use Yii;
use app\models\cliente\ClientePaquete;
use app\models\Esys;

class SystemController extends DefaultController
{
    public function actionFechaYHora()
    {
        return (new \DateTime())->format('Y-m-d\TH\:i\:s');
    }

    public function actionEstatusPaquete()
    {
    	$request = Yii::$app->request;
    	$post = $request->post();

    	$ClientePaquete = ClientePaquete::find()
    	->select([
    		'cliente_id',
    		'tipo',
    		'creditos',
    		'creditos_usados',
    		'creditos_limite',
    		'status',
    		'created_at',
    		'updated_at',
    	])
    	->where([
		  'cliente_id' => Yii::$app->user->identity->id,
		  'token'      => $post['token']
    	])->one();


    	// Existe una consistencia
        if($ClientePaquete){
			switch ($ClientePaquete['tipo']) {
				case ClientePaquete::TIPO_PRE_PAGO:
					return [
			    		'cliente_id' 	  => $ClientePaquete['cliente_id'],
			    		'tipo' 			  => ClientePaquete::$tipoList[$ClientePaquete['tipo']],
			    		'creditos' 		  => $ClientePaquete['creditos'],
			    		'creditos_usados' => $ClientePaquete['creditos_usados'],
			    		'creditos_disponibles' => $ClientePaquete['creditos'] - $ClientePaquete['creditos_usados'],
			    		'status' 		  => ClientePaquete::$statusList[$ClientePaquete['status']],
			    		'creado' 	      => Esys::unixTimeToString($ClientePaquete['created_at'], 'Y-m-d\TH\:i\:s'),
			    		'modificado'      => Esys::unixTimeToString($ClientePaquete['updated_at'], 'Y-m-d\TH\:i\:s'),
			    	];

				case ClientePaquete::TIPO_POR_CONSUMO:
					return [
			    		'cliente_id' 	  => $ClientePaquete['cliente_id'],
			    		'tipo' 			  => ClientePaquete::$tipoList[$ClientePaquete['tipo']],
			    		'creditos_usados' => $ClientePaquete['creditos_usados'],
			    		'creditos_limite' => $ClientePaquete['creditos_limite'],
			    		'status' 		  => ClientePaquete::$statusList[$ClientePaquete['status']],
			    		'creado' 	      => Esys::unixTimeToString($ClientePaquete['created_at'], 'Y-m-d\TH\:i\:s'),
			    		'modificado'      => Esys::unixTimeToString($ClientePaquete['updated_at'], 'Y-m-d\TH\:i\:s'),
			    	];
			}

    	}

        // No existe el token
        return [
            'errno'   => 10,
            'message' => "Token invalido o inexistente."
        ];
    }

}
