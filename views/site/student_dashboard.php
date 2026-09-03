<?php
/** @var yii\web\View $this */
/** @var app\models\Students $student */
/** @var app\models\Transactions[] $statementLogs */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'KORA Portal - ' . $student->name;
$sponsorshipEnabled = filter_var($student->sponsorship_enabled, FILTER_VALIDATE_BOOLEAN);
?>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const sponsorToggle = document.getElementById('sponsorToggle');
    if (!sponsorToggle) return;

    sponsorToggle.addEventListener('change', function () {
        const toggle = this;
        const params = new URLSearchParams();
        params.append('enable', toggle.checked ? '1' : '0');
        params.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->getCsrfToken() ?>');

        fetch('<?= Url::toRoute(['site/sponsor-toggle', 'code' => $student->payment_code]) ?>', {
            method: 'POST',
            body: params,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(res => res.json())
        .then(data => {
            const block = document.getElementById('sponsorLinkBlock');
            if (!data.success) {
                toggle.checked = !toggle.checked;
                alert(data.message || 'The sponsorship setting could not be saved.');
                return;
            }

            if (data.sponsorship_enabled) {
                document.getElementById('sponsorLinkInput').value = data.sponsor_url;
                block.classList.remove('d-none');
            } else {
                block.classList.add('d-none');
            }
        })
        .catch(() => {
            toggle.checked = !toggle.checked;
            alert('The sponsorship setting could not be saved.');
        });
    });
});

function copySponsorLink() {
    const copyText = document.getElementById("sponsorLinkInput");
    if (!copyText) return;

    // Select the text field content
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices

    // Target the button for visual feedback
    const copyBtn = document.querySelector("#sponsorLinkBlock .btn");
    const originalText = copyBtn ? copyBtn.innerText : "Copy";

    // Helper function to update button state
    function showSuccess() {
        if (copyBtn) {
            copyBtn.innerText = "Copied!";
            copyBtn.classList.replace("btn-outline-secondary", "btn-success");
            setTimeout(() => {
                copyBtn.innerText = originalText;
                copyBtn.classList.replace("btn-success", "btn-outline-secondary");
            }, 2000);
        } else {
            alert("Link copied to clipboard!");
        }
    }

    // Modern API Attempt
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(copyText.value)
            .then(showSuccess)
            .catch(err => useFallback(copyText.value));
    } else {
        // Fallback for older browsers or HTTP environments
        useFallback(copyText.value);
    }

    function useFallback(text) {
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showSuccess();
            } else {
                alert("Failed to copy. Please manually copy the link.");
            }
        } catch (err) {
            alert("Fallback failed. Please manually copy the link.");
        }
    }
}
</script>


<div class="site-student-dashboard bg-light py-5 min-vh-100">
    <div class="container max-w-5xl">

    <div class="card border-0 shadow-sm rounded-3 p-3 mb-3" id="sponsorshipCard">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-1">Allow Sponsorship</h6>
            <p class="text-muted small mb-0">Let relatives or well-wishers top up this wallet directly.</p>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="sponsorToggle" <?= $sponsorshipEnabled ? 'checked' : '' ?>>
        </div>
    </div>

    <div id="sponsorLinkBlock" class="mt-3 <?= $sponsorshipEnabled ? '' : 'd-none' ?>">
        <label class="form-label small fw-semibold">Share this link:</label>
        <div class="input-group">
            <input type="text" id="sponsorLinkInput" class="form-control form-control-sm" readonly
                   value="<?= $sponsorshipEnabled ? Html::encode(Url::toRoute(['site/sponsor', 'code' => $student->sponsor_code], true)) : '' ?>">
            <button class="btn btn-outline-secondary btn-sm" onclick="copySponsorLink()">Copy</button>
        </div>
    </div>
</div>
        
        <!-- Dashboard Profile Banner -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="text-center fw-bold text-xs">Secure Statement Link</div>
                    <h1 class="h3 fw-bold text-dark mb-0"><?= \yii\helpers\Html::encode($student->name) ?></h1>
                    <p class="text-muted small mb-0"><?= $student->school ? $student->school->name : 'N/A' ?> • <span class="fw-semibold text-secondary"><?= $student->class_level ?></span></p>
                </div>
                <div class="d-flex gap-2">
                <div class="d-flex gap-2">
                    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'BURSAR'): ?>
                        <a href="<?= Url::toRoute(['site/bursar']) ?>" class="btn btn-outline-secondary px-3 rounded-3 fw-bold small">
                            <i class="bi bi-arrow-left me-1"></i> Return to Bursar Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?= Url::toRoute(['site/index']) ?>" class="btn btn-outline-secondary px-3 rounded-3 fw-bold small">
                            <i class="bi bi-arrow-left me-1"></i> Exit Portal
                        </a>
                    <?php endif; ?>

                    
                </div>
                    <a href="<?= Url::toRoute(['site/download-statement', 'code' => $student->payment_code]) ?>" class="btn btn-dark px-3 rounded-3 fw-bold small"><i class="bi bi-file-earmark-arrow-down me-1"></i> Download Ledger</a>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-whiteborder-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Module 1: Tuition Due Balance</span>
                        <i class="bi bi-wallet2 text-primary fs-4"></i>
                    </div>
                    <div class="h2 fw-black text-dark mb-0">UGX <?= number_format((float)$student->tuition_balance, 0) ?></div>
                </div>
            </div>
            
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Module 2: Pocket Money S-Wallet</span>
                        <i class="bi bi-piggy-bank text-success fs-4"></i>
                    </div>
                    <div class="h2 fw-black text-dark mb-0">UGX <?= number_format((float)$student->swallet_balance, 0) ?></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title h6 fw-bold mb-0 text-dark">Chronological Account History Logs</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary border-bottom">
                        <tr>
                            <th class="ps-4">Timestamp Date</th>
                            <th>Allocation Allocation</th>
                            <th>Reference Token</th>
                            <th>Method Channel</th>
                            <th class="text-end pe-4">Transaction Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($statementLogs)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No historical transactions logged for this payment account address yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($statementLogs as $log): ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= date('Y-m-d H:i', strtotime($log->created_at)) ?></td>
                                    <td>
                                        <span class="fw-bold 
                                            <?= $log->transaction_type === 'TUITION' ? 'text-primary' : ($log->transaction_type === 'POCKET_MONEY' ? 'text-success' : 'text-danger') ?>">
                                            <?= $log->transaction_type ?>
                                        </span>
                                    </td>
                                    <td class= text-secondary"><?= $log->external_reference ?></td>
                                    <td class="fw-semibold text-dark"><?= $log->payment_channel ?></td>
                                    <td class="text-end pe-4 fw-bold <?= $log->transaction_type === 'CANTEEN_SPEND' ? 'text-danger' : 'text-success' ?>">
                                        <?= $log->transaction_type === 'CANTEEN_SPEND' ? '-' : '+' ?> UGX <?= number_format((float)$log->amount, 0) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- <link rel="stylesheet" href="https://jsdelivr.net"> -->

<style>
.max-w-5xl { max-w: 64rem; }
.tracking-wider { letter-spacing: 0.05em; }
.fw-black { font-weight: 900; }
</style>

<style>
/* Force the toggle to be visible when it is OFF (not checked) */
.form-check-input:not(:checked) {
    background-color: #94a3b8 !important; /* Medium slate gray */
    border-color: #64748b !important;     /* Darker border gray */
    opacity: 1 !important;                 /* Ensures no transparency hides it */
}

/* Optional: Customize the color when it is ON (checked) */
.form-check-input:checked {
    background-color: #0d6efd !important;  /* Bootstrap Blue */
    border-color: #0d6efd !important;
}
</style>

