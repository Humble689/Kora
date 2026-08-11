<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\Html;
?>

<div class="login-page d-flex align-items-center justify-content-center">
    



<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center login-page-bg">
    <div class="row w-100 justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="row g-0">

                    <!-- Left Brand Panel -->
                    <div class="col-lg-5 d-none d-lg-block">
                        <div class="login-image-panel h-100">

                            <?= Html::img(
                                Yii::getAlias('@web/images/login.webp'),
                                [
                                    'alt' => 'Welcome',
                                    'class' => 'login-panel-image',
                                ]
                            ) ?>

                            <div class="login-panel-overlay"></div>

                            <div class="login-panel-content">
                                <?= Html::img(
                                    Yii::getAlias('@web/images//yii3_full_white_for_dark.svg'),
                                    [
                                        'alt' => 'KORA',
                                        'height' => 72,
                                        'class' => 'mb-4 rounded-2',
                                    ],
                                ) ?>

                                <div>
                                    <h2 class="fw-bold mb-3">
                                        Welcome<br>Back
                                    </h2>

                                    <p class="mb-0 opacity-75">
                                        Log in to access your account and manage your application.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Login Form -->
                    <div class="col-lg-7 bg-red">
                        <div class="p-4 p-md-5">

                            <!-- Mobile Logo -->
                            <div class="d-lg-none mb-4">
                                <?= Html::img(
                                    Yii::getAlias('@web/images/logo.jpg'),
                                    [
                                        'alt' => 'KORA',
                                        'height' => 64,
                                        'class' => 'rounded-2',
                                    ],
                                ) ?>
                            </div>

                            <!-- Logo / Brand -->
                            <div class="d-flex align-items-center mb-4 pb-1">
                                <?= Html::img(
                                    Yii::getAlias('@web/images/logo.jpg'),
                                    [
                                        'alt' => 'KORA',
                                        'height' => 56,
                                        'class' => 'me-3 rounded-2',
                                    ],
                                ) ?>
                                <span class="h1 fw-bold mb-0">KORA</span>
                            </div>

                            <h5 class="fw-normal mb-4 pb-2 login-subtitle">
                                Sign into your account
                            </h5>

                            <?php

                                use yii\bootstrap5\ActiveForm;

                                $form = ActiveForm::begin([
                                'id' => 'login-form',
                            ]); ?>

                            <!-- Username -->
                            <div class="mb-4 bg-grey">
                                <?= $form->field($model, 'username', [
                                    'options' => [
                                        'class' => 'form-outline',
                                    ],
                                    'template' => "{input}{label}{error}",
                                    'inputOptions' => [
                                        'class' => 'form-control form-control-lg',
                                        'placeholder' => ' ',
                                        'autofocus' => true,
                                    ],
                                ])->textInput()->label(
                                    'Username',
                                    [
                                        'class' => 'form-label',
                                    ]
                                ) ?>
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <?= $form->field($model, 'password', [
                                    'options' => [
                                        'class' => 'form-outline',
                                    ],
                                    'template' => "{input}{label}{error}",
                                    'inputOptions' => [
                                        'class' => 'form-control form-control-lg',
                                        'placeholder' => ' ',
                                    ],
                                ])->passwordInput()->label(
                                    'Password',
                                    [
                                        'class' => 'form-label',
                                    ]
                                ) ?>
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-4">
                                <?= $form->field($model, 'rememberMe')->checkbox([
                                    'label' => 'Remember me',
                                ]) ?>
                            </div>

                            <!-- Login Button -->
                            <div class="pt-1 mb-4">
                                <?= Html::submitButton(
                                    'Login',
                                    [
                                        'class' => 'btn btn-dark btn-lg w-100 rounded-2',
                                        'name' => 'login-button',
                                    ],
                                ) ?>
                            </div>

                            <?php ActiveForm::end(); ?>

                            <!-- Links -->
                            <a href="#" class="small text-muted text-decoration-none">
                                Forgot password?
                            </a>

                            <p class="mb-4 mt-3" style="color: #393f81;">
                                Don't have an account?
                                <?= Html::a('Register here', ['site/signup'], ['class' => 'text-decoration-none', 'style' => 'color: #393f81;']) ?>
                            </p>

                            <div>
                                <?= Html::a('Terms of Service', ['site/terms-of-service'], ['class' => 'small text-muted text-decoration-none me-3']) ?>

                                <?= Html::a('Privacy Policy', ['site/privacy-policy'], ['class' => 'small text-muted text-decoration-none']) ?>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
</div>




<style>
        body:has(.login-page) {
        background: #6a11cb;
    }

    body:has(.login-page) main {
        padding: 0;
        margin: 0;
    }

    body:has(.login-page) main > .container {
        max-width: 100%;
        padding: 0;
        min-height: 100vh;
    }

    .login-page {
        min-height: 100vh;
        width: 100%;

        background: #6a11cb;

        background: -webkit-linear-gradient(
            to right,
            rgba(106, 17, 203, 1),
            rgba(37, 117, 252, 1)
        );

        background: linear-gradient(
            to right,
            rgba(106, 17, 203, 1),
            rgba(37, 117, 252, 1)
        );
    }

    .login-image-panel {
        position: relative;
        height: 100%;
        min-height: 600px;
        overflow: hidden;
        border-radius: 1rem 0 0 1rem;
    }

    .login-panel-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .login-panel-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
    }

    .login-panel-content {
        position: relative;
        z-index: 2;
        height: 100%;
        min-height: 600px;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: white;
    }
</style>