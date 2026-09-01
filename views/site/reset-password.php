<?php
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
$this->title = 'Set a New Password';
?>
<div class="login-page d-flex align-items-center justify-content-center" style="min-height: 100vh; background: #0f3a70;">
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5" style="max-width: 440px; width: 100%;">
        <h4 class="fw-bold mb-1">Set a New Password</h4>
        <p class="text-muted small mb-4">Choose a new password for your account.</p>

        <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
            <?= $form->field($model, 'password')->passwordInput(['autofocus' => true, 'placeholder' => 'New password']) ?>
            <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => 'Confirm new password']) ?>
            <?= Html::submitButton('Reset Password', ['class' => 'btn btn-primary btn-lg w-100 fw-bold mt-2']) ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>