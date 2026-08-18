<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "teacher_assignment".
 *
 * @property int $id
 * @property int $school_id
 * @property int $teacher_id
 * @property string $class_level
 * @property string $subject_name
 */
class TeacherAssignment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'teacher_assignments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['school_id', 'teacher_id', 'class_level', 'subject_name'], 'required'],
            [['school_id', 'teacher_id'], 'integer'],
            [['class_level', 'subject_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'school_id' => 'School',
            'teacher_id' => 'Teacher',
            'class_level' => 'Class Level',
            'subject_name' => 'Subject',
        ];
    }

    /**
     * The teacher this assignment belongs to.
     * ADAPT: swap `User` for your actual identity model class if different
     * (matches whatever class Yii::$app->user->identity resolves to).
     */
    public function getTeacher()
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }
}