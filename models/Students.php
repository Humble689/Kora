<?php

namespace app\models;

use Yii;

/**
 * 
 *
 * @property int $id
 * @property int|null $school_id
 * @property string $name
 * @property string $payment_code
 * @property string $class_level
 * @property float|null $tuition_balance
 * @property float|null $swallet_balance
 * @property float|null $daily_spend_limit
 * @property string|null $created_at
 *
 * @property Schools $school
 * @property Transactions[] $transactions
 */
class Students extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'students';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['school_id'], 'default', 'value' => null],
            [['swallet_balance'], 'default', 'value' => 0.00],
            [['daily_spend_limit'], 'default', 'value' => 5000.00],
            [['school_id'], 'default', 'value' => null],
            [['school_id'], 'integer'],
            [['name', 'payment_code', 'class_level'], 'required'],
            [['tuition_balance', 'swallet_balance', 'daily_spend_limit'], 'number'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['payment_code'], 'string', 'max' => 10],
            [['class_level'], 'string', 'max' => 50],
            [['payment_code'], 'unique'],
            [['school_id'], 'exist', 'skipOnError' => true, 'targetClass' => Schools::class, 'targetAttribute' => ['school_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'school_id' => 'School ID',
            'name' => 'Name',
            'payment_code' => 'Payment Code',
            'class_level' => 'Class Level',
            'tuition_balance' => 'Tuition Balance',
            'swallet_balance' => 'Swallet Balance',
            'daily_spend_limit' => 'Daily Spend Limit',
            'created_at' => 'Created At',
        ];
    }

    /**
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSchool()
    {
        return $this->hasOne(Schools::class, ['id' => 'school_id']);
    }

    
    public function getTransactions()
    {
        return $this->hasMany(Transactions::class, ['student_id' => 'id']);
    }

    public function generateSponsorCode(): string
{
    do {
        $code = 'SPN-' . strtoupper(Yii::$app->security->generateRandomString(6));
    } while (self::find()->where(['sponsor_code' => $code])->exists());

    return $code;
}

    

}
