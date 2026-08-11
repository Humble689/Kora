<?php use yii\bootstrap5\ActiveForm; use yii\bootstrap5\Html; $this->title = 'Update Student Entry: ' . $model->name; ?>
<div class="py-5 bg-light min-vh-100 d-flex align-items-center">
    <div class="container" style="max-width: 500px;">
        <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
            <h4 class="fw-bold mb-1 text-center">Edit Student Parameters</h4>
            <p class="text-center text-muted small mb-4">Payment Code: <span class="font-monospace fw-bold text-dark"><?= $model->payment_code ?></span></p>
            <?php $form = ActiveForm::begin(['layout' => 'horizontal']); ?>
                <?= $form->field($model, 'name')->textInput(['name' => 'Students[name]', 'required' => true])->label('Student Name') ?>
                <?= $form->field($model, 'class_level')->dropDownList([
                    'Primary 1' => 'Primary 1', 'Primary 2' => 'Primary 2', 'Primary 3' => 'Primary 3', 'Primary 4' => 'Primary 4', 'Primary 5' => 'Primary 5', 'Primary 6' => 'Primary 6', 'Primary 7' => 'Primary 7',
                    'Senior 1' => 'Senior 1', 'Senior 2' => 'Senior 2', 'Senior 3' => 'Senior 3', 'Senior 4' => 'Senior 4', 'Senior 5' => 'Senior 5', 'Senior 6' => 'Senior 6'
                ], ['name' => 'Students[class_level]', 'class' => 'form-select', 'required' => true])->label('Class Assigned Tier') ?>
                <?= $form->field($model, 'daily_spend_limit')->textInput(['name' => 'Students[daily_spend_limit]', 'type' => 'number', 'required' => true])->label('S-Wallet Daily Spend Cap (UGX)') ?>
                <div class="mt-4">
                    <?= Html::submitButton('Commit Changes Updates', ['class' => 'btn btn-success btn-lg w-100 fw-bold rounded-3']) ?>
                    <?= Html::a('Abort', ['site/bursar'], ['class' => 'btn btn-link w-100 text-muted small mt-2']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
