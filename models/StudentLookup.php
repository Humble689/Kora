<?php

namespace app\models;

use Yii;
use yii\base\Model;

class StudentLookup extends Model
{
    public $payment_code;

   
    public function rules()
    {
        return [
            [['payment_code'], 'required', 'message' => 'Please enter a student payment code.'],
            [['payment_code'], 'string', 'length' => 10, 'message' => 'The payment code must be exactly 10 digits.'],
            [['payment_code'], 'match', 'pattern' => '/^[0-9]+$/', 'message' => 'The payment code must contain numbers only.'],
        ];
    }

  
    public function attributeLabels()
    {
        return [
            'payment_code' => 'Student Payment Code',
        ];
    }
}
