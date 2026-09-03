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
    public $email;

    public $teacher_class;
    public $teacher_subject;

    /** @var array existing pos_devices.id to assign this CANTEEN staff member to */
    public $pos_device_ids = [];

    /** @var string|null optional - type a label here to register a brand-new device on the spot */
    public $new_device_label;

    /** @var string|null set by the controller before validate()/signup() run */
    public $currentUserRole;

    public function rules(): array
    {
        return [
            [['username', 'password', 'role', 'school_id', 'email'], 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 150],
            ['username', 'string', 'min' => 4, 'max' => 50],
            ['password', 'string', 'min' => 6, 'max' => 100],
            ['role', 'in', 'range' => ['SCHOOL_ADMIN', 'BURSAR', 'CANTEEN', 'TEACHER', 'DOS']],
            ['role', 'validateRoleEscalation'],
            ['school_id', 'integer'],
            [['pos_device_ids'], 'safe'],
            [['new_device_label'], 'string', 'max' => 100],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username is taken.'],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'This email is already registered.'],

            [['teacher_class', 'teacher_subject'], 'required', 'when' => function ($model) {
                return $model->role === 'TEACHER';
            }, 'whenClient' => "function (attribute, value) { return $('#roleSelectorField').val() === 'TEACHER'; }"],
        ];
    }

    public function validateRoleEscalation($attribute, $params): void
    {
        if ($this->role === 'SCHOOL_ADMIN' && $this->currentUserRole !== 'SUPER_ADMIN') {
            $this->addError($attribute, 'You are not authorized to assign the School Administrator role.');
        }
    }

    public function signup(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->role !== 'TEACHER') {
            $this->teacher_class = null;
            $this->teacher_subject = null;
        }
        if ($this->role !== 'CANTEEN') {
            $this->pos_device_ids = [];
            $this->new_device_label = null;
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $admin = new User();
            $admin->username = $this->username;
            $admin->email = $this->email;
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

                    if ($this->role === 'CANTEEN') {
                $deviceIds = array_map('intval', $this->pos_device_ids ?: []);

                if (!empty(trim((string) $this->new_device_label))) {
                    $newDevice = new PosDevices();
                    $newDevice->school_id = $this->school_id;
                    $newDevice->label = trim($this->new_device_label);
                    $newDevice->status = 'ACTIVE';
                    $newDevice->assigned_staff_id = $admin->id;   // set directly, no join table needed

                    if (!$newDevice->save()) {
                        throw new \Exception('Failed to register new POS device: ' . implode(' ', $newDevice->getFirstErrors()));
                    }

                    $deviceIds[] = $newDevice->id;
                }

                if (!empty($deviceIds)) {
                    Yii::$app->db->createCommand()->update(
                        'pos_devices',
                        ['assigned_staff_id' => $admin->id],
                        ['id' => $deviceIds]  
                    )->execute();
                }
            }
            $dbTransaction->commit();
            return true;

        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            return false;
        }
    }
}