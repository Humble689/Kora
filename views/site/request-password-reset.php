<?php
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
$this->title = 'Reset Your Password';
?>
<div class="login-page d-flex align-items-center justify-content-center" style="min-height: 100vh; background: #0f3a70;">
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5" style="max-width: 440px; width: 100%;">
        <h4 class="fw-bold mb-1">Forgot Your Password?</h4>
        <p class="text-muted small mb-4">Enter your email and we'll send you a reset link.</p>

        <?php $form = ActiveForm::begin(['id' => 'reset-request-form']); ?>
            <?= $form->field($model, 'email')->textInput(['type' => 'email', 'autofocus' => true, 'placeholder' => 'you@example.com']) ?>
            <?= Html::submitButton('Send Reset Link', ['class' => 'btn btn-primary btn-lg w-100 fw-bold mt-2']) ?>
        <?php ActiveForm::end(); ?>

        <div class="text-center mt-3">
            <?= Html::a('Back to Login', ['site/login'], ['class' => 'small text-decoration-none']) ?>
        </div>
    </div>
</div>