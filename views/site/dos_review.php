<?php
/** @var yii\web\View $this */
/** @var array $marksSummary */
/** @var array $rawRecords */
/** @var string $selectedClass */
/** @var string $selectedTerm */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Academic Review Center';
?>

<div class="site-dos bg-light py-3 min-vh-100">
    <div class="card card-body border-0 shadow-sm rounded-3 mb-4 p-4">
        <h2 class="h4 fw-bold mb-1 text-dark"><i class="bi bi-shield-check text-success me-2"></i>Academic Audit & Moderation Desk</h2>
        <p class="text-muted small mb-0">Review terminal assessment performance sheets submitted by classroom teachers before printing card reports.</p>
    </div>

    <div class="card card-body border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
        <form method="get" action="<?= Url::toRoute(['site/dos-review']) ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-secondary">Classroom Stream</label>
                <select name="class_level" class="form-select form-select-sm fw-bold" onchange="this.form.submit()">
                    <?php foreach (['Primary 1', 'Primary 2', 'Primary 3', 'Primary 4', 'Primary 5', 'Primary 6', 'Primary 7', 'Senior 1', 'Senior 2', 'Senior 3', 'Senior 4', 'Senior 5', 'Senior 6'] as $cls): ?>
                        <option value="<?= $cls ?>" <?= $selectedClass === $cls ? 'selected' : '' ?>><?= $cls ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold text-secondary">Target Term Assessment</label>
                <select name="term" class="form-select form-select-sm fw-bold" onchange="this.form.submit()">
                    <option value="TERM_1" <?= $selectedTerm === 'TERM_1' ? 'selected' : '' ?>>Term 1</option>
                    <option value="TERM_2" <?= $selectedTerm === 'TERM_2' ? 'selected' : '' ?>>Term 2</option>
                    <option value="TERM_3" <?= $selectedTerm === 'TERM_3' ? 'selected' : '' ?>>Term 3</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-sm btn-dark w-100 fw-bold"><i class="bi bi-funnel"></i> Load Records</button>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
                <?php if (empty($marksSummary)): ?>
            <div class="col-12"><div class="alert alert-secondary border-0 text-center py-4">No marks submitted for <?= Html::encode($selectedClass) ?> / <?= Html::encode($selectedTerm) ?> yet.</div></div>
        <?php else: ?>
            <?php foreach ($marksSummary as $sm): 
                $uniqueCardId = str_replace(' ', '', $sm['subject_name']);
            ?>
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm p-3 rounded-3 bg-white mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold text-dark text-md mb-0"><?= Html::encode($sm['subject_name']) ?></div>
                                <span class="badge rounded-pill small <?= $sm['status'] === 'APPROVED_SEALED' ? 'bg-success-subtle text-success' : ($sm['status'] === 'REJECTED_AMEND' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') ?>">
                                    <?= $sm['status'] ?>
                                </span>
                            </div>
                            
                            <div class="d-flex gap-1">
                                <?php if ($sm['status'] === 'PENDING_REVIEW'): ?>
                                    <?= Html::beginForm(['site/seal-marks'], 'post', ['class' => 'm-0']) ?>
                                        <input type="hidden" name="class_level" value="<?= Html::encode($selectedClass) ?>">
                                        <input type="hidden" name="subject_name" value="<?= Html::encode($sm['subject_name']) ?>">
                                        <input type="hidden" name="term" value="<?= Html::encode($selectedTerm) ?>">
                                        <button type="submit" class="btn btn-sm btn-success rounded-2 fw-bold" onclick="return confirm('Seal this marks sheet? Teachers will lose editing privileges.')"><i class="bi bi-lock-fill"></i> Seal</button>
                                    <?= Html::endForm() ?>

                                    <button class="btn btn-sm btn-outline-danger rounded-2 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#rejectBox<?= $uniqueCardId ?>">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                <?php elseif ($sm['status'] === 'APPROVED_SEALED'): ?>
                                    <span class="text-success fs-4"><i class="bi bi-patch-check-fill"></i></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="collapse mt-2" id="rejectBox<?= $uniqueCardId ?>">
                            <?= Html::beginForm(['site/reject-marks'], 'post', ['class' => 'bg-light p-2 rounded rounded-2 border']) ?>
                                <input type="hidden" name="class_level" value="<?= Html::encode($selectedClass) ?>">
                                <input type="hidden" name="subject_name" value="<?= Html::encode($sm['subject_name']) ?>">
                                <input type="hidden" name="term" value="<?= Html::encode($selectedTerm) ?>">
                                
                                <div class="mb-2">
                                    <label class="form-label text-xs fw-bold text-secondary">Correction Note for Teacher</label>
                                    <textarea name="dos_comment" rows="2" class="form-control form-control-sm text-dark bg-white" placeholder="e.g., Please review, looks like a typo..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold py-1 text-xs">Send Back to Teacher</button>
                            <?= Html::endForm() ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <?php if (!empty($rawRecords)): ?>
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="card-title h6 fw-bold mb-0"><i class="bi bi-list-columns-reverse me-1"></i> Individual Student Grade Audits Log</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary border-bottom">
                        <tr>
                            <th class="ps-4">Student Name</th>
                            <th>Subject Allocation</th>
                            <th class="text-center">BOT (20%)</th>
                            <th class="text-center">MOT (30%)</th>
                            <th class="text-center">EOT (50%)</th>
                            <th class="pe-4">Teacher Comments & Internal Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rawRecords as $rec): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= Html::encode($rec['student_name']) ?></td>
                                <td class="font-monospace text-secondary fw-semibold"><?= Html::encode($rec['subject_name']) ?></td>
                                <td class="text-center"><?= (float)$rec['bot_mark'] ?></td>
                                <td class="text-center"><?= (float)$rec['mot_mark'] ?></td>
                                <td class="text-center"><?= (float)$rec['eot_mark'] ?></td>
                                <td class="pe-4 text-muted italic small"><?= Html::encode($rec['teacher_comment'] ?: 'No comment entered.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.bg-success-subtle { background-color: #d1e7dd !important; color: #0f5132 !important; }
.bg-warning-subtle { background-color: #fff3cd !important; color: #664d03 !important; }
</style>
