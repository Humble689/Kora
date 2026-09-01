<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

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
                                <div>
                                    <h2 class=" fw-bold mb-3">
                                        Welcome<br>Back
                                    </h2>

                                    <p class="mb-0 opacity-75">
                                        Log in to access your account
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Login Form -->
                    <div class="col-lg-7 bg-white text-dark" style="background-color: #ffffff !important; color: #212529 !important;">
                        <div class="p-4 p-md-5">
                            
                            <!-- Back Button -->
                            <div class="d-flex justify-content-end mb-4">
                                <?= Html::a(
                                    'Back to Parent Portal',
                                    ['site/index'],
                                    [
                                        'class' => 'btn btn-outline-secondary btn-sm rounded-pill px-4 fw-semibold login-back-btn',
                                        'style' => 'color: #495057 !important; border-color: #6c757d !important;'
                                    ]
                                ) ?>
                            </div>

                            <!-- Mobile Logo (shown only below lg; the desktop brand block below covers lg+) -->
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

                            <!-- Logo / Brand (desktop only — the block above already covers mobile) -->
                            <div class="d-none d-lg-flex align-items-center mb-4 pb-1">
                                <?= Html::img(
                                    Yii::getAlias('@web/images/logo.jpg'),
                                    [
                                        'alt' => 'KORA',
                                        'height' => 56,
                                        'class' => 'me-3 rounded-2',
                                    ],
                                ) ?>
                                <span class="h1 fw-bold mb-0 text-dark" style="color: #212529 !important;">KORA</span>
                            </div>

                            <h5 class="fw-normal mb-4 pb-2" style="color: #212529 !important; font-weight: 500;">
                                Sign into your account
                            </h5>

                            <?php $form = ActiveForm::begin([
                                'id' => 'login-form',
                                 'successCssClass' => '',
                            ]); ?>

                            <!-- Username -->
                            <div class="mb-4">
                                <?= $form->field($model, 'username', [
                                    'options' => [
                                        'class' => 'form-outline',
                                    ],
                                    'template' => "{input}{label}{error}",
                                    'inputOptions' => [
                                        'class' => 'form-control form-control-lg border',
                                        'style' => 'background-color: #ffffff !important; color: #212529 !important; border-color: #ced4da !important;',
                                        'placeholder' => ' ',
                                        'autofocus' => true,
                                    ],
                                ])->textInput()->label(
                                    'Username',
                                    [
                                        'class' => 'form-label',
                                        'style' => 'color: #495057 !important;'
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
                                        'class' => 'form-control form-control-lg border',
                                        'style' => 'background-color: #ffffff !important; color: #212529 !important; border-color: #ced4da !important;',
                                        'placeholder' => ' ',
                                    ],
                                ])->passwordInput()->label(
                                    'Password',
                                    [
                                        'class' => 'form-label',
                                        'style' => 'color: #495057 !important;'
                                    ]
                                ) ?>
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-4">
                                <?= $form->field($model, 'rememberMe')->checkbox([
                                    'label' => 'Remember me',
                                    'class' => 'form-check-input',
                                    'labelOptions' => ['style' => 'color: #212529 !important;']
                                ]) ?>
                            </div>

                            <!-- Login Button -->
                            <div class="pt-1 mb-4">
                                <?= Html::submitButton(
                                    'Login',
                                    [
                                        'class' => 'btn btn-primary btn-lg w-100 rounded-2 text-white fw-bold',
                                        'name' => 'login-button',
                                    ],
                                ) ?>
                            </div>

                            <?php ActiveForm::end(); ?>

                            <!-- Links -->
                            <div class="mb-3">
                                <a href="<?= Url::toRoute(['site/request-password-reset']) ?>" class="small text-decoration-none fw-semibold" style="color: #0d6efd !important;">
                                    Forgot password?
                                </a>
                            </div>

                            <p class="mb-4" style="color: #212529 !important;">
                                Don't have an account?
                                <?= Html::a('Register here', ['site/signup'], ['class' => 'text-decoration-none fw-semibold', 'style' => 'color: #0d6efd !important;']) ?>
                            </p>

                            <div class="pt-3 border-top" style="border-top-color: #dee2e6 !important;">
                                <?= Html::a('Terms of Service', ['site/terms-of-service'], [
                                    'class' => 'small text-decoration-none me-3',
                                    'style' => 'color: #555555 !important; font-weight: 500;'
                                ]) ?>

                                <?= Html::a('Privacy Policy', ['site/privacy-policy'], [
                                    'class' => 'small text-decoration-none',
                                    'style' => 'color: #555555 !important; font-weight: 500;'
                                ]) ?>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>





<style>
        body:has(.login-page) {
        background:  #0f3a70;
    }

    body:has(.login-page) main {
        padding: 0;
        margin: 0;
    }

    /* :root {
        --kora-blue-900: #0b2a52;
        --kora-blue-800: #0f3a70;
        --kora-blue-700: #14488a;
        --kora-blue-accent: #3b82f6;
    }

    .site-navbar {
        background: linear-gradient(90deg, var(--kora-blue-800) 0%, var(--kora-blue-700) 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
    } */

    body:has(.login-page) main > .container {
        max-width: 100%;
        padding: 0;
        min-height: 100vh;
    }

    .login-page {
        min-height: 100vh;
        width: 100%;

        background: #0f3a70;
/* 
        background: -webkit-linear-gradient(
            to right,
            rgba(106, 17, 203, 1),
            rgba(37, 117, 252, 1)
        );

        background: linear-gradient(
            to right,
            rgba(106, 17, 203, 1),
            rgba(37, 117, 252, 1)
        ); */
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

    .login-back-btn {
        color: #1f6feb;
        border: 1px solid rgba(31, 111, 235, 0.22);
        box-shadow: 0 12px 28px rgba(16, 33, 58, 0.12);
    }

    .login-back-btn:hover,
    .login-back-btn:focus {
        color: #1657c1;
        border-color: rgba(31, 111, 235, 0.35);
    }

    @media (max-width: 575.98px) {
        .card.shadow-lg {
            border-radius: 0.75rem !important;
        }
        .p-4.p-md-5 {
            padding: 1.5rem !important;
        }
    }
</style>