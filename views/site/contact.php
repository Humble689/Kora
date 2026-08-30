<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\ContactForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\captcha\Captcha;

$this->title = 'Contact Us';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Get in touch with the Kora team — questions, support, or partnership inquiries.';
$this->params['meta_keywords'] = 'kora, school payments, support, contact';

$htmlIcon = <<<HTML
{label}<div class="input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label fw-semibold small'];
?>
<style>
.kora-contact-page { background: linear-gradient(180deg, var(--theme-soft, #f0fdf4) 0%, #f7f8fa 55%); min-height: 100vh; }
.kora-contact-card { border-radius: 22px; box-shadow: 0 10px 34px rgba(16, 24, 40, .07); }
.kora-brand-panel { background: linear-gradient(160deg, var(--theme-primary, #16a34a), #14532d); }
.kora-brand-mark { font-weight: 800; font-size: 1.35rem; letter-spacing: .02em; }
.kora-brand-sub { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; opacity: .8; font-weight: 700; }
.kora-input-group .input-group-text { background: var(--theme-soft, #f9fafb); border-color: var(--theme-border, #e5e7eb); }
.kora-input-group .form-control { border-color: var(--theme-border, #e5e7eb); }
.kora-input-group .form-control:focus { border-color: var(--theme-primary, #16a34a); box-shadow: 0 0 0 .2rem rgba(22, 163, 74, .12); }
.kora-submit-btn { background: var(--theme-primary, #16a34a); border: none; font-weight: 700; }
.kora-submit-btn:hover { background: var(--theme-primary, #16a34a); opacity: .92; }
</style>

<?php if (Yii::$app->session->hasFlash('success')): ?>

<div class="kora-contact-page d-flex align-items-center justify-content-center text-center py-5">
    <div class="mx-auto" style="max-width: 480px;">
        <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: var(--theme-primary, #16a34a);"></i>
        <h1 class="h3 fw-bold mb-2 mt-3">Message sent</h1>
        <p class="text-muted mb-4">Thanks for reaching out — the Kora team will get back to you shortly.</p>

        <?php if (YII_DEBUG && Yii::$app->mailer->useFileTransport): ?>
            <p class="text-body-tertiary small mb-4">
                Development mode: email saved under
                <code><?= Yii::getAlias(Yii::$app->mailer->fileTransportPath) ?></code>
            </p>
        <?php endif; ?>

        <?= Html::a(
            'Send another message',
            ['contact'],
            ['class' => 'btn kora-submit-btn text-white px-4'],
        ) ?>
    </div>
</div>

<?php else: ?>

<div class="kora-contact-page d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 overflow-hidden kora-contact-card" style="max-width: 900px; width: 100%;">
        <div class="row g-0">

            <!-- Brand panel -->
            <div class="col-md-4 d-none d-md-flex kora-brand-panel text-white">
                <div class="d-flex flex-column justify-content-between p-4 p-lg-5 w-100">
                    <div>
                        <div class="kora-brand-mark">KORA</div>
                        <div class="kora-brand-sub">School Wallet Platform</div>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-3 text-white">
                            Get In<br>Touch
                        </h2>
                        <p class="opacity-75 mb-0 text-white">
                            Questions about payments, wallets, or partnering with your school? We'd love to hear from you.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form panel -->
            <div class="col-md-8">
                <div class="p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="d-md-none mb-3">
                            <div class="kora-brand-mark" style="color: var(--theme-primary, #16a34a);">KORA</div>
                        </div>
                        <h1 class="h3 fw-bold mb-1"><?= Html::encode($this->title) ?></h1>
                        <p class="text-body-secondary small text-dark">Fill out the form below and we'll get back to you</p>
                    </div>

                    <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                    <div class="row">
                        <div class="col-sm-6 mb-3 kora-input-group">
                            <?= $form->field($model, 'name', [
                                'options' => ['class' => 'mb-0'],
                                'template' => sprintf($htmlIcon, '&#128100;'),
                                'inputOptions' => [
                                    'class' => 'form-control',
                                    'placeholder' => 'Name',
                                    'autofocus' => true,
                                ],
                            ])->label('Your Name', $labelOptions) ?>
                        </div>

                        <div class="col-sm-6 mb-3 kora-input-group">
                            <?= $form->field($model, 'email', [
                                'options' => ['class' => 'mb-0'],
                                'template' => sprintf($htmlIcon, '&#9993;'),
                                'inputOptions' => [
                                    'class' => 'form-control',
                                    'placeholder' => 'email@example.com',
                                ],
                            ])->label('Your Email', $labelOptions) ?>
                        </div>
                    </div>

                    <div class="mb-3 kora-input-group">
                        <?= $form->field($model, 'subject', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, '&#128172;'),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'Subject',
                            ],
                        ])->label('Subject', $labelOptions) ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'body', [
                            'options' => ['class' => 'mb-0'],
                            'template' => '{label}{input}{error}{hint}',
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'Your message...',
                                'rows' => 4,
                            ],
                        ])->textarea()->label('Message', $labelOptions) ?>
                    </div>

                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <?= $form->field($model, 'verifyCode', [
                            'enableLabel' => false,
                            'options' => ['class' => ''],
                            'inputOptions' => ['aria-label' => 'Verification code'],
                        ])->widget(Captcha::class, [
                            'template' => '<div class="d-flex align-items-center gap-2">{image}{input}</div>',
                        ]) ?>

                        <?= Html::submitButton(
                            'Submit',
                            [
                                'class' => 'btn kora-submit-btn text-white px-4 ms-auto',
                                'name' => 'contact-button',
                            ],
                        ) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>

        </div>
    </div>
</div>

<?php endif; ?>