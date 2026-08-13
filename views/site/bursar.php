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
                    'onsubmit' => "return confirm(' NEW TERM ROLLOVER WARNING \\n\\nAre you completely sure you want to start a new academic term?\\n\\nThis will automatically bill every active student the new term fee of UGX " . number_format($stats['base_tuition_fees'] ?? 850000, 0) . " and carry forward previous debts.\\n\\nAll student pocket money balances WILL safely continue untouched! This cannot be undone.');"
                ]) ?>
                    <button type="submit" class="btn btn-outline-dark fw-bold rounded-3 shadow-sm px-3 d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-repeat"></i> Start New Term
                    </button>
                <?= Html::endForm() ?>

                <a href="<?= Url::toRoute(['site/register-student']) ?>" class="btn btn-success fw-bold rounded-3 shadow-sm px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill"></i> Enroll Student
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4">
                    <div class="text-muted small fw-semibold text-uppercase tracking-wider mb-1">Total Tuition Collected</div>
                    <div class="h3 fw-bold text-dark mb-0">UGX <?= number_format($stats['total_tuition'], 0) ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4">
                    <div class="text-muted small fw-semibold text-uppercase tracking-wider mb-1">Total Outstanding Fees</div>
                    <div class="h3 fw-bold text-dark mb-0">UGX <?= number_format($stats['total_outstanding'], 0) ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white border-start border-4">
                    <div class="text-muted small fw-semibold text-uppercase tracking-wider mb-1">Active S-Wallet Vaults</div>
                    <div class="h3 fw-bold text-dark mb-0">UGX <?= number_format($stats['total_swallet'], 0) ?></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-4">
            <div class="card-header bg-primary text-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="card-title h6 fw-bold mb-0">
                    <i class="bi bi-receipt-cutoff me-1"></i> Recent Clearing Network Receipts
                </h5>

                <a href="<?= Url::toRoute(['site/export-receipts', 'tx_q' => $txSearchKeyword]) ?>" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1 fw-bold"><i class="bi bi-file-earmark-spreadsheet-fill"></i> Save Spreadsheet</a>
                
                <form method="get" action="<?= Url::toRoute(['site/bursar']) ?>" class="d-flex m-0 gap-1" style="max-width: 350px; width: 100%;">
                    <div class="input-group input-group-sm">
                        <input type="hidden" name="q" value="<?= Html::encode($searchKeyword ?? '') ?>">
                        
                        <input type="text" name="tx_q" value="<?= Html::encode($txSearchKeyword ?? '') ?>" class="form-control bg-white text-dark border-0 rounded-start-2" placeholder="Search reference, type, or channel..." autocomplete="off">
                        
                        <?php if (!empty($txSearchKeyword)): ?>
                            <a href="<?= Url::toRoute(['site/bursar', 'q' => ($searchKeyword ?? '')]) ?>" class="btn btn-light border-0 text-muted d-flex align-items-center px-2">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-dark fw-bold border-0 px-3" style="background-color: grey !important;">Filter</button>
                    </div>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 small">
                    <thead class="table-light text-secondary uppercase border-bottom">
                        <tr>
                            <th>No</th>
                            <th class="ps-4">Timestamp Date</th>
                            <th>Student Name</th>
                            <th>Allocation Profile</th>
                            <th>Gateway Channel</th>
                            <th>Clearing Token Reference</th>
                            <th class="text pe-4">Settlement Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No historical transactions logged match the filter criteria.</td>
                            </tr>
                        <?php else: ?>
                           <?php foreach ($recentTransactions as $i => $tx): ?>
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">
                                        <?= $i + 1 ?>
                                    </td>

                                    <td class="ps-4 text-muted">
                                        <?= date('Y-m-d H:i', strtotime($tx->created_at)) ?>
                                    </td>

                                    <td class="fw-bold text-dark">
                                        <?= $tx->student ? Html::encode($tx->student->name) : 'Unknown Record' ?>
                                    </td>

                                    <td>
                                        <span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold <?= 
                                            $tx->transaction_type === 'TUITION'
                                                ? 'text-primary'
                                                : ($tx->transaction_type === 'POCKET_MONEY'
                                                    ? ' text-success'
                                                    : 'text-danger')
                                        ?>">
                                            <?= Html::encode($tx->transaction_type) ?>
                                        </span>
                                    </td>

                                    <td class="text-secondary fw-semibold">
                                        <?= Html::encode($tx->payment_channel) ?>
                                    </td>

                                    <td>
                                        <span class=bg-light px-2 py-0.5 rounded border small d-inline-block">
                                            <?= Html::encode($tx->external_reference) ?>
                                        </span>
                                    </td>

                                    <td class="pe-4 fw-bold text-dark">
                                        UGX <?= number_format((float)$tx->amount, 0) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-center pt-3 border-top">
                <?= LinkPager::widget([
                    'pagination' => $txPages,
                    'maxButtonCount' => 5,
                    'options' => ['class' => 'pagination pagination-sm mb-0']
                ]) ?>
            </div>
        </div>
    </div>
</div>
