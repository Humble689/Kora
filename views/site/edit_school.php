<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\Schools $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Modify Institution Parameters: ' . $model->name;
?>
<div class="py-5 bg-light min-vh-100 d-flex align-items-center">
    <div class="container" style="max-width: 650px;">
        <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
            <div class="text-center mb-4 border-bottom pb-2">
                <h3 class="fw-bold mb-1 text-dark">Edit School Specifications</h3>
                <p class="text-muted small">Update campus identity, settlement details, and tuition parameters for <?= Html::encode($model->name) ?>.</p>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'school-edit-form',
                'layout' => 'default',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'form-label fw-semibold text-secondary small'],
                    'inputOptions' => ['class' => 'form-control form-control-sm text-dark fw-bold bg-white'],
                    'errorOptions' => ['class' => 'invalid-feedback d-block'],
                ],
            ]); ?>

                <h6 class="fw-bold text-primary mb-3 uppercase tracking-wider"><i class="bi bi-building"></i> Campus Identity & Settlement Details</h6>
                <div class="row g-2 mb-3 align-items-start">
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'name')->textInput([
                            'required' => true,
                            'autocomplete' => 'new-name-field',
                        ])->label('Institution Name') ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'contact_email')->textInput([
                            'type' => 'email',
                            'required' => true,
                            'autocomplete' => 'new-contact-email',
                        ])->label('Contact Address Email') ?>
                    </div>
                </div>

                <div class="row g-2 mb-4 align-items-start">
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'bank_account')->textInput([
                            'required' => true,
                            'autocomplete' => 'off',
                        ])->label('Clearing Settlement Account') ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'base_tuition_fees')->textInput([
                            'type' => 'number',
                            'required' => true,
                        ])->label('Tuition Fees Rate (UGX)') ?>
                    </div>
                </div>

                <div class="mt-4">
                    <?= Html::submitButton('<i class="bi bi-cloud-check-fill me-1"></i> Save Parameters Changes', ['class' => 'btn btn-primary btn-lg w-100 fw-bold rounded-3 shadow-sm']) ?>
                    <?= Html::a('Cancel', ['site/super-admin'], ['class' => 'btn btn-link w-100 text-muted small mt-2']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>