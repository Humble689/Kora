<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Register New Institutional Staff';
?>
<div class="site-signup bg-light py-5 min-vh-100">
    <div class="container" style="max-width: 560px;">

        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3 shadow-sm" style="width: 56px; height: 56px; background: var(--theme-primary);">
                <i class="bi bi-person-plus-fill text-white fs-4"></i>
            </div>
            <span class="text-uppercase fw-bold small d-block mb-1" style="font-size: 11px; color: var(--theme-primary); letter-spacing: 0.08em;">
                Staff Onboarding
            </span>
            <h1 class="h4 fw-bold mb-1" style="color: var(--theme-text);"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted small mb-0">Create secure credentials to access your designated workspace desk.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-body p-4 p-md-5">

                <?php $form = ActiveForm::begin([
                    'id' => 'form-signup',
                    'fieldConfig' => [
                        'template' => "{label}\n{input}\n{error}",
                        'labelOptions' => ['class' => 'form-label fw-semibold small text-uppercase', 'style' => 'color: var(--theme-muted); font-size: 11px; letter-spacing: 0.04em;'],
                        'inputOptions' => ['class' => 'form-control'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'],
                    ],
                ]); ?>

                    <!-- Section: School -->
                    <div class="mb-4">
                        <?php if ($currentUserRole === 'SUPER_ADMIN'): ?>
                            <?= $form->field($model, 'school_id')->dropDownList(
                                \yii\helpers\ArrayHelper::map(\app\models\Schools::find()->all(), 'id', 'name'),
                                ['class' => 'form-select fw-semibold', 'prompt' => 'Choose school association...']
                            )->label('Employing School') ?>
                        <?php else: ?>
                            <label class="form-label fw-semibold small text-uppercase" style="color: var(--theme-muted); font-size: 11px; letter-spacing: 0.04em;">Employing School</label>
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background: var(--theme-soft); border: 1px solid var(--theme-border);">
                                <i class="bi bi-building" style="color: var(--theme-primary);"></i>
                                <span class="fw-semibold" style="color: var(--theme-text);">
                                    <?= Html::encode(Yii::$app->user->identity->school ? Yii::$app->user->identity->school->name : 'Our Campus') ?>
                                </span>
                            </div>
                            <input type="hidden" name="SignupForm[school_id]" value="<?= Yii::$app->user->identity->school_id ?>">
                        <?php endif; ?>
                    </div>

                    <!-- Section: Account credentials -->
                    <div class="mb-4">
                        <h6 class="fw-bold small text-uppercase mb-3" style="color: var(--theme-primary); font-size: 11px; letter-spacing: 0.06em;">
                            <i class="bi bi-shield-lock me-1"></i> Account Credentials
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'off', 'placeholder' => 'Choose staff username']) ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'email')->textInput([
                                    'type' => 'email',
                                    'autocomplete' => 'off',
                                    'placeholder' => 'staff@example.com',
                                    'required' => true,
                                ])->label('Personal Staff Email') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Create secure temporary password']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Role -->
                    <div class="mb-3">
                        <h6 class="fw-bold small text-uppercase mb-3" style="color: var(--theme-primary); font-size: 11px; letter-spacing: 0.06em;">
                            <i class="bi bi-person-badge me-1"></i> System Role
                        </h6>

                        <?php
                        $roleOptions = [
                            'BURSAR' => 'Bursar Admin (Full Fees & Wallet Access)',
                            'CANTEEN' => 'Canteen Operator (POS Sales Counter)',
                            'TEACHER' => 'Classroom Teacher (UNEB Marks Entry Desk)',
                            'DOS' => 'Director of Studies (Academic Audit & Sealing Panel)',
                        ];
                        if ($currentUserRole === 'SUPER_ADMIN') {
                            $roleOptions = ['SCHOOL_ADMIN' => 'School Administrator (Full School Management Access)'] + $roleOptions;
                        }
                        ?>

                        <?= $form->field($model, 'role')->dropDownList($roleOptions, [
                            'id' => 'roleSelectorField',
                            'class' => 'form-select fw-semibold',
                            'prompt' => 'Select staff permission assignment...',
                            'onchange' => 'toggleTeacherAssignmentInputs(this.value); toggleCanteenDeviceInputs(this.value);',
                        ])->label(false) ?>
                    </div>

                    <!-- Dynamic: Teacher assignment -->
                    <div id="teacherAssignmentFieldsBlock" class="d-none rounded-3 p-4 mb-4" style="background: var(--theme-soft); border: 1px solid var(--theme-border);">
                        <h6 class="fw-bold mb-3 small text-uppercase" style="color: var(--theme-primary); font-size: 11px; letter-spacing: 0.06em;">
                            <i class="bi bi-bookmark-plus-fill me-1"></i> First Academic Assignment
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <?= $form->field($model, 'teacher_class')->dropDownList([
                                    'Primary 1' => 'Primary 1', 'Primary 2' => 'Primary 2', 'Primary 3' => 'Primary 3', 'Primary 4' => 'Primary 4', 'Primary 5' => 'Primary 5', 'Primary 6' => 'Primary 6', 'Primary 7' => 'Primary 7',
                                    'Senior 1' => 'Senior 1', 'Senior 2' => 'Senior 2', 'Senior 3' => 'Senior 3', 'Senior 4' => 'Senior 4', 'Senior 5' => 'Senior 5', 'Senior 6' => 'Senior 6',
                                ], ['class' => 'form-select fw-semibold bg-white', 'prompt' => 'Select class...'])->label('Class') ?>
                            </div>
                            <div class="col-12 col-sm-6">
                                <?= $form->field($model, 'teacher_subject')->dropDownList([
                                    'Mathematics' => 'Mathematics', 'English' => 'English', 'Biology' => 'Biology', 'Chemistry' => 'Chemistry',
                                    'Physics' => 'Physics', 'History' => 'History', 'Geography' => 'Geography', 'Entrepreneurship' => 'Entrepreneurship',
                                    'French' => 'French', 'Art' => 'Art', 'Social Studies' => 'Social Studies', 'Science' => 'Science',
                                ], ['class' => 'form-select fw-semibold bg-white', 'prompt' => 'Select subject...'])->label('Subject') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic: Canteen device assignment -->
                    <div id="canteenDeviceFieldsBlock" class="d-none rounded-3 p-4 mb-4" style="background: var(--theme-soft); border: 1px solid var(--theme-border);">
                        <h6 class="fw-bold mb-3 small text-uppercase" style="color: var(--theme-primary); font-size: 11px; letter-spacing: 0.06em;">
                            <i class="bi bi-hdd-stack-fill me-1"></i> POS Terminal Assignment
                        </h6>

                        <?php
                        $deviceQuery = \app\models\PosDevices::find()->andWhere(['status' => 'ACTIVE']);
                        if ($currentUserRole !== 'SUPER_ADMIN') {
                            $deviceQuery->andWhere(['school_id' => Yii::$app->user->identity->school_id]);
                        }
                        $devices = $deviceQuery->all();
                        ?>

                        <?php if (!empty($devices)): ?>
                            <label class="form-label fw-semibold small text-uppercase mb-2" style="color: var(--theme-muted); font-size: 11px; letter-spacing: 0.04em;">Existing Terminals</label>
                            <div class="d-flex flex-column gap-2 mb-1">
                                <?php foreach ($devices as $device): ?>
                                    <label class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 bg-white" style="border: 1px solid var(--theme-border); cursor: pointer;">
                                        <input type="checkbox" class="form-check-input m-0" name="SignupForm[pos_device_ids][]" value="<?= $device->id ?>" style="accent-color: var(--theme-primary);">
                                        <div class="flex-fill">
                                            <div class="fw-semibold small" style="color: var(--theme-text);"><?= Html::encode($device->label ?: $device->device_uid) ?></div>
                                            <div class="text-muted font-monospace" style="font-size: 10px;"><?= Html::encode($device->device_uid) ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small mb-1"><i class="bi bi-info-circle"></i> No active terminals registered for this school yet.</div>
                        <?php endif; ?>

                        <hr class="my-3" style="border-color: var(--theme-border);">

                        <?= $form->field($model, 'new_device_label')->textInput([
                            'maxlength' => 100,
                            'placeholder' => 'e.g. Snack Bar Counter, Front Gate Kiosk',
                            'class' => 'form-control bg-white',
                        ])->label('Or Register a New Terminal')->hint('Leave blank to skip. A device ID is generated automatically.') ?>
                    </div>

                    <?= Html::submitButton('<i class="bi bi-check-circle-fill me-2"></i>Register Staff Account', [
                        'class' => 'btn btn-lg w-100 fw-bold rounded-3 shadow-sm text-white',
                        'style' => 'background: var(--theme-primary); border-color: var(--theme-primary);',
                        'name' => 'signup-button',
                        'encode' => false,
                    ]) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

    </div>
</div>

<script>
function toggleTeacherAssignmentInputs(selectedRole) {
    document.getElementById('teacherAssignmentFieldsBlock').classList.toggle('d-none', selectedRole !== 'TEACHER');
}

function toggleCanteenDeviceInputs(selectedRole) {
    document.getElementById('canteenDeviceFieldsBlock').classList.toggle('d-none', selectedRole !== 'CANTEEN');
}
</script>