<?php
/** @var yii\web\View $this */
/** @var app\models\Students $student */
/** @var app\models\Transactions[] $statementLogs */

use yii\helpers\Url;

$this->title = 'KORA Portal - ' . $student->name;
?>

<div class="site-student-dashboard bg-light py-5 min-vh-100">
    <div class="container max-w-5xl">
        
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
