<?php
namespace app\models;
use yii\db\ActiveRecord;

class TermRolloverDetails extends ActiveRecord
{
    public static function tableName() { return 'term_rollover_details'; }
    public function getStudent() { return $this->hasOne(Students::class, ['id' => 'student_id']); }
}