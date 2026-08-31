<?php
/** @var yii\web\View $this */
/** @var app\models\StudentLookup $model */

use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'KORA Canteen Counter Terminal';
?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 border-secondary-subtle">
    <div>
        <span class="text-success text-uppercase tracking-wider fw-bold small d-block mb-1" style="font-size: 11px;">
            Canteen Check-Out Counter
        </span>
        <h1 class="h3 fw-bold text-dark mb-1">Cashless Pocket Money Terminal</h1>
        <p class="text-muted small mb-0">Scan barcode or enter the student 10-digit code to execute terminal wallet deductions.</p>
            </div>
        <?php
        $deviceLabel = 'No device assigned — contact your administrator';
        $deviceOk = false;

        if ($assignedDevice) {
            if ($assignedDevice->status === 'ACTIVE') {
                $deviceLabel = $assignedDevice->label ?: $assignedDevice->device_uid;
                $deviceOk = true;
            } else {
                $deviceLabel = ($assignedDevice->label ?: $assignedDevice->device_uid) . ' (deactivated)';
            }
        }
        ?>
        <div class="text-end">
            <span class="badge <?= $deviceOk ? 'bg-light text-dark' : 'bg-danger text-white' ?> border small" id="posDeviceBadge">
                <i class="bi bi-hdd-stack"></i> <span id="posDeviceLabel"><?= Html::encode($deviceLabel) ?></span>
            </span>
        </div>
</div>  

<div class="site-canteen bg-light py-5 min-vh-100 text-dark">
    <div class="container-fluid" style="max-width: 1200px;">
        
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 border-secondary-subtle">
            <div>
                <span class="text-success text-uppercase tracking-wider  fw-bold small d-block mb-1" style="font-size: 11px;">
                    Canteen Check-Out Counter
                </span>
                <h1 class="h3 fw-bold text-dark mb-1">Cashless Pocket Money Terminal</h1>
                <p class="text-muted small mb-0">Scan barcode or enter the student 10-digit code to execute terminal wallet deductions.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 bg-white p-4 rounded-3 border-start border-4 shadow-sm">
                    <h5 class="fw-bold mb-3 text-success d-flex align-items-center gap-2 small text-uppercase tracking-wide">
                        <i class="bi bi-search"></i> Account Lookup
                    </h5>
                    
                    <?php $form = ActiveForm::begin([
                        'id' => 'posLookupForm',
                        'action' => Url::toRoute(['site/lookup']),
                        'options' => ['onsubmit' => 'executePosLookup(event)'],
                    ]); ?>

                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase tracking-wider" style="font-size: 11px;">10-Digit Code</label>
                            <?= $form->field($model, 'payment_code')->textInput([
                                'id' => 'posCodeInput',
                                'maxlength' => 10,
                                'class' => 'form-control form-control-lg bg-light text-dark border-secondary-subtle text-center tracking-wide fw-bold py-2.5 shadow-sm',
                                'placeholder' => '0000000000',
                                'autocomplete' => 'off'
                            ])->label(false) ?>
                        </div>

                        <button type="submit" id="posSubmitBtn" class="btn btn-success btn-lg w-100 rounded-2 fw-bold shadow-sm small py-2 text-uppercase tracking-wide" style="font-size: 13px;">
                            Pull Student Profile
                        </button>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="col-12 col-md-8">
                <div id="terminalAlertContainer" class="d-none mb-3"></div>

                <div id="terminalScreenPlaceholder" class="card border-0 bg-white p-5 rounded-3 border border-dashed border-secondary-subtle text-center text-muted shadow-sm">
                    <i class="bi bi-calculator fs-1 mb-2 text-secondary"></i>
                    <div class="fw-semibold text-dark-50">Waiting for transaction authorization input...</div>
                    <div class="small text-muted mt-1">Ready to receive hardware scanner reads or keypad configurations.</div>
                </div>

                <div id="terminalScreenActive" class="card border-0 bg-white text-dark rounded-3 shadow-sm d-none overflow-hidden border-start border-4">
                    <div class="card-header bg-primary text-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title h6 fw-bold mb-0 d-flex align-items-center gap-2 text-uppercase text-white tracking-wide" style="font-size: 12px;">
                            <i class="bi bi-person-badge"></i> Active Student File
                        </h5>
                        <span class="badge bg-white tracking-wide text-uppercase" id="posClassLevel">-</span>
                    </div>

                    <div class="card-body p-4">
                        <div class="mb-4">
                            <span class="text-muted small fw-bold text-uppercase tracking-wider" style="font-size: 11px;">Identified Account</span>
                            <h2 class="h3 fw-bold text-dark mt-1 mb-0" id="posStudentName">-</h2>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 border border-secondary-subtle">
                                    <div class="text-muted small fw-bold text-uppercase tracking-wider mb-1" style="font-size: 11px;">Available S-Wallet</div>
                                    <div class="h4 fw-bold text-success mb-0">UGX <span id="posSwalletBalance">0</span></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 border border-secondary-subtle">
                                    <div class="text-muted small fw-bold text-uppercase tracking-wider mb-1" style="font-size: 11px;">Daily Limit Remaining</div>
                                    <div class="h4 fw-bold text-dark mb-0">UGX <span id="posDailyLimit">0</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 bg-light p-4 rounded-3 border border-secondary-subtle">
                            <label class="form-label fw-bold text-secondary text-uppercase tracking-wider small mb-2" style="font-size: 11px;">Total Cart Amount to Charge</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-muted" style="font-size: 14px;">UGX</span>
                                <input type="number" id="posChargeAmount" class="form-control fw-bold text-dark border-start-0 ps-1" placeholder="0" min="100">
                            </div>
                        </div>

                        <button id="confirmPurchaseBtn" onclick="commitCanteenDeduction()" class="btn btn-outline-danger btn-lg w-100 rounded-2 fw-bold shadow-sm py-3 text-uppercase tracking-wide" style="font-size: 14px;">
                            Confirm Purchase & Deduct Funds
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.tracking-wide { letter-spacing: 0.05em; }
</style>



<!-- <link rel="stylesheet" href="https://jsdelivr.net"> -->



<script>



function executePosLookup(event) {
    event.preventDefault();
    const form = document.getElementById('posLookupForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('posSubmitBtn');
    
    submitBtn.disabled = true;
    submitBtn.innerText = "Searching Wallet...";

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerText = "Pull Student Profile";

        if (data.success) {
            document.getElementById('posStudentName').innerText = data.name;
            document.getElementById('posClassLevel').innerText = data.class_level;
            document.getElementById('posSwalletBalance').innerText = data.swallet_balance;
            document.getElementById('posDailyLimit').innerText = data.daily_spend_limit;
            
            document.getElementById('posChargeAmount').value = '';
            document.getElementById('terminalScreenPlaceholder').classList.add('d-none');
            document.getElementById('terminalScreenActive').classList.remove('d-none');
        } else {
            document.getElementById('terminalScreenActive').classList.add('d-none');
            document.getElementById('terminalScreenPlaceholder').classList.remove('d-none');
            alert(data.message);
        }
    });
}
function commitCanteenDeduction() {
    const code = document.getElementById('posCodeInput').value;
    const amount = document.getElementById('posChargeAmount').value;

    if (!amount || amount <= 0) {
        alert("Please enter a valid checkout transaction value.");
        return;
    }

    if (!confirm(`Charge student wallet UGX ${Number(amount).toLocaleString()} for this canteen checkout?`)) {
        return;
    }

    const idempotencyKey = crypto.randomUUID();   

    const params = new URLSearchParams();
    params.append('payment_code', code);
    params.append('amount', amount);
    params.append('idempotency_key', idempotencyKey);   
    params.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->getCsrfToken() ?>');

    fetch('<?= yii\helpers\Url::toRoute(['site/canteen-debit']) ?>', {
        method: 'POST',
        body: params,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('posSwalletBalance').innerText = data.new_swallet;
            document.getElementById('posDailyLimit').innerText = data.new_remaining_limit;
            document.getElementById('posChargeAmount').value = '';
        } else {
            alert("POS Execution Rejected:\n" + data.message);
        }
    });
}
</script>



<style>
.tracking-wide { letter-spacing: 0.1em; }
.uppercase { text-transform: uppercase; }
</style>
