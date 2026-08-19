<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $school_id
 * @property string $device_uid
 * @property string|null $label
 * @property string $status
 * @property string|null $last_synced_at
 * @property string $created_at
 */
class PosDevices extends ActiveRecord
{
    public static function tableName()
    {
        return 'pos_devices';
    }

    public function rules()
    {
        return [
            [['school_id', 'device_uid'], 'required'],
            [['school_id'], 'integer'],
            [['device_uid', 'label'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 20],
            [['last_synced_at', 'created_at'], 'safe'],
        ];
    }
}