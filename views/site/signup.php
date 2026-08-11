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
    <div class="container" style="max-width: 500px;">
        <div class="card shadow-sm border-0 rounded-4 p-4 mx-auto bg-white">
            <div class="text-center mb-4">
                <h1 class="h4 fw-bold text-dark"><?= Html::encode($this->title) ?></h1>
                <p class="text-muted small">Create secure credentials to access your designated workspace desk.</p>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'form-signup',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'form-label fw-semibold text-secondary small'],
                    'inputOptions' => ['class' => 'form-control form-control-lg'],
                    'errorOptions' => ['class' => 'invalid-feedback d-block'],
                ],
            ]); ?>

                               <!-- Inside views/site/signup.php: -->
                
                <?php if ($currentUserRole === 'SUPER_ADMIN'): ?>
                    <!-- Super Admin still gets the global multi-tenant dropdown selection -->
                    <?= $form->field($model, 'school_id')->dropDownList(
                        \yii\helpers\ArrayHelper::map(\app\models\Schools::find()->all(), 'id', 'name'),
                        ['class' => 'form-select form-select-lg fw-bold text-dark mb-3', 'prompt' => 'Choose school association...']
                    )->label('Employing School') ?>
                <?php else: ?>
                    <!--  SCHOOL ADMIN FIX: Clean read-only text box, no leaking data pools -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Employing School</label>
                        <input type="text" class="form-control form-control-lg bg-light text-muted fw-bold" value="<?= Html::encode(Yii::$app->user->identity->school ? Yii::$app->user->identity->school->name : 'Our Campus') ?>" readonly disabled>
                        <!-- Hidden structural handler tag to pass validation rules arrays safely -->
                        <input type="hidden" name="SignupForm[school_id]" value="<?= Yii::$app->user->identity->school_id ?>">
                    </div>
                <?php endif; ?>


                <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'off', 'placeholder' => 'Choose staff username']) ?>

                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Create secure account password']) ?>

                <?= $form->field($model, 'role')->dropDownList([
                    'BURSAR' => 'Bursar Admin (Full Fees & Wallet Access)',
                    'CANTEEN' => 'Canteen Operator (POS Sales Counter)',
                    'TEACHER' => 'Classroom Teacher (UNEB Marks Entry Desk)',
                    'DOS' => 'Director of Studies (Academic Audit & Sealing Panel)',
                ], [
                    'id' => 'roleSelectorField',
                    'class' => 'form-select form-select-lg fw-bold text-dark',
                    'prompt' => 'Select staff permission assignment...',
                    'onchange' => 'toggleTeacherAssignmentInputs(this.value)'
                ])->label('Assign System Role') ?>

                <!-- NEW: Dynamic Teacher Assignment Fields Block Container (Hidden by default) -->
                <div id="teacherAssignmentFieldsBlock" class="d-none border-top border-primary border-2 pt-3 mt-3 bg-light p-3 rounded-3">
                    <h6 class="fw-bold text-primary mb-3 small uppercase tracking-wider"><i class="bi bi-bookmark-plus-fill"></i> Setup First Academic Assignment</h6>
                    
                    <?= $form->field($model, 'teacher_class')->dropDownList([
                        'Primary 1' => 'Primary 1', 'Primary 2' => 'Primary 2', 'Primary 3' => 'Primary 3', 'Primary 4' => 'Primary 4', 'Primary 5' => 'Primary 5', 'Primary 6' => 'Primary 6', 'Primary 7' => 'Primary 7',
                        'Senior 1' => 'Senior 1', 'Senior 2' => 'Senior 2', 'Senior 3' => 'Senior 3', 'Senior 4' => 'Senior 4', 'Senior 5' => 'Senior 5', 'Senior 6' => 'Senior 6'
                    ], ['class' => 'form-select form-select-lg text-dark fw-semibold', 'prompt' => 'Select Assigned Class...'])->label('Class Assignment') ?>

                    <?= $form->field($model, 'teacher_subject')->dropDownList([
                        'Mathematics' => 'Mathematics', 'English' => 'English', 'Biology' => 'Biology', 'Chemistry' => 'Chemistry', 
                        'Physics' => 'Physics', 'History' => 'History', 'Geography' => 'Geography', 'Entrepreneurship' => 'Entrepreneurship',
                        'French' => 'French', 'Art' => 'Art', 'Social Studies' => 'Social Studies', 'Science' => 'Science'
                    ], ['class' => 'form-select form-select-lg text-dark fw-semibold', 'prompt' => 'Select Assigned Subject...'])->label('Subject Assignment') ?>
                </div>
                                    <!-- Inside views/site/signup.php, right above the username field input: -->
                
                <?= $form->field($model, 'email')->textInput([
                    'type' => 'email',
                    'autocomplete' => 'off', 
                    'placeholder' => 'Enter personal staff email address (e.g., moses@gmail.com)',
                    'required' => true
                ])->label('Personal Staff Email Address') ?>



                <div class="mt-4">
                    <?= Html::submitButton('Register Staff Account', ['class' => 'btn btn-primary btn-lg w-100 fw-bold rounded-3 shadow-sm', 'name' => 'signup-button']) ?>
                </div>


            <?php ActiveForm::end(); ?>

            <div class="text-center mt-3 small">
                <span class="text-muted">Already registered?</span>
                <?= Html::a('Log in here', ['site/login'], ['class' => 'fw-semibold text-decoration-none']) ?>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Toggles visibility of assignment selectors instantly based on selected administrative role
 */
function toggleTeacherAssignmentInputs(selectedRole) {
    const fieldsBlock = document.getElementById('teacherAssignmentFieldsBlock');
    if (selectedRole === 'TEACHER') {
        fieldsBlock.classList.remove('d-none');
    } else {
        fieldsBlock.classList.add('d-none');
    }
}
</script>
