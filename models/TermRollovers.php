<?php
namespace app\models;
use yii\db\ActiveRecord;

class TermRollovers extends ActiveRecord
{
    public static function tableName() { return 'term_rollovers'; }
    public function getPerformedBy() { return $this->hasOne(User::class, ['id' => 'performed_by']); }
    public function getReversedBy() { return $this->hasOne(User::class, ['id' => 'reversed_by']); }
    public function getDetails() { return $this->hasMany(TermRolloverDetails::class, ['term_rollover_id' => 'id']); }
}