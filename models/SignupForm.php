<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;

class SignupForm extends Model
{
    public $username;
    public $password;
    public $role;
    public $school_id;
    public $email; // 💡 NEW: Email field attribute
    
    public $teacher_class;
    public $teacher_subject;

    public function rules(): array
    {
        return [
            [['username', 'password', 'role', 'school_id', 'email'], 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 150],
            ['username', 'string', 'min' => 4, 'max' => 50],
            ['password', 'string', 'min' => 6, 'max' => 100],
            ['role', 'in', 'range' => ['SCHOOL_ADMIN', 'BURSAR', 'CANTEEN', 'TEACHER', 'DOS']],
            ['school_id', 'integer'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username is taken.'],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'This email is already registered.'],
            
            [['teacher_class', 'teacher_subject'], 'required', 'when' => function($model) {
                return $model->role === 'TEACHER';
            }, 'whenClient' => "function (attribute, value) { return $('#roleSelectorField').val() === 'TEACHER'; }"],
        ];
    }

    public function signup(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $admin = new User(); 
            $admin->username = $this->username;
            $admin->email = $this->email; //  Save email to DB
            $admin->password_hash = Yii::$app->security->generatePasswordHash($this->password);
            $admin->auth_key = Yii::$app->security->generateRandomString();
            $admin->role = $this->role;
            $admin->school_id = $this->school_id;

            if (!$admin->save(false)) {
                throw new \Exception("Failed to save core user record.");
            }

            if ($this->role === 'TEACHER') {
                Yii::$app->db->createCommand()->insert('teacher_assignments', [
                    'school_id' => $this->school_id,
                    'teacher_id' => $admin->id,
                    'class_level' => $this->teacher_class,
                    'subject_name' => $this->teacher_subject,
                ])->execute();
            }

            $dbTransaction->commit();
            return true;

        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            return false;
        }
    }
}
