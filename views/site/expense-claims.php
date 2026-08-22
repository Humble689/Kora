<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\ExpenseClaims[] $pendingClaims */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'KORA ERP — Expense Claims';

$categoryBadgeMap = [
    'FUEL'         => 'bg-warning-subtle text-warning-emphasis',
    'STATIONERY'   => 'bg-info-subtle text-info-emphasis',
    'MAINTENANCE'  => 'bg-secondary-subtle text-secondary-emphasis',
    'UTILITIES'    => 'bg-primary-subtle text-primary',
];
$categoryBadgeDefault = 'bg-light text-dark';
?>

<div class="site-expense-claims bg-light py-4 min-vh-100">
    <div class="container-fluid" style="max-width: 90rem;">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom pb-3 gap-3">
            <div>
                <h1 class="h3 fw-bold text-dark mb-0">Expense Claims &amp; Petty Cash</h1>
                <p class="text-muted small mb-0">Review, authorize, and release funds for departmental purchasing requests.</p>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2 kora-header-actions">
                <a href="<?= Url::toRoute(['site/bursar']) ?>" class="btn btn-outline-secondary fw-bold rounded-3 shadow-sm px-3 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-arrow-left"></i> Back to Ledger
                </a>
                <button type="button" class="btn btn-primary fw-bold rounded-3 shadow-sm px-3 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#newClaimModal">
                    <i class="bi bi-plus-circle-fill"></i> New Claim
                </button>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4 border-warning h-100">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing:.04em;">Pending Claims</div>
                    <div class="h3 fw-bold text-dark mb-0"><?= count($pendingClaims) ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4 border-danger h-100">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing:.04em;">Pending Value</div>
                    <div class="h3 fw-bold text-dark mb-0">UGX <?= number_format(array_sum(array_map(fn($c) => (float)$c->amount, $pendingClaims)), 0) ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4 border-success h-100">
                    <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing:.04em;">Approved This Month</div>
                    <div class="h3 fw-bold text-dark mb-0">
                        UGX <?= number_format(
                            (float) \app\models\ExpenseClaims::find()
                                ->where(['school_id' => Yii::$app->user->identity->school_id, 'status' => 'APPROVED'])
                                ->andWhere(['>=', 'reviewed_at', date('Y-m-01')])
                                ->sum('amount'),
                            0
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claims table -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-4 kora-card-contained">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="card-title h6 fw-bold mb-0">
                    <i class="bi bi-clipboard-check-fill me-1"></i> Pending Departmental Requests
                </h5>
            </div>

            <!-- Desktop / tablet: full table -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover table-striped align-middle mb-0 small">
                    <thead class="table-light text-secondary text-uppercase border-bottom">
                        <tr>
                            <th class="ps-3">No</th>
                            <th>Date Submitted</th>
                            <th>Requested By</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Reference</th>
                            <th class="text-center pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingClaims)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No pending expense claims to review.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingClaims as $i => $claim): ?>
                                <?php $badgeClass = $categoryBadgeMap[$claim->category] ?? $categoryBadgeDefault; ?>
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold"><?= $i + 1 ?></td>
                                    <td class="text-muted"><?= date('Y-m-d H:i', strtotime($claim->created_at)) ?></td>
                                    <td class="fw-bold text-dark"><?= Html::encode($claim->requested_by->name ?? 'Unknown') ?></td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 fw-semibold <?= $badgeClass ?>">
                                            <?= Html::encode($claim->category) ?>
                                        </span>
                                    </td>
                                    <td class="text-secondary"><?= Html::encode($claim->description) ?></td>
                                    <td class="fw-bold text-dark text-end">UGX <?= number_format((float)$claim->amount, 0) ?></td>
                                    <td class="text-muted font-monospace small"><?= $claim->reference_number ? Html::encode($claim->reference_number) : '—' ?></td>
                                    <td class="text-center pe-3">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <?= Html::beginForm(['site/expense-claims'], 'post', ['class' => 'd-inline']) ?>
                                                <?= Html::hiddenInput('claim_id', $claim->id) ?>
                                                <?= Html::hiddenInput('decision', 'APPROVE') ?>
                                                <button type="submit" class="btn btn-sm btn-success fw-bold" title="Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            <?= Html::endForm() ?>
                                            <?= Html::beginForm(['site/expense-claims'], 'post', ['class' => 'd-inline']) ?>
                                                <?= Html::hiddenInput('claim_id', $claim->id) ?>
                                                <?= Html::hiddenInput('decision', 'REJECT') ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" title="Reject" onclick="return confirm('Reject this claim?');">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            <?= Html::endForm() ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile: stacked claim cards — every field shown, no side-scrolling -->
            <div class="d-md-none">
                <?php if (empty($pendingClaims)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        No pending expense claims to review.
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingClaims as $i => $claim): ?>
                        <?php $badgeClass = $categoryBadgeMap[$claim->category] ?? $categoryBadgeDefault; ?>
                        <div class="kora-claim-card border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="fw-bold text-dark"><?= Html::encode($claim->requested_by->name ?? 'Unknown') ?></div>
                                <div class="fw-bold text-dark text-nowrap">UGX <?= number_format((float)$claim->amount, 0) ?></div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="badge rounded-pill px-3 py-2 fw-semibold <?= $badgeClass ?>">
                                    <?= Html::encode($claim->category) ?>
                                </span>
                                <span class="text-muted small">#<?= $i + 1 ?> &middot; <?= date('Y-m-d H:i', strtotime($claim->created_at)) ?></span>
                            </div>

                            <div class="small text-secondary mb-2"><?= Html::encode($claim->description) ?></div>

                            <div class="small text-muted mb-3">
                                Ref: <span class="font-monospace"><?= $claim->reference_number ? Html::encode($claim->reference_number) : '—' ?></span>
                            </div>

                            <div class="d-flex gap-2">
                                <?= Html::beginForm(['site/expense-claims'], 'post', ['class' => 'm-0 flex-fill']) ?>
                                    <?= Html::hiddenInput('claim_id', $claim->id) ?>
                                    <?= Html::hiddenInput('decision', 'APPROVE') ?>
                                    <button type="submit" class="btn btn-sm btn-success fw-bold w-100" title="Approve">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                <?= Html::endForm() ?>
                                <?= Html::beginForm(['site/expense-claims'], 'post', ['class' => 'm-0 flex-fill']) ?>
                                    <?= Html::hiddenInput('claim_id', $claim->id) ?>
                                    <?= Html::hiddenInput('decision', 'REJECT') ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold w-100" title="Reject" onclick="return confirm('Reject this claim?');">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                <?= Html::endForm() ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- New Claim Modal -->
<div class="modal fade kora-modal" id="newClaimModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <?= Html::beginForm(['site/expense-claims-create'], 'post') ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i> New Expense Claim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="FUEL">Fuel</option>
                        <option value="STATIONERY">Stationery / Chalk / Supplies</option>
                        <option value="MAINTENANCE">Maintenance &amp; Repairs</option>
                        <option value="UTILITIES">Utilities</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="e.g. 20L diesel for school van, term 2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount (UGX)</label>
                    <input type="number" name="amount" class="form-control" min="0" step="500" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Requested by</label>
                    <input type="text" name="requested_by" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn kora-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn kora-btn-confirm kora-btn-primary">Submit Claim</button>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>

<style>
    :root {
        --kora-blue-900: #0b2a52;
        --kora-blue-800: #0f3a70;
        --kora-blue-700: #14488a;
        --kora-blue-accent: #3b82f6;
        --kora-blue-soft: rgba(59, 130, 246, 0.1);
        --kora-border: #e7ecf3;
    }

    .kora-modal .modal-content {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 45px rgba(11, 42, 82, 0.25);
    }

    .kora-modal .modal-header {
        background: linear-gradient(135deg, var(--kora-blue-800), var(--kora-blue-700));
        color: #fff;
        border: none;
        padding: 1.1rem 1.4rem;
    }

    .kora-modal .modal-title {
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .kora-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: 0.85;
    }

    .kora-modal .modal-body {
        padding: 1.4rem;
        background: #fff;
    }

    .kora-modal .form-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
    }

    .kora-modal .form-control,
    .kora-modal .form-select,
    .kora-modal textarea.form-control {
        border: 1px solid var(--kora-border);
        border-radius: 8px;
    }

    .kora-modal .form-control:focus,
    .kora-modal .form-select:focus {
        border-color: var(--kora-blue-accent);
        box-shadow: 0 0 0 0.2rem var(--kora-blue-soft);
    }

    .kora-modal .modal-footer {
        border-top: 1px solid var(--kora-border);
        background: #fafbfd;
        padding: 1rem 1.4rem;
    }

    .kora-modal .kora-btn-cancel {
        border-radius: 50px;
        font-weight: 700;
        background: #fff;
        border: 1px solid var(--kora-border);
        color: #64748b;
        padding: 0.5rem 1.15rem;
    }

    .kora-modal .kora-btn-cancel:hover {
        background: #f4f7fb;
    }

    .kora-modal .kora-btn-confirm {
        border-radius: 50px;
        font-weight: 700;
        border: none;
        padding: 0.5rem 1.25rem;
        color: #fff;
    }

    .kora-modal .kora-btn-primary { background: var(--kora-blue-accent); }
    .kora-modal .kora-btn-primary:hover { background: #2563eb; color: #fff; }

    .kora-card-contained {
        overflow: hidden;
    }

    .kora-claim-card:last-child {
        border-bottom: none !important;
    }
    .kora-claim-card:nth-child(odd) {
        background-color: #fafbfd;
    }

    @media (max-width: 575.98px) {
        .site-expense-claims .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        .site-expense-claims h1.h3 {
            font-size: 1.2rem;
        }
        .kora-header-actions {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }
        .kora-header-actions .btn {
            width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.kora-modal').forEach(function (modalEl) {
        document.body.appendChild(modalEl);
    });
});
</script>