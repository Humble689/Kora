<?php

namespace app\models;

use Yii;

/**
 * 
 *
 * @property int $id
 * @property int|null $student_id
 * @property float $amount
 * @property string $transaction_type
 * @property string $payment_channel
 * @property string $external_reference
 * @property string|null $status
 * @property string|null $created_at
 *
 * @property Students $student
 */
class Transactions extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transactions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['student_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'SUCCESS'],
            [['student_id'], 'default', 'value' => null],
            [['student_id'], 'integer'],
            [['amount', 'transaction_type', 'payment_channel', 'external_reference'], 'required'],
            [['amount'], 'number'],
            [['created_at'], 'safe'],
            [['transaction_type', 'status'], 'string', 'max' => 20],
            [['payment_channel'], 'string', 'max' => 50],
            [['external_reference'], 'string', 'max' => 100],
            [['external_reference'], 'unique'],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => Students::class, 'targetAttribute' => ['student_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student ID',
            'amount' => 'Amount',
            'transaction_type' => 'Transaction Type',
            'payment_channel' => 'Payment Channel',
            'external_reference' => 'External Reference',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }

    
    public function getStudent()
    {
        return $this->hasOne(Students::class, ['id' => 'student_id']);
    }

    public function getSchool()
{
    return $this->hasOne(Schools::class, ['id' => 'school_id']);
}

public function getDevice()
{
    return $this->hasOne(PosDevices::class, ['id' => 'device_id']);
}

}
