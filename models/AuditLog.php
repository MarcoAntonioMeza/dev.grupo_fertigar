<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "audit_log".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $module
 * @property string|null $action
 * @property string|null $request_method
 * @property string|null $request_url
 * @property string|null $request_data
 * @property string|null $response_data
 * @property int $created_at
 */
class AuditLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'audit_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['request_url', 'request_data', 'response_data'], 'string'],
            [['module'], 'string', 'max' => 100],
            [['action'], 'string', 'max' => 255],
            [['request_method'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'module' => 'Module',
            'action' => 'Action',
            'request_method' => 'Request Method',
            'request_url' => 'Request Url',
            'request_data' => 'Request Data',
            'response_data' => 'Response Data',
            'created_at' => 'Created At',
        ];
    }
}
