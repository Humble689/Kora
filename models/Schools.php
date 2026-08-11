<?php

namespace app\models;

use Yii;


class Schools extends \yii\db\ActiveRecord
{


   
    public static function tableName()
    {
        return 'schools';
    }

   

    public $admin_username;
    public $admin_password;

    public function rules()
    {
        return [
            [['name', 'bank_account', 'base_tuition_fees', 'contact_email', 'admin_username', 'admin_password'], 'required'],
            ['contact_email', 'email'],
            ['base_tuition_fees', 'number'],
            [['name', 'bank_account', 'admin_username'], 'string', 'max' => 150],
            ['admin_password', 'string', 'min' => 6],
            [['admin_username', 'admin_password'], 'safe'],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'bank_account' => 'Bank Account',
            'created_at' => 'Created At',
        ];
    }

    public function getStudents()
    {
        return $this->hasMany(Students::class, ['school_id' => 'id']);
    }

}
