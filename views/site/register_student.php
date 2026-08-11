<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\Students $model */
/** @var string $schoolName */
/** @var float $baseFees */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'KORA Enroll New Student';
?>

<div class="site-register-student bg-light py-4 min-vh-100">
    <div class="container" style="max-width: 550px;">
        
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-3">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
            <div class="text-center mb-4 border-bottom pb-3">
                <h1 class="h3 fw-bold text-dark mb-1">Student Enrollment</h1>
                <p class="text-muted small mb-0">Onboarding file for: <span class="fw-bold text-primary"><?= Html::encode($schoolName) ?></span></p>
            </div>

            <div class="alert alert-info border-0 rounded-3 small mb-4 py-2.5">
                <i class="bi bi-info-circle-fill me-1"></i>
                By enrolling, this student will be auto-charged the school's configured term base fee structure of <strong>UGX <?= number_format($baseFees, 0) ?></strong>.
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'form-register-student',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'form-label fw-semibold text-secondary small'],
                    'inputOptions' => ['class' => 'form-control form-control-lg'],
                    'errorOptions' => ['class' => 'invalid-feedback d-block'],
                ],
            ]); ?>

                <?= $form->field($model, 'name')->textInput(['name' => 'Students[name]', 'autofocus' => true, 'autocomplete' => 'off', 'placeholder' => 'Enter Student\'s Full Name', 'required' => true])->label('Student Full Name') ?>

                <?= $form->field($model, 'class_level')->dropDownList([
                    'Primary 1' => 'Primary 1', 'Primary 2' => 'Primary 2', 'Primary 3' => 'Primary 3',
                    'Primary 4' => 'Primary 4', 'Primary 5' => 'Primary 5', 'Primary 6' => 'Primary 6', 'Primary 7' => 'Primary 7',
                    'Senior 1' => 'Senior 1', 'Senior 2' => 'Senior 2', 'Senior 3' => 'Senior 3', 'Senior 4' => 'Senior 4',
                    'Senior 5' => 'Senior 5', 'Senior 6' => 'Senior 6'
                ], [
                    'id' => 'enrollClassSelector',
                    'name' => 'Students[class_level]',
                    'class' => 'form-select form-select-lg fw-bold text-dark',
                    'prompt' => 'Select classroom assignment tier...',
                    'required' => true,
                    'onchange' => 'toggleCurriculumFields(this.value)'
                ])->label('Class Level') ?>

                <!--  O-Level Optional Subjects Input (Hidden by default) -->
                <div id="oLevelOptionalsWrapper" class="mb-3 d-none">
                    <label class="form-label fw-semibold text-secondary small">O-Level Optional Subjects (Comma-separated)</label>
                    <input type="text" name="Students[optional_subjects]" class="form-control form-control-lg" placeholder="e.g., French, Art, Music">
                    <div class="form-text text-muted text-xs">Type the optional electives this student is registered to study.</div>
                </div>

                <!--   A-Level Subject Combination Input (Hidden by default) -->
                <div id="aLevelCombinationWrapper" class="mb-3 d-none">
                    <label class="form-label fw-semibold text-secondary small">A-Level Subject Combination Code</label>
                    <input type="text" name="Students[a_level_combination]" class="form-control form-control-lg text-uppercase font-monospace" placeholder="e.g., PCM/Sub-Math, HEG/ICT">
                    <div class="form-text text-muted text-xs">Enter core letters representing their primary combination subjects.</div>
                </div>

                <div class="mt-4">
                    <?= Html::submitButton('Generate Code & Enroll Student', ['class' => 'btn btn-primary btn-lg w-100 fw-bold rounded-3 shadow-sm']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script>

function toggleCurriculumFields(selectedClass) {
    const oLevelWrapper = document.getElementById('oLevelOptionalsWrapper');
    const aLevelWrapper = document.getElementById('aLevelCombinationWrapper');

    // Reset default styling states
    oLevelWrapper.classList.add('d-none');
    aLevelWrapper.classList.add('d-none');

    if (!selectedClass) return;

    if (selectedClass.includes('Senior 1') || selectedClass.includes('Senior 2') || selectedClass.includes('Senior 3') || selectedClass.includes('Senior 4')) {
        oLevelWrapper.classList.remove('d-none');
    } else if (selectedClass.includes('Senior 5') || selectedClass.includes('Senior 6')) {
        aLevelWrapper.classList.remove('d-none');
    }
}
</script>
