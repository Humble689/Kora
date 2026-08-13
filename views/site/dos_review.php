<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $rawRecords */
/** @var string $selectedClass */
/** @var string $selectedTerm */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Kora ERP - Academic Review Center';

$userRole = Yii::$app->user->identity->role;
$canUnseal = in_array($userRole, ['SCHOOL_ADMIN', 'SUPER_ADMIN']);

$columns = ['bot' => 'BOT (20%)', 'mot' => 'MOT (30%)', 'eot' => 'EOT (50%)'];
?>

<div class="site-dos bg-light py-3 min-vh-100">

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
            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-secondary">Target Term Assessment</label>
                <select name="term" class="form-select form-select-sm fw-bold" onchange="this.form.submit()">
                    <option value="TERM_1" <?= $selectedTerm === 'TERM_1' ? 'selected' : '' ?>>Term 1</option>
                    <option value="TERM_2" <?= $selectedTerm === 'TERM_2' ? 'selected' : '' ?>>Term 2</option>
                    <option value="TERM_3" <?= $selectedTerm === 'TERM_3' ? 'selected' : '' ?>>Term 3</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-sm btn-dark w-100 fw-bold py-2"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>

    <div class="mb-3">
        <input type="text" id="dosStudentSearchBox" class="form-control form-control-sm"
               placeholder="Search by student name or subject...">
    </div>

    <?= Html::beginForm(['site/bulk-moderate-marks'], 'post', ['id' => 'dosBatchActionForm', 'class' => 'm-0']) ?>

        <input type="hidden" name="class_level" value="<?= Html::encode($selectedClass) ?>">
        <input type="hidden" name="term" value="<?= Html::encode($selectedTerm) ?>">
        <input type="hidden" name="moderation_action" id="bulkActionTrackerType" value="APPROVE_SELECTED">

        <div class="card card-body border-0 shadow-sm rounded-3 p-3 mb-3 bg-dark text-white">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-secondary text-white border-0 text-xs">D.O.S / Admin Comment</span>
                        <input type="text" name="dos_comment" id="dosFeedbackTextInputField" class="form-control bg-white text-dark border-0" placeholder="Required for rejections and unseal corrections...">
                    </div>
                </div>
                <div class="col-12 col-lg-7 text-end d-flex flex-wrap gap-1 justify-content-end">
                    <button type="button" id="btnApproveSelected" class="btn btn-sm btn-success fw-bold text-xs px-2.5"><i class="bi bi-check-all"></i> Approve Selected</button>
                    <button type="button" id="btnRejectSelected" class="btn btn-sm btn-danger fw-bold text-xs px-2.5"><i class="bi bi-x-circle"></i> Reject Selected</button>
                    <button type="button" id="btnApproveAll" class="btn btn-sm btn-outline-success fw-bold text-xs px-2.5"><i class="bi bi-shield-lock-fill"></i> Approve & Seal All Pending</button>
                    <button type="button" id="btnRejectAll" class="btn btn-sm btn-outline-danger text-danger border-danger bg-transparent fw-bold text-xs px-2.5"><i class="bi bi-arrow-counterclockwise"></i> Reject All Pending</button>
                    <?php if ($canUnseal): ?>
                        <button type="button" id="btnUnsealSelected" class="btn btn-sm btn-warning fw-bold text-xs px-2.5"><i class="bi bi-unlock-fill"></i> Unseal Selected</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-5">
            <div class="table-responsive">
                <table id="dosGridTable" class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark font-monospace text-xs">
                        <tr>
                            <th class="text-center" style="width: 4%;">No</th>
                            <th class="ps-3" style="width: 15%;">Student Full Name</th>
                            <th style="width: 14%;">Subject</th>
                            <?php foreach ($columns as $label): ?>
                                <th class="text-center" style="width: 15%;"><?= $label ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNum = 1; foreach ($rawRecords as $rec): ?>
                            <tr>
                                <td class="text-center text-muted"><?= $rowNum++ ?></td>
                                <td class="ps-3 fw-bold text-dark"><?= Html::encode($rec['student_name']) ?></td>
                                <td class="font-monospace text-secondary fw-semibold"><?= Html::encode($rec['subject_name']) ?></td>

                                <?php foreach (array_keys($columns) as $colKey):
                                    $status = $rec[$colKey . '_status'] ?? 'NOT_SUBMITTED';
                                    $mark = (float)($rec[$colKey . '_mark'] ?? 0);
                                    $token = $rec['id'] . '_' . $colKey;
                                ?>
                                    <td class="text-center">
                                        <?php if ($status !== 'NOT_SUBMITTED'): ?>
                                            <div class="font-monospace fw-bold mb-1"><?= $mark ?></div>
                                        <?php endif; ?>

                                        <?php if ($status === 'PENDING_REVIEW'): ?>
                                            <label class="d-inline-flex align-items-center gap-1 small">
                                                <input type="checkbox" name="selected_marks[]" value="<?= $token ?>" class="form-check-input border-dark row-audit-grid-checkbox">
                                                <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-10 fw-bold text-xs rounded-2 px-2 py-1"><i class="bi bi-hourglass-split"></i> Pending</span>
                                            </label>

                                        <?php elseif ($status === 'APPROVED_SEALED'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-10 fw-bold text-xs rounded-2 px-2 py-1 d-block mb-1"><i class="bi bi-patch-check-fill"></i> Sealed</span>
                                            <?php if ($canUnseal): ?>
                                                <label class="d-inline-flex align-items-center gap-1 small">
                                                    <input type="checkbox" name="selected_unseal[]" value="<?= $token ?>" class="form-check-input border-dark row-unseal-grid-checkbox">
                                                    <span class="text-warning text-xs">unseal</span>
                                                </label>
                                            <?php endif; ?>

                                        <?php elseif ($status === 'REJECTED_AMEND'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-10 fw-bold text-xs rounded-2 px-2 py-1"><i class="bi bi-arrow-left-right"></i> Returned</span>

                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-10 fw-bold text-xs rounded-2 px-2 py-1"><i class="bi bi-dash-circle"></i> Not Submitted</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?= Html::endForm() ?>
</div>

<style>
.bg-success-subtle { background-color: #d1e7dd !important; color: #0f5132 !important; }
.bg-warning-subtle { background-color: #fff3cd !important; color: #664d03 !important; }
.bg-danger-subtle { background-color: #f8d7da !important; color: #842029 !important; }
.bg-secondary-subtle { background-color: #e2e3e5 !important; color: #41464b !important; }

#dosGridTable .form-check-input {
    width: 1.1em;
}
#dosGridTable .form-check-input:checked {
    background-color: #198754 !important;
    border-color: #198754 !important;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const feedbackInput = document.getElementById('dosFeedbackTextInputField');
    const form = document.getElementById('dosBatchActionForm');
    const actionTracker = document.getElementById('bulkActionTrackerType');
    const searchBox = document.getElementById('dosStudentSearchBox');
    const table = document.getElementById('dosGridTable');

    if (searchBox && table) {
        searchBox.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    }

    function executeModerationAction(actionCode, needsComment, checkboxSelector, actionLabel) {
        const commentValue = feedbackInput.value.trim();

        if (needsComment && commentValue === '') {
            alert("A comment is required before you can " + actionLabel + ".");
            return;
        }

        if (checkboxSelector) {
            const totalChecked = document.querySelectorAll(checkboxSelector + ':checked').length;
            if (totalChecked === 0) {
                const otherSelector = checkboxSelector === '.row-audit-grid-checkbox'
                    ? '.row-unseal-grid-checkbox'
                    : '.row-audit-grid-checkbox';
                const otherChecked = document.querySelectorAll(otherSelector + ':checked').length;

                if (otherChecked > 0) {
                    alert("You have boxes checked in the wrong column for this action. Check the matching checkboxes for \"" + actionLabel + "\" instead.");
                } else {
                    alert("No columns selected! Please check the boxes for the marks you want to " + actionLabel + ".");
                }
                return;
            }
        }

        if (confirm("Confirm: " + actionLabel + "?")) {
            actionTracker.value = actionCode;
            form.submit();
        }
    }

    document.getElementById('btnApproveSelected').addEventListener('click',
        () => executeModerationAction('APPROVE_SELECTED', false, '.row-audit-grid-checkbox', 'approve the selected columns'));
    document.getElementById('btnRejectSelected').addEventListener('click',
        () => executeModerationAction('REJECT_SELECTED', true, '.row-audit-grid-checkbox', 'reject the selected columns'));
    document.getElementById('btnApproveAll').addEventListener('click',
        () => executeModerationAction('APPROVE_ALL', false, null, 'approve and seal all pending columns'));
    document.getElementById('btnRejectAll').addEventListener('click',
        () => executeModerationAction('REJECT_ALL', true, null, 'reject all pending columns'));

    const btnUnseal = document.getElementById('btnUnsealSelected');
    if (btnUnseal) {
        btnUnseal.addEventListener('click',
            () => executeModerationAction('ADMIN_UNSEAL_SELECTED', true, '.row-unseal-grid-checkbox', 'unseal the selected columns'));
    }
});
</script>