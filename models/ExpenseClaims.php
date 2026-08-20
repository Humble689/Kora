<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $school_id
 * @property int|null $requested_by
 * @property string $category
 * @property string $description
 * @property float $amount
 * @property string $status
 * @property int|null $reviewed_by
 * @property string|null $reviewed_at
 * @property string $created_at
 */
class ExpenseClaims extends ActiveRecord
{
    public static function tableName()
    {
        return 'expense_claims';
    }

  public function rules()
{
    return [
        [['school_id', 'category', 'description', 'amount'], 'required'],
        [['school_id', 'requested_by', 'reviewed_by'], 'integer'],
        [['description'], 'string'],
        [['amount'], 'number'],
        [['category', 'status'], 'string', 'max' => 30],
        [['reference_number'], 'string', 'max' => 100],
        [['reviewed_at', 'created_at'], 'safe'],
    ];
}

    public function getRequestedBy()
    {
        return $this->hasOne(User::class, ['id' => 'requested_by']); 
    }

    public function getReviewedBy()
    {
        return $this->hasOne(User::class, ['id' => 'reviewed_by']);
    }
}