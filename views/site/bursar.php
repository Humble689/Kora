<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $stats */
/** @var app\models\Transactions[] $recentTransactions */
/** @var yii\data\Pagination $txPages */
/** @var string $txSearchKeyword */
/** @var string $searchKeyword */

use yii\bootstrap5\LinkPager;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'KORA ERP Bursar Operations';

$txBadgeMap = [
    'TUITION'       => 'text-primary',
    'POCKET_MONEY'  => 'text-success',
];
$txBadgeDefault = 'text-danger';

$totalCount = $txPages->totalCount ?? count($recentTransactions);
$pageStart  = $totalCount > 0 ? ($txPages->getOffset() + 1) : 0;
$pageEnd    = min($txPages->getOffset() + $txPages->getLimit(), $totalCount);
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

        <!-- Stat cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4 border-primary h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing:.04em;">Total Tuition Collected</div>
                            <div class="h3 fw-bold text-dark mb-0">UGX <?= number_format($stats['total_tuition'], 0) ?></div>
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
                                        <?= $tx->student ? Html::encode($tx->student->name) : '<span class="text-muted fst-italic">Unknown Record</span>' ?>
                                    </td>

                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 fw-semibold <?= $badgeClass ?>">
                                            <?= Html::encode($tx->transaction_type) ?>
                                        </span>
                                    </td>

                                    <td class="text-secondary fw-semibold"><?= Html::encode($tx->payment_channel) ?></td>

                                    <td>
                                        <span class="bg-light px-2 py-1 rounded border small d-inline-block font-monospace">
                                            <?= Html::encode($tx->external_reference) ?>
                                        </span>
                                    </td>

                                    <td class="pe-4 fw-bold text-dark text-end">
                                        UGX <?= number_format((float)$tx->amount, 0) ?>
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