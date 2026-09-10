<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\Schools $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Onboard New Multi-Tenant Institution';
?>
<div class="py-5 bg-light min-vh-100 d-flex align-items-center">
    <div class="container" style="max-width: 650px;">
        <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
            <div class="text-center mb-4 border-bottom pb-2">
                <h3 class="fw-bold mb-1 text-dark"><?= $this->title ?></h3>
                <p class="text-muted small">Initialize campus servers, configure tuition constraints, and provision the Master Administrator profile.</p>
            </div>
            

            <?php $form = ActiveForm::begin([
                'id' => 'school-onboarding-form', 
                'layout' => 'default',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'form-label fw-semibold text-secondary small'],
                    'inputOptions' => ['class' => 'form-control form-control-sm text-dark fw-bold bg-white'],
                    'errorOptions' => ['class' => 'invalid-feedback d-block'],
                ],
            ]); ?>
                
                <h6 class="fw-bold text-primary mb-3 uppercase tracking-wider"><i class="bi bi-building"></i> 1. Campus Identity & Settlement Details</h6>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'name')->textInput(['placeholder' => 'e.g., Greenhill Academy'])->label('Institution Name') ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'contact_email')->textInput(['type' => 'email', 'placeholder' => 'bursar@school.com'])->label('Administrative Contact Email') ?>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'bank_account')->textInput(['placeholder' => 'Clearing Routing Number'])->label('Clearing Bank Account') ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'base_tuition_fees')->textInput(['type' => 'number', 'placeholder' => '850000'])->label('Base Term Tuition (UGX)') ?>
                    </div>
                </div>

                <h6 class="fw-bold text-warning mb-3 uppercase tracking-wider"><i class="bi bi-shield-lock-fill"></i> 2. Provision Master School Administrator Account</h6>
                <div class="row g-2 mb-3 p-3 bg-light rounded rounded-3 border">
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'admin_username')->textInput(['autocomplete' => 'off', 'placeholder' => 'e.g., greenhill_admin'])->label('Master Admin Username') ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'admin_password')->textInput(['autocomplete' => 'off', 'placeholder' => 'Minimum 6 characters'])->label('Temporary Setup Password') ?>
                    </div>
                    <div class="form-text text-muted text-xs ps-2 mt-2"><i class="bi bi-info-circle"></i> This user will inherit full privileges at the school level to provision lower tier staff (Bursars, Teachers, D.O.S.).</div>
                </div>

                <div class="mt-4">
                    <?= Html::submitButton('<i class="bi bi-cloud-check-fill me-1"></i> Deploy School Space & Dispatch Activation Email', ['class' => 'btn btn-success btn-lg w-100 fw-bold rounded-3 shadow-sm']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
