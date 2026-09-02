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
                'options' => ['enctype' => 'multipart/form-data'],
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'form-label fw-semibold text-secondary small'],
                    'inputOptions' => ['class' => 'form-control form-control-lg'],
                    'errorOptions' => ['class' => 'invalid-feedback d-block'],
                ],
            ]); ?>

                <!-- Profile photo -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small">Student Photo (optional)</label>
                    <div class="d-flex align-items-center gap-3">
                        <span id="enroll-photo-placeholder" class="rounded-circle bg-secondary-subtle text-secondary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:64px;height:64px;">
                            <i class="bi bi-person-fill fs-3"></i>
                        </span>
                        <img id="enroll-photo-preview" class="rounded-circle border d-none flex-shrink-0" style="width:64px;height:64px;object-fit:cover;" alt="">
                        <input type="file" name="student_photo" id="student_photo" accept="image/png,image/jpeg,image/webp" class="form-control">
                    </div>
                    <div class="form-text text-muted text-xs">JPG, PNG, or WEBP. Max 2MB. Used on report cards if provided.</div>
                </div>

                <?= $form->field($model, 'name')->textInput(['name' => 'Students[name]', 'autofocus' => true, 'autocomplete' => 'off', 'placeholder' => 'Enter Student\'s Full Name', 'required' => true])->label('Student Full Name') ?>

                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'sex')->dropDownList([
                            'MALE' => 'Male',
                            'FEMALE' => 'Female',
                        ], [
                            'name' => 'Students[sex]',
                            'class' => 'form-select form-select-lg',
                            'prompt' => 'Select sex...',
                        ])->label('Sex') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'date_of_birth')->textInput([
                            'name' => 'Students[date_of_birth]',
                            'type' => 'date',
                            'class' => 'form-control form-control-lg',
                            'max' => date('Y-m-d'),
                        ])->label('Date of Birth') ?>
                    </div>
                </div>

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

document.getElementById('student_photo').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('enroll-photo-preview');
    const placeholder = document.getElementById('enroll-photo-placeholder');
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (ev) {
        preview.src = ev.target.result;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
    };
    reader.readAsDataURL(file);
});
</script>