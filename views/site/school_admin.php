<?php
/** @var yii\web\View $this */
/** @var app\models\Schools $school */
/** @var array $stats */
/** @var array $pendingSheets */
/** @var app\models\Transactions[] $recentTransactions */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'School Master Admin Control Panel';
?>

<div class="site-school-admin bg-light py-3 min-vh-100">
    
    <div class="card card-body border-0 shadow-sm rounded-3 mb-4 p-4 border-start border-primary border-4 bg-white">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h2 class="h4 fw-bold mb-0 text-dark"><i class="bi bi-shield-shaded me-2 text-primary"></i><?= Html::encode(strtoupper($school->name)) ?>  System Command Hub</h2>
                <p class="text-muted small mb-0">Platform Provider Authorization Level. Full cross-departmental operations audit overview.</p>
            </div>
            <div>
                <a href="<?= Url::toRoute(['site/signup']) ?>" class="btn btn-sm btn-primary fw-bold rounded-2 shadow-sm px-3 py-2"><i class="bi bi-person-plus-fill me-1"></i> Provision Staff Account</a>
            </div>
        </div>
    </div>

    <!-- Combined Institutional KPI Metric Analytics Row Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted text-xs uppercase fw-bold mb-1">Total Fees Collected</div>
                <div class="h4 fw-bold text-success mb-0">UGX <?= number_format($stats['total_collected'], 0) ?></div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted text-xs uppercase fw-bold mb-1">Outstanding Receivables</div>
                <div class="h4 fw-bold text-danger mb-0">UGX <?= number_format($stats['total_outstanding'], 0) ?></div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted text-xs uppercase fw-bold mb-1">Enrolled Students</div>
                <div class="h4 fw-bold text-dark mb-0"><?= $stats['active_students'] ?> <small class="text-muted text-xs">Active</small></div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted text-xs uppercase fw-bold mb-1">Active System Staff</div>
                <div class="h4 fw-bold text-dark mb-0"><?= $stats['total_staff'] ?> <small class="text-muted text-xs">Profiles</small></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="bi bi-clipboard-check me-1 text-warning"></i> Pending Academic Review Queue</h5>
                    <a href="<?= Url::toRoute(['site/dos-review']) ?>" class="btn btn-xs btn-outline-light py-0.5 px-2 text-xs rounded">Open Audit Desk</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover small">
                        <thead class="table-light text-secondary text-xs">
                            <tr>
                                <th class="ps-3">Class Level</th>
                                <th>Subject Stream</th>
                                <th>Assessment</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingSheets)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">All grading sheets are audited and sealed. Clean slate.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pendingSheets as $sheet): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold"><?= Html::encode($sheet['class_level']) ?></td>
                                        <td class="fw-semibold text-secondary"><?= Html::encode($sheet['subject_name']) ?></td>
                                        <td><span class="badge bg-warning-subtle text-warning border px-2 py-1 rounded"><?= Html::encode($sheet['term']) ?></span></td>
                                        <td class="text-end pe-3"><a href="<?= Url::toRoute(['site/dos-review', 'class_level' => $sheet['class_level'], 'term' => $sheet['term']]) ?>" class="btn btn-outline-dark btn-xs py-0.5 px-2 rounded">Audit</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="bi bi-currency-exchange me-1 text-info"></i> Inbound Bank Settlement Audits</h5>
                    <a href="<?= Url::toRoute(['site/bursar']) ?>" class="btn btn-xs btn-outline-light py-0.5 px-2 text-xs rounded">Open Ledger</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover small">
                        <thead class="table-light text-secondary text-xs">
                            <tr>
                                <th class="ps-3">Student File</th>
                                <th>Channel</th>
                                <th>Reference Token</th>
                                <th class="text-end pe-3">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentTransactions)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">No inbound settlement records traced inside clearing metrics.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentTransactions as $tx): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark"><?= $tx->student ? Html::encode($tx->student->name) : 'N/A' ?></td>
                                        <td class="text-secondary"><?= Html::encode($tx->payment_channel) ?></td>
                                        <td class="text-xs"><span class="bg-light border px-1.5 py-0.5 rounded"><?= Html::encode($tx->external_reference) ?></span></td>
                                        <td class="text-end pe-3 fw-bold text-success">+UGX <?= number_format((float)$tx->amount, 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-warning-subtle { background-color: #fff3cd !important; color: #664d03 !important; }
.btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.uppercase { text-transform: uppercase; }
</style>
