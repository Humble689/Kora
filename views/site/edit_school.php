<?php use yii\bootstrap5\ActiveForm; use yii\bootstrap5\Html; $this->title = 'Modify Institution Parameters: ' . $model->name; ?>
<div class="py-5 bg-light min-vh-100 d-flex align-items-center">
    <div class="container" style="max-width: 500px;">
        <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
            <h4 class="fw-bold mb-3 text-center">Edit School Specifications</h4>
            <?php $form = ActiveForm::begin(['layout' => 'horizontal']); ?>
                <?= $form->field($model, 'name')->textInput(['name' => 'Schools[name]', 'required'=>true])->label('Institution Name') ?>
                <?= $form->field($model, 'bank_account')->textInput(['name' => 'Schools[bank_account]', 'required'=>true])->label('Clearing Settlement Account') ?>
                <?= $form->field($model, 'base_tuition_fees')->textInput(['name' => 'Schools[base_tuition_fees]', 'type' => 'number', 'required'=>true])->label('Tuition Fees Rate (UGX)') ?>
                <?= $form->field($model, 'contact_email')->textInput(['name' => 'Schools[contact_email]', 'type' => 'email', 'required'=>true])->label('Contact Address Email') ?>
                <div class="mt-4">
                    <?= Html::submitButton('Save Parameters Changes', ['class' => 'btn btn-primary btn-lg w-100 fw-bold rounded-3']) ?>
                    <?= Html::a('Cancel', ['site/super-admin'], ['class' => 'btn btn-link w-100 text-muted small mt-2']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
