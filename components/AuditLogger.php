<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\Json;
use app\models\AuditLog; // Modelo para la tabla audit_log

class AuditLogger extends Component
{
    public static function log($module, $action, $requestData = [], $responseData = null)
    {
        $log = new AuditLog();
        $log->user_id = Yii::$app->user->id ?? null;
        $log->module = $module;
        $log->action = $action;
        $log->request_method = Yii::$app->request->method;
        $log->request_url = Yii::$app->request->absoluteUrl;
        $log->request_data = Json::encode($requestData);
        $log->response_data = $responseData ? Json::encode($responseData) : null;
        $log->save();
         
    }
}
