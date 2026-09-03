<?php
/** @var yii\web\View $this */
/** @var app\models\Students[] $students */
/** @var string $selectedClass */
/** @var string $selectedTerm */
/** @var int $selectedYear */
/** @var array $availableYears */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Report Cards Hub';
?>

<div class="site-print-reports bg-light py-3 min-vh-100">
    <div class="card card-body border-0 shadow-sm rounded-3 mb-4 p-4">
        <h2 class="h4 fw-bold mb-1 text-dark"><i class="bi bi-printer-fill text-primary me-2"></i>Official Report Compilation Desk</h2>
        <p class="text-muted small mb-0">Select a classroom stream below to review, audit, or batch-print official academic report cards.</p>
    </div>

    <div class="card card-body border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
        <form method="get" action="<?= Url::toRoute(['site/print-reports']) ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold text-secondary">Classroom Stream Selection</label>
                <select name="class_level" class="form-select form-select-sm fw-bold text-dark" onchange="this.form.submit()">
                    <?php foreach (['Primary 1', 'Primary 2', 'Primary 3', 'Primary 4', 'Primary 5', 'Primary 6', 'Primary 7', 'Senior 1', 'Senior 2', 'Senior 3', 'Senior 4', 'Senior 5', 'Senior 6'] as $cls): ?>
                        <option value="<?= $cls ?>" <?= $selectedClass === $cls ? 'selected' : '' ?>><?= $cls ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary">Target Term Assessment Cycle</label>
                <select name="term" class="form-select form-select-sm fw-bold text-dark" onchange="this.form.submit()">
                    <option value="TERM_1" <?= $selectedTerm === 'TERM_1' ? 'selected' : '' ?>>Term 1</option>
                    <option value="TERM_2" <?= $selectedTerm === 'TERM_2' ? 'selected' : '' ?>>Term 2</option>
                    <option value="TERM_3" <?= $selectedTerm === 'TERM_3' ? 'selected' : '' ?>>Term 3</option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary">Academic Year</label>
                <select name="year" class="form-select form-select-sm fw-bold text-dark" onchange="this.form.submit()">
                    <?php foreach ($availableYears as $yr): ?>
                        <option value="<?= $yr ?>" <?= $selectedYear === (int) $yr ? 'selected' : '' ?>><?= $yr ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-sm btn-dark w-100 fw-bold py-2"><i class="bi bi-search"></i> Load Directory</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">

        <div class="card-header bg-primary text-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h5 class="card-title h6 fw-bold mb-0"><i class="bi bi-people-fill me-1"></i> Class Roll & Report Card Issuance Links - <?= $selectedYear ?></h5>

            <div class="d-flex align-items-center gap-2">
                <a href="<?= Url::toRoute(['site/batch-print-reports', 'class_level' => $selectedClass, 'term' => $selectedTerm, 'year' => $selectedYear]) ?>" class="btn btn-sm btn-light text-primary fw-bold text-xs d-flex align-items-center gap-1 shadow-sm" target="_blank">
                    <i class="bi bi-printer-fill"></i> Batch Print Cleared Cards (<?= Html::encode($selectedTerm) ?>)
                </a>

                <a href="<?= Url::toRoute(['site/export-class-marks', 'class_level' => $selectedClass, 'term' => $selectedTerm, 'year' => $selectedYear]) ?>" class="btn btn-sm btn-success text-white fw-bold text-xs d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Save Excel Ledger
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary border-bottom">
                    <tr>
                        <th class="ps-4">Student Name</th>
                        <th>10-Digit Code</th>
                        <th>Tuition Due Balance</th>
                        <th>Lock Watermark Status</th>
                        <th class="text-end pe-4">Compile Terminal Reports</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No students currently enrolled in this class level stream.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $st):
                            $hasDebt = (float)$st->tuition_balance > 0;
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= Html::encode(ucfirst($st->name)) ?></td>
                                <td class="text-secondary fw-semibold font-monospace"><?= Html::encode($st->payment_code) ?></td>
                                <td class="fw-bold <?= $hasDebt ? 'text-danger' : 'text-success' ?> font-monospace">UGX <?= number_format((float)$st->tuition_balance, 0) ?></td>
                                <td>
                                    <?php if ($hasDebt): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-20"><i class="bi bi-lock-fill me-1"></i> Financial Lock Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-20"><i class="bi bi-unlock-fill me-1"></i> Cleared for Print</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= Url::toRoute(['site/view-report-card', 'id' => $st->id, 'term' => 'TERM_1', 'year' => $selectedYear]) ?>" class="btn btn-outline-dark btn-sm rounded-2 text-xs fw-bold px-2.5" target="_blank">Term 1</a>
                                        <a href="<?= Url::toRoute(['site/view-report-card', 'id' => $st->id, 'term' => 'TERM_2', 'year' => $selectedYear]) ?>" class="btn btn-outline-dark btn-sm rounded-2 text-xs fw-bold px-2.5" target="_blank">Term 2</a>
                                        <a href="<?= Url::toRoute(['site/view-report-card', 'id' => $st->id, 'term' => 'TERM_3', 'year' => $selectedYear]) ?>" class="btn btn-outline-dark btn-sm rounded-2 text-xs fw-bold px-2.5" target="_blank">Term 3</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>