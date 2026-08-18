<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $rawRecords */
/** @var string $selectedClass */
/** @var string $selectedTerm */
/** @var string $selectedSubject */
/** @var bool $onlyPending */
/** @var array $subjectList */
/** @var yii\data\Pagination $pagination */
/** @var int $pendingTotal */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Kora ERP - Academic Review Center';

$userRole = Yii::$app->user->identity->role;
$canUnseal = in_array($userRole, ['SCHOOL_ADMIN', 'SUPER_ADMIN']);

$columns = ['bot' => 'BOT (20%)', 'mot' => 'MOT (30%)', 'eot' => 'EOT (50%)'];

$termLabels = ['TERM_1' => 'Term 1', 'TERM_2' => 'Term 2', 'TERM_3' => 'Term 3'];
$pendingCount = $pendingTotal;
?>

<div class="site-dos py-4">

    <div class="dos-page-header mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="dos-page-title mb-1"><i class="bi bi-clipboard-check"></i> Academic Review Center</h1>
                <p class="dos-page-subtitle mb-0">Moderate and seal submitted marks before report cards are printed.</p>
            </div>
            <!-- <div class="dos-summary-chip">
                <span class="dos-summary-count"><?= $pendingCount ?></span>
                <span class="dos-summary-label">Pending Review<?= $pendingCount === 1 ? '' : 's' ?></span>
            </div> -->
        </div>
    </div>

    <div class="card dos-card border-0 rounded-3 p-3 mb-3">
        <form method="get" action="<?= Url::toRoute(['site/dos-review']) ?>" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold dos-label">Classroom Stream</label>
                <select name="class_level" class="form-select form-select-sm dos-input fw-semibold" onchange="this.form.submit()">
                    <?php foreach (['Primary 1', 'Primary 2', 'Primary 3', 'Primary 4', 'Primary 5', 'Primary 6', 'Primary 7', 'Senior 1', 'Senior 2', 'Senior 3', 'Senior 4', 'Senior 5', 'Senior 6'] as $cls): ?>
                        <option value="<?= $cls ?>" <?= $selectedClass === $cls ? 'selected' : '' ?>><?= $cls ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold dos-label">Target Term Assessment</label>
                <select name="term" class="form-select form-select-sm dos-input fw-semibold" onchange="this.form.submit()">
                    <?php foreach ($termLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $selectedTerm === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold dos-label">Subject</label>
                <select name="subject" class="form-select form-select-sm dos-input fw-semibold" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjectList as $subjectName): ?>
                        <option value="<?= Html::encode($subjectName) ?>" <?= $selectedSubject === $subjectName ? 'selected' : '' ?>><?= Html::encode($subjectName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($onlyPending): ?>
                <input type="hidden" name="status" value="pending">
            <?php endif; ?>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-sm dos-btn-filter w-100 fw-bold py-2"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>

    <?php
        $pendingToggleParams = array_filter([
            'class_level' => $selectedClass,
            'term' => $selectedTerm,
            'subject' => $selectedSubject !== '' ? $selectedSubject : null,
            'status' => $onlyPending ? null : 'pending',
        ]);
    ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="dos-class-badge">
                <i class="bi bi-mortarboard"></i>
                <?= Html::encode($selectedClass) ?>
                <span class="dos-class-badge-divider">&middot;</span>
                <?= Html::encode($termLabels[$selectedTerm] ?? $selectedTerm) ?>
                <?php if ($selectedSubject !== ''): ?>
                    <span class="dos-class-badge-divider">&middot;</span>
                    <?= Html::encode($selectedSubject) ?>
                <?php endif; ?>
            </div>
            <a href="<?= Url::toRoute(array_merge(['site/dos-review'], $pendingToggleParams)) ?>"
               class="dos-pending-toggle <?= $onlyPending ? 'active' : '' ?>">
                <i class="bi bi-hourglass-split"></i>
                <?= $onlyPending ? 'Showing pending only' : 'Show pending only' ?>
                <?php if (!$onlyPending): ?><span class="dos-pending-toggle-count"><?= $pendingTotal ?></span><?php endif; ?>
                <?php if ($onlyPending): ?><i class="bi bi-x-circle ms-1"></i><?php endif; ?>
            </a>
        </div>
        <div class="dos-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="dosStudentSearchBox" class="form-control form-control-sm dos-input"
                   placeholder="Search this page...">
        </div>
    </div>

    <?= Html::beginForm(['site/bulk-moderate-marks'], 'post', ['id' => 'dosBatchActionForm', 'class' => 'm-0']) ?>

        <input type="hidden" name="class_level" value="<?= Html::encode($selectedClass) ?>">
        <input type="hidden" name="term" value="<?= Html::encode($selectedTerm) ?>">
        <input type="hidden" name="subject" value="<?= Html::encode($selectedSubject) ?>">
        <input type="hidden" name="moderation_action" id="bulkActionTrackerType" value="APPROVE_SELECTED">

        <div class="card dos-toolbar-card border-0 rounded-3 p-3 mb-3">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-5">
                    <div class="input-group input-group-sm dos-comment-group">
                        <span class="input-group-text"><i class="bi bi-chat-left-text"></i> Comment</span>
                        <input type="text" name="dos_comment" id="dosFeedbackTextInputField" class="form-control" placeholder="Required for rejections and unseal corrections...">
                    </div>
                </div>
                <div class="col-12 col-lg-7 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="button" id="btnApproveSelected" class="btn btn-sm dos-btn dos-btn-success"><i class="bi bi-check-all"></i> Approve Selected</button>
                    <button type="button" id="btnRejectSelected" class="btn btn-sm dos-btn dos-btn-danger"><i class="bi bi-x-circle"></i> Reject Selected</button>
                    <button type="button" id="btnApproveAll" class="btn btn-sm dos-btn dos-btn-success-outline"><i class="bi bi-shield-lock-fill"></i> Approve &amp; Seal All Pending</button>
                    <button type="button" id="btnRejectAll" class="btn btn-sm dos-btn dos-btn-danger-outline"><i class="bi bi-arrow-counterclockwise"></i> Reject All Pending</button>
                    <?php if ($canUnseal): ?>
                        <button type="button" id="btnUnsealSelected" class="btn btn-sm dos-btn dos-btn-warning"><i class="bi bi-unlock-fill"></i> Unseal Selected</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card dos-table-card border-0 rounded-3 overflow-hidden mb-5">
            <div class="table-responsive">
                <table id="dosGridTable" class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 4%;">No</th>
                            <th style="width: 26%;">Student</th>
                            <th style="width: 18%;">Subject</th>
                            <?php foreach ($columns as $label): ?>
                                <th class="text-center"><?= $label ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rawRecords)): ?>
                            <tr>
                                <td colspan="<?= 3 + count($columns) ?>" class="text-center py-5 dos-empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <div class="mt-2 fw-semibold">No records found for this class and term.</div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php $rowNum = 1; foreach ($rawRecords as $rec): ?>
                            <?php
                                $studentName = ucwords($rec['student_name']);
                                $initials = '';
                                foreach (array_slice(explode(' ', trim($studentName)), 0, 2) as $part) {
                                    $initials .= mb_substr($part, 0, 1);
                                }
                            ?>
                            <tr>
                                <td class="text-center dos-row-num"><?= $rowNum++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="dos-avatar"><?= Html::encode(mb_strtoupper($initials)) ?></span>
                                        <span class="fw-semibold dos-student-name"><?= Html::encode($studentName) ?></span>
                                    </div>
                                </td>
                                <td class="dos-subject"><?= Html::encode($rec['subject_name']) ?></td>

                                <?php foreach (array_keys($columns) as $colKey):
                                    $status = $rec[$colKey . '_status'] ?? 'NOT_SUBMITTED';
                                    $mark = (float)($rec[$colKey . '_mark'] ?? 0);
                                    $token = $rec['id'] . '_' . $colKey;
                                ?>
                                    <td class="text-center">
                                        <div class="dos-mark-cell">
                                            <?php if ($status !== 'NOT_SUBMITTED'): ?>
                                                <div class="dos-mark-value"><?= $mark ?></div>
                                            <?php endif; ?>

                                            <?php if ($status === 'PENDING_REVIEW'): ?>
                                                <label class="dos-status-check dos-status-pending">
                                                    <input type="checkbox" name="selected_marks[]" value="<?= $token ?>" class="form-check-input row-audit-grid-checkbox">
                                                    <span class="dos-badge dos-badge-pending"><i class="bi bi-hourglass-split"></i> Pending</span>
                                                </label>

                                            <?php elseif ($status === 'APPROVED_SEALED'): ?>
                                                <span class="dos-badge dos-badge-sealed d-block mb-1"><i class="bi bi-patch-check-fill"></i> Sealed</span>
                                                <?php if ($canUnseal): ?>
                                                    <label class="dos-status-check dos-status-unseal">
                                                        <input type="checkbox" name="selected_unseal[]" value="<?= $token ?>" class="form-check-input row-unseal-grid-checkbox">
                                                        <span class="dos-unseal-label">unseal</span>
                                                    </label>
                                                <?php endif; ?>

                                            <?php elseif ($status === 'REJECTED_AMEND'): ?>
                                                <span class="dos-badge dos-badge-returned"><i class="bi bi-arrow-left-right"></i> Returned</span>

                                            <?php else: ?>
                                                <span class="dos-badge dos-badge-empty"><i class="bi bi-dash-circle"></i> Not Submitted</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pagination->totalCount > 0): ?>
                <div class="dos-pager-bar d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span class="dos-pager-summary">
                        Showing <?= $pagination->offset + 1 ?>&ndash;<?= min($pagination->offset + $pagination->limit, $pagination->totalCount) ?>
                        of <?= $pagination->totalCount ?> records
                    </span>
                    <?= LinkPager::widget([
                        'pagination' => $pagination,
                        'options' => ['class' => 'pagination pagination-sm mb-0 dos-pagination'],
                        'linkOptions' => ['class' => 'page-link'],
                        'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'page-link'],
                        'maxButtonCount' => 7,
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>

    <?= Html::endForm() ?>
</div>

<style>
    :root {
        --kora-blue-900: #0b2a52;
        --kora-blue-800: #0f3a70;
        --kora-blue-700: #14488a;
        --kora-blue-accent: #3b82f6;
        --kora-blue-soft: rgba(59, 130, 246, 0.1);
        --kora-ink: #1e2a3a;
        --kora-muted: #64748b;
        --kora-border: #e7ecf3;
    }

    .site-dos {
        background: #f4f7fb;
        min-height: 100vh;
    }

    /* ===== Header ===== */
    .dos-page-title {
        color: var(--kora-ink);
        font-weight: 700;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .dos-page-title i {
        color: var(--kora-blue-accent);
    }

    .dos-page-subtitle {
        color: var(--kora-muted);
        font-size: 0.88rem;
    }

    .dos-summary-chip {
        background: linear-gradient(135deg, var(--kora-blue-800), var(--kora-blue-700));
        color: #fff;
        border-radius: 12px;
        padding: 0.6rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        box-shadow: 0 8px 20px rgba(15, 58, 112, 0.22);
    }

    .dos-summary-count {
        font-size: 1.4rem;
        font-weight: 800;
        line-height: 1;
    }

    .dos-summary-label {
        font-size: 0.75rem;
        font-weight: 600;
        opacity: 0.85;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* ===== Cards ===== */
    .dos-card, .dos-toolbar-card, .dos-table-card {
        background: #fff;
        border: 1px solid var(--kora-border) !important;
        box-shadow: 0 2px 10px rgba(15, 42, 82, 0.05);
    }

    .dos-label {
        color: var(--kora-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .dos-input {
        border: 1px solid var(--kora-border);
        border-radius: 8px;
    }

    .dos-input:focus {
        border-color: var(--kora-blue-accent);
        box-shadow: 0 0 0 0.2rem var(--kora-blue-soft);
    }

    .dos-btn-filter {
        background: var(--kora-blue-800);
        color: #fff;
        border-radius: 8px;
        border: none;
    }

    .dos-btn-filter:hover {
        background: var(--kora-blue-700);
        color: #fff;
    }

    /* ===== Class badge + search ===== */
    .dos-class-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--kora-blue-soft);
        color: var(--kora-blue-800);
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
    }

    .dos-class-badge-divider {
        opacity: 0.5;
    }

    .dos-search-wrap {
        position: relative;
        min-width: 260px;
    }

    .dos-search-wrap i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--kora-muted);
        font-size: 0.85rem;
    }

    .dos-search-wrap .form-control {
        padding-left: 2rem;
    }

    .dos-pending-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #fff;
        border: 1px solid var(--kora-border);
        color: var(--kora-muted);
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.5rem 0.9rem;
        border-radius: 50px;
        text-decoration: none;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .dos-pending-toggle:hover {
        border-color: var(--kora-blue-accent);
        color: var(--kora-blue-800);
        text-decoration: none;
    }

    .dos-pending-toggle.active {
        background: #fff3cd;
        border-color: #f0ad0b;
        color: #8a6400;
    }

    .dos-pending-toggle-count {
        background: var(--kora-blue-soft);
        color: var(--kora-blue-800);
        font-size: 0.7rem;
        font-weight: 800;
        border-radius: 50px;
        padding: 0.05rem 0.45rem;
    }

    .dos-pending-toggle.active .dos-pending-toggle-count {
        background: rgba(0, 0, 0, 0.08);
        color: #8a6400;
    }

    .dos-pager-bar {
        padding: 0.85rem 1rem;
        border-top: 1px solid var(--kora-border);
        background: #fafbfd;
    }

    .dos-pager-summary {
        color: var(--kora-muted);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .dos-pagination .page-link {
        border: 1px solid var(--kora-border);
        color: var(--kora-blue-800);
        font-weight: 600;
        font-size: 0.8rem;
    }

    .dos-pagination .page-item.active .page-link {
        background: var(--kora-blue-800);
        border-color: var(--kora-blue-800);
    }

    .dos-pagination .page-item.disabled .page-link {
        color: #cbd5e1;
    }

    /* ===== Toolbar ===== */
    .dos-comment-group .input-group-text {
        background: #f4f7fb;
        border: 1px solid var(--kora-border);
        color: var(--kora-muted);
        font-size: 0.8rem;
        font-weight: 600;
        gap: 0.4rem;
    }

    .dos-comment-group .form-control {
        border: 1px solid var(--kora-border);
        font-size: 0.85rem;
    }

    .dos-comment-group .form-control:focus {
        border-color: var(--kora-blue-accent);
        box-shadow: none;
    }

    .dos-btn {
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.78rem;
        padding: 0.45rem 0.9rem;
        border: 1px solid transparent;
    }

    .dos-btn-success { background: #198754; color: #fff; }
    .dos-btn-success:hover { background: #157347; color: #fff; }

    .dos-btn-danger { background: #dc3545; color: #fff; }
    .dos-btn-danger:hover { background: #bb2d3b; color: #fff; }

    .dos-btn-success-outline { background: #fff; color: #198754; border-color: #198754; }
    .dos-btn-success-outline:hover { background: #198754; color: #fff; }

    .dos-btn-danger-outline { background: #fff; color: #dc3545; border-color: #dc3545; }
    .dos-btn-danger-outline:hover { background: #dc3545; color: #fff; }

    .dos-btn-warning { background: #f0ad0b; color: #fff; }
    .dos-btn-warning:hover { background: #d99406; color: #fff; }

    /* ===== Table ===== */
    #dosGridTable thead th {
        background: var(--kora-blue-900);
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: none;
        padding: 0.85rem 0.75rem;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #dosGridTable tbody td {
        padding: 0.75rem;
        border-bottom: 1px solid var(--kora-border);
        font-size: 0.87rem;
    }

    #dosGridTable tbody tr:nth-child(even) {
        background: #fafbfd;
    }

    #dosGridTable tbody tr:hover {
        background: var(--kora-blue-soft);
    }

    #dosGridTable tbody tr:last-child td {
        border-bottom: none;
    }

    .dos-row-num {
        color: var(--kora-muted);
        font-size: 0.8rem;
    }

    .dos-avatar {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        border-radius: 50%;
        background: var(--kora-blue-800);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .dos-student-name {
        color: var(--kora-ink);
    }

    .dos-subject {
        color: var(--kora-muted);
        font-weight: 600;
        font-size: 0.85rem;
    }

    .dos-mark-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.3rem;
    }

    .dos-mark-value {
        font-family: 'Courier New', monospace;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--kora-ink);
    }

    .dos-status-check {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
        margin: 0;
    }

    .dos-status-check .form-check-input {
        width: 1em;
        height: 1em;
        margin: 0;
        cursor: pointer;
    }

    .dos-status-check .row-audit-grid-checkbox:checked {
        background-color: #198754;
        border-color: #198754;
    }

    .dos-status-check .row-unseal-grid-checkbox:checked {
        background-color: #f0ad0b;
        border-color: #f0ad0b;
    }

    .dos-unseal-label {
        color: #b8860b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .dos-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.68rem;
        font-weight: 700;
        border-radius: 6px;
        padding: 0.28rem 0.55rem;
        white-space: nowrap;
    }

    .dos-badge-pending { background: #fff3cd; color: #8a6400; }
    .dos-badge-sealed { background: #d1e7dd; color: #0f5132; }
    .dos-badge-returned { background: #f8d7da; color: #842029; }
    .dos-badge-empty { background: #eef1f5; color: var(--kora-muted); }

    .dos-empty-state {
        color: var(--kora-muted);
    }

    .dos-empty-state i {
        font-size: 2rem;
        opacity: 0.4;
    }

    .form-check-input:checked {
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