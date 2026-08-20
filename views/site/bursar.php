<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $stats */
/** @var array $channelBreakdown */
/** @var array $defaulterHeatmap */
/** @var array $collectionVelocity */
/** @var array $reconciliationByChannel */
/** @var array $settlementRelevantChannels */
/** @var app\models\Transactions[] $recentTransactions */
/** @var yii\data\Pagination $txPages */
/** @var string $txSearchKeyword */
/** @var string $searchKeyword */

use yii\bootstrap5\LinkPager;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\models\Students;

$this->title = 'KORA ERP Bursar Operations';

$txBadgeMap = [
    'TUITION'       => 'text-primary',
    'POCKET_MONEY'  => 'text-success',
    'EXPENSE' => 'text-danger',
];
$txBadgeDefault = 'text-danger';

$totalCount = $txPages->totalCount ?? count($recentTransactions);
$pageStart  = $totalCount > 0 ? ($txPages->getOffset() + 1) : 0;
$pageEnd    = min($txPages->getOffset() + $txPages->getLimit(), $totalCount);

$userSchoolId = Yii::$app->user->identity->school_id;

// Reconciliation chart data — one grouped bar per relevant channel,
// Cleared vs Settled, replacing the old per-channel table.
$reconciliationLabels = [];
$reconciliationClearedValues = [];
$reconciliationSettledValues = [];
foreach ($reconciliationByChannel as $row) {
    if (!in_array($row['payment_channel'], $settlementRelevantChannels)) continue;
    $reconciliationLabels[] = $row['payment_channel'];
    $reconciliationClearedValues[] = (float) $row['network_cleared'];
    $reconciliationSettledValues[] = (float) $row['bank_settled'];
}
?>

<div class="site-bursar bg-light py-4 min-vh-100">
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
                <h1 class="h3 fw-bold text-dark mb-0">
                    <?= Yii::$app->user->identity->school ? Html::encode(Yii::$app->user->identity->school->name) : 'Financial Collections' ?> Control Centre
                </h1>
                <p class="text-muted small mb-0">Real-time ledger overview and cross-channel multi-tenant settlement audits.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?= Html::beginForm(['site/term-rollover'], 'post', [
                    'class' => 'm-0 d-inline-block',
                    'onsubmit' => "return confirm('NEW TERM ROLLOVER WARNING\\n\\nAre you completely sure you want to start a new academic term?\\n\\nThis will automatically bill every active student the new term fee of UGX " . number_format($stats['base_tuition_fees'] ?? 850000, 0) . " and carry forward previous debts.\\n\\nAll student pocket money balances WILL safely continue untouched! This cannot be undone.');"
                ]) ?>
                    <button type="submit" class="btn btn-outline-primary fw-bold rounded-3 shadow-sm px-3 d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-repeat"></i> Start New Term
                    </button>
                <?= Html::endForm() ?>

                <a href="<?= Url::toRoute(['site/register-student']) ?>" class="btn btn-primary fw-bold rounded-3 shadow-sm px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill"></i> Enroll Student
                </a>
            </div>
        </div>

        <!-- Key Bursar Actions -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <button type="button" class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#walletModal">
                <i class="bi bi-wallet-fill"></i> Wallet Top-Up / Freeze
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#voidModal">
                <i class="bi bi-x-octagon-fill"></i> Void / Reverse Transaction
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#batchInvoiceModal">
                <i class="bi bi-receipt"></i> Batch Invoice Class
            </button>

            <a href="<?= Url::toRoute(['site/expense-claims']) ?>" class="btn btn-outline-dark btn-sm fw-bold">
                <i class="bi bi-clipboard-check-fill"></i> Approve Expense Claims
            </a>
             <?= Html::beginForm(['site/force-pos-sync'], 'post', ['class' => 'd-inline']) ?>
                <button type="submit" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="bi bi-arrow-repeat"></i> Force POS Sync
                </button>
            <?= Html::endForm() ?>
        </div>

        <!-- Stat cards -->
        <div class="row g-3 mb-4">
           <div class="col-12 col-md-4">
    <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4 border-primary h-100">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing:.04em;">Total Tuition Collected</div>
                <div class="h3 fw-bold text-dark mb-0">UGX <?= number_format($stats['total_tuition'], 0) ?></div>
                <div class="text-muted small mt-1">
                    Net available: <span class="fw-semibold <?= $stats['net_available_tuition'] < 0 ? 'text-danger' : 'text-success' ?>">
                        UGX <?= number_format($stats['net_available_tuition'], 0) ?>
                    </span>
                    <?php if ($stats['total_approved_expenses'] > 0): ?>
                        <span class="text-muted">(after UGX <?= number_format($stats['total_approved_expenses'], 0) ?> approved expenses)</span>
                    <?php endif; ?>
                </div>
            </div>
            <span class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                <i class="bi bi-cash-coin fs-5"></i>
            </span>
        </div>
    </div>
</div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4 border-danger h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing:.04em;">Total Outstanding Fees</div>
                            <div class="h3 fw-bold text-dark mb-0">UGX <?= number_format($stats['total_outstanding'], 0) ?></div>
                        </div>
                        <span class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                            <i class="bi bi-exclamation-circle fs-5"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4 border-success h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing:.04em;">Active S-Wallet Vaults</div>
                            <div class="h3 fw-bold text-dark mb-0">UGX <?= number_format($stats['total_swallet'], 0) ?></div>
                        </div>
                        <span class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settlement Reconciliation Tracker + S-Wallet Float Monitor -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Settlement Reconciliation</h6>
                        <?php if (($stats['settlement_gap'] ?? 0) > 0): ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold">Gap: UGX <?= number_format($stats['settlement_gap'], 0) ?></span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success fw-semibold">Fully Settled</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small mb-3">Mobile money, card, and online gateway payments only — cash and internal wallet transfers settle differently and aren't included here.</p>

                    <div class="d-flex gap-4 mb-3">
                        <div class="flex-fill">
                            <div class="text-muted small text-uppercase fw-semibold">Network Cleared</div>
                            <div class="h4 fw-bold text-dark mb-0">UGX <?= number_format($stats['network_cleared'] ?? 0, 0) ?></div>
                        </div>
                        <div class="vr"></div>
                        <div class="flex-fill">
                            <div class="text-muted small text-uppercase fw-semibold">Bank Settled</div>
                            <div class="h4 fw-bold text-dark mb-0">UGX <?= number_format($stats['bank_settled'] ?? 0, 0) ?></div>
                        </div>
                    </div>

                    <?php if (empty($reconciliationLabels)): ?>
                        <div class="text-muted small fst-italic text-center py-4">No channel-level reconciliation data available.</div>
                    <?php else: ?>
                        <div style="position: relative; height: 190px;">
                            <canvas id="reconciliationChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-lg-6 d-flex">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100 w-100">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 me-2 text-success"></i>S-Wallet Float Monitor</h6>
                    <div class="d-flex gap-4">
                        <div class="flex-fill">
                            <div class="text-muted small text-uppercase fw-semibold">Total Float Liability</div>
                            <div class="h4 fw-bold text-dark mb-0">UGX <?= number_format($stats['swallet_float'] ?? 0, 0) ?></div>
                            <div class="text-muted small">Held in escrow for canteen use</div>
                        </div>
                        <div class="vr"></div>
                        <div class="flex-fill">
                            <div class="text-muted small text-uppercase fw-semibold">Last 7 Days Top-ups</div>
                            <div class="h4 fw-bold text-dark mb-0">UGX <?= number_format($stats['swallet_7d_topups'] ?? 0, 0) ?></div>
                            <div class="text-muted small">Inflow into student wallets</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Channel Utilization + Defaulter Heatmap -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Channel Utilization</h6>
                    <canvas id="channelDonut" height="220"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-grid-3x3-gap-fill me-2 text-danger"></i>Defaulter Heatmap by Class</h6>
                    <?php if (empty($defaulterHeatmap)): ?>
                        <div class="text-muted small fst-italic text-center py-4">No class-level data available.</div>
                    <?php else: ?>
                        <?php
                        $maxOutstanding = max(array_column($defaulterHeatmap, 'total_outstanding') ?: [1]);
                        if ($maxOutstanding <= 0) $maxOutstanding = 1;
                        ?>
                        <div class="row g-2">
                            <?php foreach ($defaulterHeatmap as $row):
                                $intensity = min(1, ((float)$row['total_outstanding']) / $maxOutstanding);
                                $bg = sprintf('rgba(220, 53, 69, %.2f)', 0.12 + ($intensity * 0.78));
                                $textClass = $intensity > 0.45 ? 'text-white' : 'text-dark';
                            ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="rounded-3 p-2 text-center h-100 <?= $textClass ?>" style="background-color: <?= $bg ?>;">
                                        <div class="small fw-semibold text-truncate" style="font-size: 0.72rem;"><?= Html::encode($row['class_level']) ?></div>
                                        <div class="fw-bold" style="font-size: 0.85rem;">UGX <?= number_format((float)$row['total_outstanding'], 0) ?></div>
                                        <div class="small" style="font-size: 0.68rem; opacity: 0.85;"><?= (int)$row['defaulter_count'] ?> defaulter<?= (int)$row['defaulter_count'] === 1 ? '' : 's' ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="small text-muted mt-2">Darker tiles indicate a higher concentration of outstanding fees within that class.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Collection Velocity Graph -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Collection Velocity — This Term vs Last Term</h6>
                    <canvas id="velocityChart" height="90"></canvas>
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-4">
            <div class="card-header bg-primary text-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="card-title h6 fw-bold mb-0">
                    <i class="bi bi-receipt-cutoff me-1"></i> Recent Clearing Network Receipts
                </h5>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="<?= Url::toRoute(['site/export-receipts', 'tx_q' => $txSearchKeyword]) ?>" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1 fw-bold">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Save Spreadsheet
                    </a>
                </div>

                    <form method="get" action="<?= Url::toRoute(['site/bursar']) ?>" class="d-flex m-0 gap-1" style="max-width: 350px; width: 100%;">
                        <div class="input-group input-group-sm">
                            <input type="hidden" name="q" value="<?= Html::encode($searchKeyword ?? '') ?>">
                            <input type="text" name="tx_q" value="<?= Html::encode($txSearchKeyword ?? '') ?>" class="form-control bg-white text-dark border-0 rounded-start-2" placeholder="Search reference, type, or channel..." autocomplete="off">
                            <?php if (!empty($txSearchKeyword)): ?>
                                <a href="<?= Url::toRoute(['site/bursar', 'q' => ($searchKeyword ?? '')]) ?>" class="btn btn-light border-0 text-muted d-flex align-items-center px-2">
                                    <i class="bi bi-x-circle-fill"></i>
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-dark fw-bold border-0 px-3">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 small">
                    <thead class="table-light text-secondary text-uppercase border-bottom">
                        <tr>
                            <th class="ps-3">No</th>
                            <th class="ps-4">Timestamp Date</th>
                            <th>Student Name</th>
                            <th>Allocation Profile</th>
                            <th>Gateway Channel</th>
                            <th>Clearing Token Reference</th>
                            <th class="text-end pe-4">Settlement Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No historical transactions logged match the filter criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentTransactions as $i => $tx): ?>
                                <?php $badgeClass = $txBadgeMap[$tx->transaction_type] ?? $txBadgeDefault; ?>
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold"><?= $txPages->getOffset() + $i + 1 ?></td>

                                    <td class="ps-4 text-muted"><?= date('Y-m-d H:i', strtotime($tx->created_at)) ?></td>

                                   <td class="fw-bold text-dark">
                                        <?php if ($tx->transaction_type === 'EXPENSE'): ?>
                                            <span class="text-muted fst-italic"><i class="bi bi-receipt me-1"></i>Petty Cash / Dept. Expense</span>
                                        <?php elseif ($tx->student): ?>
                                            <?= Html::encode($tx->student->name) ?>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Unknown Record</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 fw-semibold <?= $badgeClass ?>">
                                            <?= Html::encode($tx->transaction_type) ?>
                                        </span>
                                        <?php if (($tx->status ?? null) === 'VOIDED'): ?>
                                            <span class="badge bg-secondary rounded-pill">Voided</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-secondary fw-semibold"><?= Html::encode($tx->payment_channel) ?></td>

                                    <td>
                                        <span class="bg-light px-2 py-1 rounded border small d-inline-block font-monospace">
                                            <?= Html::encode($tx->external_reference) ?>
                                        </span>
                                    </td>

                                    <td class="pe-4 fw-bold text-dark text-end">
                                        UGX <?= number_format((float)$tx->amount, 0) ?>
                                        <a href="<?= Url::toRoute(['site/print-receipt', 'id' => $tx->id]) ?>" target="_blank" class="btn btn-sm btn-light border ms-2" title="Print Receipt">
                                            <i class="bi bi-printer-fill"></i>
                                        </a>
                                        <?php if (($tx->status ?? null) !== 'VOIDED'): ?>
                                            <button type="button" class="btn btn-sm btn-light border ms-1 kora-void-trigger" title="Void / Reverse This Transaction"
                                                    data-bs-toggle="modal" data-bs-target="#voidModal"
                                                    data-tx-id="<?= (int)$tx->id ?>"
                                                    data-tx-label="<?= Html::encode(($tx->student->name ?? 'Unknown') . ' — ' . $tx->transaction_type . ' — UGX ' . number_format((float)$tx->amount, 0) . ' (' . date('Y-m-d', strtotime($tx->created_at)) . ')') ?>">
                                                <i class="bi bi-x-octagon text-danger"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white d-flex flex-column flex-md-row justify-content-between align-items-center pt-3 border-top gap-2">
                <div class="text-muted small">
                    <?php if ($totalCount > 0): ?>
                        Showing <strong><?= $pageStart ?>&ndash;<?= $pageEnd ?></strong> of <strong><?= $totalCount ?></strong> receipts
                    <?php else: ?>
                        No receipts to show
                    <?php endif; ?>
                </div>
                <?= LinkPager::widget([
                    'pagination' => $txPages,
                    'maxButtonCount' => 5,
                    'options' => ['class' => 'pagination pagination-sm mb-0'],
                ]) ?>
            </div>
        </div>
    </div>
</div>

<!-- ===================== MODALS ===================== -->

<!-- Wallet Modal -->
<div class="modal fade kora-modal" id="walletModal" tabindex="-1">
    <div class="modal-dialog">
        <?= Html::beginForm(['site/wallet-adjust'], 'post') ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-wallet-fill"></i> Wallet Top-Up / Freeze</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Step 1 — Class</label>
                    <select id="walletClassSelect" class="form-select" required>
                        <option value="">Select class...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Step 2 — Student</label>
                    <select name="student_id" id="walletStudentSelect" class="form-select" required disabled>
                        <option value="">Select a class first...</option>
                    </select>
                    <div class="form-text">Search by name — payment code shown to tell same-name students apart.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Action</label>
                    <select name="wallet_action" class="form-select" id="walletActionSelect" required>
                        <option value="TOPUP">Top-Up</option>
                        <option value="FREEZE">Freeze Wallet</option>
                        <option value="UNFREEZE">Unfreeze Wallet</option>
                    </select>
                </div>
                <div class="mb-3" id="topupAmountField">
                    <label class="form-label">Amount (UGX)</label>
                    <input type="number" name="amount" class="form-control" min="0" step="500">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn kora-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn kora-btn-confirm kora-btn-success">Confirm</button>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>

<!-- Void/Reversal Modal -->
<div class="modal fade kora-modal" id="voidModal" tabindex="-1">
    <div class="modal-dialog">
        <?= Html::beginForm(['site/void-transaction'], 'post') ?>
        <div class="modal-content rounded-3">
            <div class="modal-header kora-modal-header-danger">
                <h5 class="modal-title fw-bold">Void / Reverse Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Transaction</label>
                    <input type="text" id="voidTxLabel" class="form-control" readonly disabled placeholder="Select a transaction using the void icon on a row">
                    <input type="hidden" name="id" id="voidTxId" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Reason (required — audit log)</label>
                    <textarea name="void_reason" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn kora-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn kora-btn-confirm kora-btn-danger">Void &amp; Reverse</button>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>

<!-- Batch Invoice Modal -->
<div class="modal fade kora-modal" id="batchInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <?= Html::beginForm(['site/batch-invoice'], 'post') ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Batch Invoice &amp; Waiver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Class</label>
                    <select name="class_level" id="batchClassSelect" class="form-select" required>
                        <option value="">Select class...</option>
                    </select>
                    <div class="form-text" id="batchClassCount"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Base Fee (UGX)</label>
                    <input type="number" name="base_fee" class="form-control" min="0" step="1000" required>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="apply_sibling_waiver" value="1" id="sibWaiver">
                    <label class="form-check-label small" for="sibWaiver">Apply sibling waiver</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="apply_staff_waiver" value="1" id="staffWaiver">
                    <label class="form-check-label small" for="staffWaiver">Apply staff-child waiver</label>
                </div>
                <div class="alert alert-warning small mb-0 py-2 d-none" id="batchInvoiceWarning">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    This will add the base fee to <strong id="batchInvoiceWarningCount">0</strong> students' outstanding balances. This cannot be undone in bulk — you'd need to reverse each transaction individually.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn kora-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn kora-btn-confirm kora-btn-primary" id="batchInvoiceSubmit" disabled>Generate Invoices</button>
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

    .kora-modal .modal-header.kora-modal-header-danger {
        background: linear-gradient(135deg, #b02a37, #dc3545);
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

    .kora-modal .form-text {
        font-size: 0.75rem;
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

    .kora-modal .kora-btn-confirm:disabled {
        opacity: 0.5;
    }

    .kora-modal .kora-btn-success { background: #198754; }
    .kora-modal .kora-btn-success:hover { background: #157347; color: #fff; }

    .kora-modal .kora-btn-danger { background: #dc3545; }
    .kora-modal .kora-btn-danger:hover { background: #bb2d3b; color: #fff; }

    .kora-modal .kora-btn-primary { background: var(--kora-blue-accent); }
    .kora-modal .kora-btn-primary:hover { background: #2563eb; color: #fff; }

    .kora-modal .alert {
        border-radius: 8px;
        font-size: 0.82rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
   
    document.querySelectorAll('.kora-modal').forEach(function (modalEl) {
        document.body.appendChild(modalEl);
    });

    const walletSelect = document.getElementById('walletActionSelect');
    const topupField = document.getElementById('topupAmountField');
    if (walletSelect && topupField) {
        walletSelect.addEventListener('change', function () {
            topupField.style.display = this.value === 'TOPUP' ? 'block' : 'none';
        });
    }

    document.querySelectorAll('.kora-void-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('voidTxId').value = this.dataset.txId;
            document.getElementById('voidTxLabel').value = this.dataset.txLabel;
        });
    });

    const voidModalEl = document.getElementById('voidModal');
    if (voidModalEl) {
        voidModalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('voidTxId').value = '';
            document.getElementById('voidTxLabel').value = '';
        });
    }
});
</script>

<?php
$channelLabels = json_encode(array_column($channelBreakdown, 'payment_channel'));
$channelValues = json_encode(array_map('floatval', array_column($channelBreakdown, 'total')));
$velocityCurrent = json_encode($collectionVelocity['current'] ?? []);
$velocityPrevious = json_encode($collectionVelocity['previous'] ?? []);
$reconciliationChartLabels = json_encode($reconciliationLabels);
$reconciliationChartCleared = json_encode($reconciliationClearedValues);
$reconciliationChartSettled = json_encode($reconciliationSettledValues);

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4', ['position' => \yii\web\View::POS_END]);
$this->registerJs(<<<JS
    document.addEventListener('DOMContentLoaded', function () {
        const channelCanvas = document.getElementById('channelDonut');
        if (channelCanvas) {
            new Chart(channelCanvas, {
                type: 'doughnut',
                data: {
                    labels: {$channelLabels},
                    datasets: [{
                        data: {$channelValues},
                        backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997']
                    }]
                },
                options: { plugins: { legend: { position: 'bottom' } } }
            });
        }

        const reconciliationCanvas = document.getElementById('reconciliationChart');
        if (reconciliationCanvas) {
            new Chart(reconciliationCanvas, {
                type: 'bar',
                data: {
                    labels: {$reconciliationChartLabels},
                    datasets: [
                        { label: 'Cleared', data: {$reconciliationChartCleared}, backgroundColor: '#3b82f6', borderRadius: 4 },
                        { label: 'Settled', data: {$reconciliationChartSettled}, backgroundColor: '#198754', borderRadius: 4 }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => (v / 1000) + 'k' } } }
                }
            });
        }

        const cur = {$velocityCurrent};
        const prev = {$velocityPrevious};
        const velocityCanvas = document.getElementById('velocityChart');
        if (velocityCanvas) {
            new Chart(velocityCanvas, {
                type: 'line',
                data: {
                    labels: cur.map((r, i) => 'Day ' + (i + 1)),
                    datasets: [
                        { label: 'This Term', data: cur.map(r => r.cumulative), borderColor: '#0d6efd', tension: 0.3 },
                        { label: 'Last Term', data: prev.map(r => r.cumulative), borderColor: '#adb5bd', borderDash: [5,5], tension: 0.3 }
                    ]
                },
                options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
            });
        }
    });
JS
, \yii\web\View::POS_END);
?>
<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);

$classListUrl = Url::to(['site/class-list']);
$studentsByClassUrl = Url::to(['site/students-by-class']);

$this->registerJs(<<<JS
    document.addEventListener('DOMContentLoaded', function () {
        const classSelect = $('#walletClassSelect');
        const studentSelect = $('#walletStudentSelect');

        // Load classes once when modal opens
        $('#walletModal').on('show.bs.modal', function () {
            if (classSelect.find('option').length <= 1) {
                fetch('{$classListUrl}')
                    .then(r => r.json())
                    .then(classes => {
                        classes.forEach(c => {
                            classSelect.append(new Option(c, c));
                        });
                    });
            }
        });

        classSelect.on('change', function () {
            const classLevel = this.value;
            studentSelect.prop('disabled', !classLevel).html('<option value="">Loading...</option>');
            if (!classLevel) {
                studentSelect.html('<option value="">Select a class first...</option>');
                return;
            }

            studentSelect.select2({
                dropdownParent: $('#walletModal'),
                ajax: {
                    url: '{$studentsByClassUrl}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ class_level: classLevel, q: params.term }),
                    processResults: data => ({ results: data })
                },
                placeholder: 'Search student by name...',
                minimumInputLength: 0
            });
            studentSelect.prop('disabled', false).html('');
        });

        const walletSelect = document.getElementById('walletActionSelect');
        const topupField = document.getElementById('topupAmountField');
        if (walletSelect && topupField) {
            walletSelect.addEventListener('change', function () {
                topupField.style.display = this.value === 'TOPUP' ? 'block' : 'none';
            });
        }
    });
JS
, \yii\web\View::POS_END);
?>

<?php
$classListUrl = Url::to(['site/class-list']);
$classCountUrl = Url::to(['site/class-student-count']);

$this->registerJs(<<<JS
    document.addEventListener('DOMContentLoaded', function () {
        const batchClassSelect = document.getElementById('batchClassSelect');
        const batchClassCount = document.getElementById('batchClassCount');
        const batchWarning = document.getElementById('batchInvoiceWarning');
        const batchWarningCount = document.getElementById('batchInvoiceWarningCount');
        const batchSubmit = document.getElementById('batchInvoiceSubmit');

        document.getElementById('batchInvoiceModal')?.addEventListener('show.bs.modal', function () {
            if (batchClassSelect.options.length <= 1) {
                fetch('{$classListUrl}')
                    .then(r => r.json())
                    .then(classes => {
                        classes.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c;
                            opt.textContent = c;
                            batchClassSelect.appendChild(opt);
                        });
                    });
            }
        });

        batchClassSelect?.addEventListener('change', function () {
            const classLevel = this.value;
            batchSubmit.disabled = true;
            batchWarning.classList.add('d-none');
            batchClassCount.textContent = '';

            if (!classLevel) return;

            batchClassCount.textContent = 'Checking class size...';
            fetch('{$classCountUrl}?class_level=' + encodeURIComponent(classLevel))
                .then(r => r.json())
                .then(data => {
                    batchClassCount.textContent = data.count + ' student(s) in this class.';
                    batchWarningCount.textContent = data.count;
                    if (data.count > 0) {
                        batchWarning.classList.remove('d-none');
                        batchSubmit.disabled = false;
                    } else {
                        batchClassCount.textContent = 'No students found in this class — nothing to invoice.';
                    }
                });
        });
    });
JS
, \yii\web\View::POS_END);
?>