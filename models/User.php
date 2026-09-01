<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'system_admins';
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword(
            $password,
            $this->password_hash
        );
    }

    public function generatePasswordResetToken(): void
{
    $this->password_reset_token = Yii::$app->security->generateRandomString(48);
    $this->password_reset_expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour
}

public static function findByPasswordResetToken(string $token): ?self
{
    $user = static::findOne(['password_reset_token' => $token]);

    if (!$user || strtotime($user->password_reset_expires_at) < time()) {
        return null; 
    }

    return $user;
}

public function clearPasswordResetToken(): void
{
    $this->password_reset_token = null;
    $this->password_reset_expires_at = null;
}

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getSchool()
{
    return $this->hasOne(Schools::class, ['id' => 'school_id']);
}

}