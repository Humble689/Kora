<?php
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Edit Student — ' . $model->name;
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css');

$hasFloor = $model->price_floor !== null;
$hasCeiling = $model->price_ceiling !== null;
?>
<style>
.edit-student-page { background: linear-gradient(180deg, var(--theme-soft, #f0fdf4) 0%, #f7f8fa 55%); min-height: 100vh; padding: 3rem 1rem; }
.edit-student-shell { max-width: 560px; margin: 0 auto; }

.es-card { background: #fff; border: 1px solid var(--theme-border, #eceef1); border-radius: 20px; box-shadow: 0 10px 30px rgba(16, 24, 40, .06); overflow: hidden; }
.es-header { padding: 1.75rem 2rem 1.25rem; border-bottom: 1px solid var(--theme-border, #f1f2f4); }
.es-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--theme-primary, #16a34a); }
.es-title { font-size: 1.35rem; font-weight: 800; color: var(--theme-text, #111827); margin: .2rem 0 .3rem; }
.es-code { font-size: .8rem; color: #9ca3af; }
.es-code span { font-family: monospace; font-weight: 700; color: var(--theme-text, #374151); background: var(--theme-soft, #f9fafb); padding: .1rem .5rem; border-radius: 6px; }

.es-body { padding: 1.75rem 2rem 2rem; }
.es-section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin: 1.6rem 0 .9rem; display: flex; align-items: center; gap: .4rem; }
.es-section-label:first-child { margin-top: 0; }

.es-field label { font-size: .8rem; font-weight: 700; color: var(--theme-muted, #4b5563); margin-bottom: .35rem; }
.es-field .form-control, .es-field .form-select {
    border: 1px solid var(--theme-border, #e5e7eb);
    border-radius: 10px;
    padding: .6rem .85rem;
    font-size: .92rem;
}
.es-field .form-control:focus, .es-field .form-select:focus {
    border-color: var(--theme-primary, #16a34a);
    box-shadow: 0 0 0 .2rem rgba(22, 163, 74, .12);
}
.es-hint { font-size: .76rem; color: #9ca3af; margin-top: .3rem; }

.guardrail-toggle {
    border: 1px solid var(--theme-border, #e5e7eb);
    border-radius: 14px;
    padding: 1rem 1.1rem;
    margin-bottom: .9rem;
    transition: border-color .15s;
}
.guardrail-toggle.enabled { border-color: var(--theme-primary, #16a34a); background: var(--theme-soft, #f0fdf4); }
.guardrail-toggle-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
.guardrail-toggle-head h6 { font-weight: 700; font-size: .9rem; margin-bottom: .2rem; color: var(--theme-text, #111827); }
.guardrail-toggle-head p { font-size: .78rem; color: #9ca3af; margin: 0; }
.guardrail-input-block { margin-top: .9rem; }
.guardrail-input-block.d-none { display: none; }

.es-actions { margin-top: 1.75rem; display: flex; flex-direction: column; gap: .5rem; }
.btn-es-save { background: var(--theme-primary, #16a34a); border: none; color: #fff; font-weight: 700; padding: .75rem; border-radius: 12px; }
.btn-es-save:hover { opacity: .92; color: #fff; }
</style>

<div class="edit-student-page">
    <div class="edit-student-shell">
        <div class="es-card">

            <div class="es-header">
                <span class="es-eyebrow">Student Directory</span>
                <h1 class="es-title"><?= Html::encode(ucfirst($model->name)) ?></h1>
                <div class="es-code">Payment Code: <span><?= Html::encode($model->payment_code) ?></span></div>
            </div>

            <div class="es-body">
                <?php $form = ActiveForm::begin(['layout' => 'default']); ?>

                    <div class="es-section-label"><i class="bi bi-person-vcard"></i> Basic Details</div>

                    <div class="es-field mb-3">
                        <?= $form->field($model, 'name', ['template' => "{label}\n{input}\n{error}"])
                            ->textInput(['name' => 'Students[name]', 'required' => true])
                            ->label('Student Name') ?>
                    </div>

                    <div class="es-field mb-3">
                        <?= $form->field($model, 'class_level', ['template' => "{label}\n{input}\n{error}"])
                            ->dropDownList([
                                'Primary 1' => 'Primary 1', 'Primary 2' => 'Primary 2', 'Primary 3' => 'Primary 3', 'Primary 4' => 'Primary 4', 'Primary 5' => 'Primary 5', 'Primary 6' => 'Primary 6', 'Primary 7' => 'Primary 7',
                                'Senior 1' => 'Senior 1', 'Senior 2' => 'Senior 2', 'Senior 3' => 'Senior 3', 'Senior 4' => 'Senior 4', 'Senior 5' => 'Senior 5', 'Senior 6' => 'Senior 6',
                            ], ['name' => 'Students[class_level]', 'class' => 'form-select', 'required' => true])
                            ->label('Class') ?>
                    </div>

                    <div class="es-section-label"><i class="bi bi-piggy-bank"></i> Wallet Controls</div>

                    <div class="es-field mb-1">
                        <?= $form->field($model, 'daily_spend_limit', ['template' => "{label}\n{input}\n{error}"])
                            ->textInput(['name' => 'Students[daily_spend_limit]', 'type' => 'number', 'required' => true])
                            ->label('Daily Spend Cap (UGX)') ?>
                        <div class="es-hint">Total the student can spend across all canteen purchases in one day.</div>
                    </div>

                    <div class="es-section-label"><i class="bi bi-sliders"></i> Per-Purchase Guardrails <span class="badge bg-light text-muted border ms-1" style="font-size: 10px; font-weight: 600;">Optional</span></div>

                    <div class="es-section-label"><i class="bi bi-bell"></i> Low Balance Alerts <span class="badge bg-light text-muted border ms-1" style="font-size: 10px; font-weight: 600;">Optional</span></div>

<?php $lowBalanceEnabled = $model->low_balance_enabled ?? false; ?>
<div class="guardrail-toggle <?= $lowBalanceEnabled ? 'enabled' : '' ?>" id="lowBalanceToggleCard">
    <div class="guardrail-toggle-head">
        <div>
            <h6>Notify Parent When Balance Is Low</h6>
            <p>Sends an email when the wallet drops to or below a set amount.</p>
        </div>
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" name="Students[low_balance_enabled]" value="1" id="lowBalanceEnabled" <?= $lowBalanceEnabled ? 'checked' : '' ?> onchange="toggleLowBalanceBlock()">
        </div>
    </div>
    <div class="guardrail-input-block <?= $lowBalanceEnabled ? '' : 'd-none' ?>" id="lowBalanceInputBlock">
        <label class="form-label small fw-semibold mt-2">Parent Email</label>
        <input type="email" name="Students[parent_email]" id="parentEmailInput" class="form-control mb-2" placeholder="parent@example.com" value="<?= Html::encode($model->parent_email ?? '') ?>">

        <label class="form-label small fw-semibold">Alert Threshold (UGX)</label>
        <input type="number" name="Students[low_balance_threshold]" id="lowBalanceThresholdInput" class="form-control" placeholder="e.g. 5000" value="<?= Html::encode($model->low_balance_threshold ?? '') ?>">
    </div>
</div>

<script>
function toggleLowBalanceBlock() {
    const checkbox = document.getElementById('lowBalanceEnabled');
    const card = document.getElementById('lowBalanceToggleCard');
    const block = document.getElementById('lowBalanceInputBlock');

    if (checkbox.checked) {
        card.classList.add('enabled');
        block.classList.remove('d-none');
    } else {
        card.classList.remove('enabled');
        block.classList.add('d-none');
        document.getElementById('parentEmailInput').value = '';
        document.getElementById('lowBalanceThresholdInput').value = '';
    }
}
</script>

                    <div class="guardrail-toggle <?= $hasFloor ? 'enabled' : '' ?>" id="floorToggleCard">
                        <div class="guardrail-toggle-head">
                            <div>
                                <h6>Minimum Purchase Amount</h6>
                                <p>Blocks very small, frequent purchases (e.g. spare-change snacking).</p>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="floorEnabled" <?= $hasFloor ? 'checked' : '' ?> onchange="toggleGuardrail('floor')">
                            </div>
                        </div>
                        <div class="guardrail-input-block <?= $hasFloor ? '' : 'd-none' ?>" id="floorInputBlock">
                            <input type="number" name="Students[price_floor]" id="priceFloorInput"
                                   class="form-control" placeholder="e.g. 500"
                                   value="<?= Html::encode($model->price_floor ?? '') ?>">
                        </div>
                    </div>

                    <div class="guardrail-toggle <?= $hasCeiling ? 'enabled' : '' ?>" id="ceilingToggleCard">
                        <div class="guardrail-toggle-head">
                            <div>
                                <h6>Maximum Purchase Amount</h6>
                                <p>Caps how much can be spent in a single transaction.</p>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="ceilingEnabled" <?= $hasCeiling ? 'checked' : '' ?> onchange="toggleGuardrail('ceiling')">
                            </div>
                        </div>
                        <div class="guardrail-input-block <?= $hasCeiling ? '' : 'd-none' ?>" id="ceilingInputBlock">
                            <input type="number" name="Students[price_ceiling]" id="priceCeilingInput"
                                   class="form-control" placeholder="e.g. 10000"
                                   value="<?= Html::encode($model->price_ceiling ?? '') ?>">
                        </div>
                    </div>

                    <div class="es-actions">
                        <?= Html::submitButton('Save Changes', ['class' => 'btn btn-es-save']) ?>
                        <?= Html::a('Cancel', ['site/bursar'], ['class' => 'btn btn-link text-muted small text-center']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleGuardrail(type) {
    const checkbox = document.getElementById(type + 'Enabled');
    const card = document.getElementById(type + 'ToggleCard');
    const block = document.getElementById(type + 'InputBlock');
    const input = document.getElementById('price' + (type === 'floor' ? 'Floor' : 'Ceiling') + 'Input');

    if (checkbox.checked) {
        card.classList.add('enabled');
        block.classList.remove('d-none');
    } else {
        card.classList.remove('enabled');
        block.classList.add('d-none');
        input.value = ''; 
    }
}


</script>

<style>
.form-check-input:not(:checked) {
    background-color: #94a3b8 !important; 
    border-color: #64748b !important;    
    opacity: 1 !important;              
}

.form-check-input:checked {
    background-color: #0d6efd !important; 
    border-color: #0d6efd !important;
}
</style>
