<?php
/** @var yii\web\View $this */
/** @var app\models\User[] $teachers */
/** @var array $activeAssignments */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Teacher Allocations Registry';
?>

<div class="site-manage-assignments bg-light py-3 min-vh-100">
    <div class="card card-body border-0 shadow-sm rounded-3 mb-4 p-4">
        <h2 class="h4 fw-bold mb-1 text-dark"><i class="bi bi-person-lines-fill text-warning me-2"></i>Multi-Class Teacher Allocations</h2>
        <p class="text-muted small mb-0">Onboard multiple classes and subjects for any teacher registered under your institution.</p>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white">
                <h5 class="fw-bold mb-3 text-dark small uppercase tracking-wider"><i class="bi bi-plus-circle-fill text-primary"></i> Allocate New Subject</h5>
                
                <?= Html::beginForm(['site/manage-assignments'], 'post') ?>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Select Faculty Teacher</label>
                        <select name="teacher_id" class="form-select form-select-sm fw-bold" required>
                            <option value="">-- Choose Staff Member --</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t->id ?>"><?= Html::encode($t->username) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Target Class Level</label>
                        <select name="class_level" class="form-select form-select-sm fw-bold" required>
                            <option value="">-- Choose Class Tier --</option>
                            <?php foreach (['Primary 1', 'Primary 2', 'Primary 3', 'Primary 4', 'Primary 5', 'Primary 6', 'Primary 7', 'Senior 1', 'Senior 2', 'Senior 3', 'Senior 4', 'Senior 5', 'Senior 6'] as $cls): ?>
                                <option value="<?= $cls ?>"><?= $cls ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Target Subject curriculum</label>
                        <select name="subject_name" class="form-select form-select-sm fw-bold" required>
                            <option value="">-- Choose Subject Course --</option>
                            <?php foreach (['Mathematics', 'English', 'Biology', 'Chemistry', 'Physics', 'History', 'Geography', 'Entrepreneurship', 'French', 'Art', 'Social Studies', 'Science'] as $sub): ?>
                                <option value="<?= $sub ?>"><?= $sub ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold rounded-3 py-2 shadow-sm mt-2">
                        Map Assignment Route
                    </button>

                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="bi bi-grid-3x3-gap-fill me-1"></i> Active Institutional Allocation Ledger Matrix</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-secondary border-bottom">
                            <tr>
                                <th class="ps-4">Teacher Username</th>
                                <th>Class Stream</th>
                                <th>Assigned Subject</th>
                                <th class="text-end pe-4">Action Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activeAssignments)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No teacher combinations mapped inside database columns yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activeAssignments as $row): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark"><i class="bi bi-person-badge-fill text-muted me-1"></i> <?= Html::encode($row['teacher_name']) ?></td>
                                        <td><span class="badge bg-secondary px-2.5 py-1"><?= Html::encode($row['class_level']) ?></span></td>
                                        <td class="font-monospace text-secondary fw-semibold"><?= Html::encode($row['subject_name']) ?></td>
                                        <td class="text-end pe-4">
                                            <?= Html::beginForm(['site/delete-assignment', 'id' => $row['id']], 'post', [
                                                'class' => 'm-0 d-inline-block',
                                                'onsubmit' => "return confirm('Are you sure you want to revoke this subject assignment?');"
                                            ]) ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-2 px-2" title="Revoke Allocation">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            <?= Html::endForm() ?>
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
</div>
