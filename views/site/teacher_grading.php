<?php
declare(strict_types=1);
/** @var yii\web\View $this */
/** @var array $assignments */
/** @var array $activeAssignment */
/** @var string $selectedTerm */
/** @var int $selectedYear */
/** @var array $availableYears */
/** @var app\models\Students[] $studentsList */
/** @var array $existingMarks */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Kora ERP - Teacher Grading Terminal';

function calculateUnebGrade($bot, $mot, $eot) {
    $finalScore = ($bot * 0.2) + ($mot * 0.3) + ($eot * 0.5);

    if ($finalScore >= 100) return ['G' => 'D1', 'R' => 'Excellent'];
    if ($finalScore >= 75) return ['G' => 'D2', 'R' => 'Very Good'];
    if ($finalScore >= 70) return ['G' => 'C3', 'R' => 'Good'];
    if ($finalScore >= 65) return ['G' => 'C4', 'R' => 'Competent'];
    if ($finalScore >= 60) return ['G' => 'C5', 'R' => 'Fairly Good'];
    if ($finalScore >= 50) return ['G' => 'C6', 'R' => 'Pass'];
    if ($finalScore >= 45) return ['G' => 'P7', 'R' => 'Modest Pass'];
    if ($finalScore >= 40) return ['G' => 'P8', 'R' => 'Weak Pass'];
    return ['G' => 'F9', 'R' => 'Fail'];
}

function columnStatusMeta(string $status): array {
    switch ($status) {
        case 'APPROVED_SEALED':
            return ['background-color:#e3f4ea;', 'cell-sealed', '<i class="bi bi-check-circle-fill text-success ms-1" title="Sealed"></i>'];
        case 'REJECTED_AMEND':
            return ['background-color:#fbe6e8;', 'cell-rejected', '<i class="bi bi-x-circle-fill text-danger ms-1" title="Returned for fix"></i>'];
        case 'PENDING_REVIEW':
            return ['background-color:#eaf1fb;', 'cell-pending', '<i class="bi bi-hourglass-split text-secondary ms-1" title="Pending review"></i>'];
        default:
            return ['', '', ''];
    }
}
?>

<div class="site-teacher-grading py-4">
    <div class="tg-page-container">

    <div class="tg-page-header mb-4">
        <h1 class="tg-page-title mb-1"><i class="bi bi-journal-bookmark-fill"></i> Academic Assessment Grading Grid</h1>
        <p class="tg-page-subtitle mb-0">Select your active subject tier workflow below to manage terminal assessment grids.</p>
    </div>

    <div class="card tg-card border-0 rounded-3 p-3 mb-4">
        <form method="get" action="<?= Url::toRoute(['site/teacher-grading']) ?>" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold tg-label">Active Subject &amp; Classroom Allocation</label>
                <select name="assignment_id" class="form-select form-select-sm tg-input fw-semibold" onchange="this.form.submit()">
                    <?php foreach ($assignments as $asg): ?>
                        <option value="<?= $asg['id'] ?>" <?= $activeAssignment && (int)$asg['id'] === (int)$activeAssignment['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($asg['class_level'] . ' - ' . $asg['subject_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
           <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold tg-label">Term</label>
                    <select name="term" class="form-select form-select-sm tg-input fw-semibold" onchange="this.form.submit()">
                        <option value="TERM_1" <?= $selectedTerm === 'TERM_1' ? 'selected' : '' ?>>Term 1</option>
                        <option value="TERM_2" <?= $selectedTerm === 'TERM_2' ? 'selected' : '' ?>>Term 2</option>
                        <option value="TERM_3" <?= $selectedTerm === 'TERM_3' ? 'selected' : '' ?>>Term 3</option>
                    </select>
                </div>
           <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold tg-label">Year</label>
                    <select name="year" class="form-select form-select-sm tg-input fw-semibold" onchange="this.form.submit()">
                        <?php foreach ($availableYears as $year): ?>
                            <option value="<?= (int)$year ?>" <?= $selectedYear === (int)$year ? 'selected' : '' ?>><?= (int)$year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
          <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-sm tg-btn-filter w-100 fw-bold">
                        <i class="bi bi-arrow-clockwise"></i> Reload Sheet
                    </button>
                </div>
        </form>
    </div>

<?php if ($activeAssignment && !empty($studentsList)): ?>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="tg-class-badge">
            <i class="bi bi-mortarboard"></i>
            <?= Html::encode($activeAssignment['class_level']) ?>
            <span class="tg-class-badge-divider">&middot;</span>
            <?= Html::encode($activeAssignment['subject_name']) ?>
        </div>
        <div class="tg-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="teacherStudentSearchBox" class="form-control form-control-sm tg-input"
                   placeholder="Search by student name...">
        </div>
    </div>

    <?= Html::beginForm(['site/submit-marks'], 'post', ['id' => 'gradingSheetForm']) ?>
        <input type="hidden" name="class_level" value="<?= Html::encode($activeAssignment['class_level']) ?>">
        <input type="hidden" name="subject_name" value="<?= Html::encode($activeAssignment['subject_name']) ?>">
        <input type="hidden" name="term" value="<?= Html::encode($selectedTerm) ?>">
        <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
        <input type="hidden" name="assignment_id" value="<?= (int)$activeAssignment['id'] ?>">

        <?php
        $showDosFeedbackColumn = false;
        foreach ($existingMarks as $markData) {
            $hasRejectedState = ($markData['bot_status'] ?? '') === 'REJECTED_AMEND'
                || ($markData['mot_status'] ?? '') === 'REJECTED_AMEND'
                || ($markData['eot_status'] ?? '') === 'REJECTED_AMEND';
            $hasDosComment = !empty(trim((string)($markData['dos_feedback'] ?? '')));

            if ($hasRejectedState || $hasDosComment) {
                $showDosFeedbackColumn = true;
                break;
            }
        }
        ?>

        <div class="card tg-toolbar-card border-0 rounded-3 p-3 mb-3">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-6">
                    <span class="tg-toolbar-hint text-white"><i class="bi bi-info-circle-fill"></i> Check specific student rows to send partial grades, or commit all rows at once.</span>
                </div>
                <div class="col-12 col-md-6 text-md-end d-flex flex-wrap gap-2 justify-content-md-end">
                    <button type="button" id="btnTeacherSubmitSelected" class="btn btn-sm tg-btn tg-btn-primary flex-fill flex-md-grow-0"><i class="bi bi-check-square-fill"></i> Submit Selected Rows</button>
                    <button type="button" id="btnTeacherSubmitAll" class="btn btn-sm tg-btn tg-btn-primary-outline flex-fill flex-md-grow-0"><i class="bi bi-cloud-arrow-up-fill"></i> Submit All Rows</button>
                </div>
            </div>
        </div>

        <!-- Legend / key -->
        <div class="d-flex flex-wrap gap-3 align-items-center mb-3 px-1">
            <span class="small fw-bold tg-legend-label">Key:</span>
            <span class="d-flex align-items-center small tg-legend-item"><span class="bi bi-check-circle-fill text-success ms-1 me-1"></span>  Sealed</span>
            <span class="d-flex align-items-center small tg-legend-item"><span class="bi bi-hourglass-split text-secondary ms-1 me-1"></span> Pending Review</span>
            <span class="d-flex align-items-center small tg-legend-item"><span class="bi bi-x-circle-fill text-danger ms-1 me-1"></span> Rejected / Returned for Fix</span>
            <span class="d-flex align-items-center small tg-legend-item"><span class="legend-swatch cell-blank me-1"></span> Not Submitted</span>
        </div>

        <div class="tg-scroll-hint d-md-none mb-2">
            <i class="bi bi-arrow-left-right"></i> Scroll sideways to see all score columns - name stays pinned.
        </div>

        <div class="card tg-table-card border-0 rounded-3 overflow-hidden mb-3">
            <div class="table-responsive">
                <table id="teacherGradingTable" class="table align-middle mb-0">
                    <thead>
                        <tr>
                        <?php if ($showDosFeedbackColumn): ?>
                            <th class="text-center tg-sticky-col tg-sticky-col-1" style="width: 3%;">No</th>
                            <th class="text-center tg-sticky-col tg-sticky-col-2" style="width: 4%;">
                                <input type="checkbox" id="masterTeacherGridCheckboxSelector" class="form-check-input" title="Select all rows">
                            </th>
                            <th class="ps-4 tg-sticky-col tg-sticky-col-3" style="width: 18%;">Student Full Name</th>
                            <th class="text-center" style="width: 7%;">BOT (20%)</th>
                            <th class="text-center" style="width: 7%;">MOT (30%)</th>
                            <th class="text-center" style="width: 7%;">EOT (50%)</th>
                            <th class="text-center" style="width: 7%;">UNEB Grade</th>
                            <th class="pe-4" style="width: 25%;">Teacher Remarks &amp; Comments</th>
                            <th class="text-center" style="width: 22%;">DOS Comment</th>
                        <?php else: ?>
                            <th class="text-center tg-sticky-col tg-sticky-col-1" style="width: 3%;">No</th>
                            <th class="text-center tg-sticky-col tg-sticky-col-2" style="width: 4%;">
                                <input type="checkbox" id="masterTeacherGridCheckboxSelector" class="form-check-input" title="Select all rows">
                            </th>
                            <th class="ps-3 tg-sticky-col tg-sticky-col-3" style="width: 22%;">Student Full Name</th>
                            <th class="text-center" style="width: 10%;">BOT (20%)</th>
                            <th class="text-center" style="width: 10%;">MOT (30%)</th>
                            <th class="text-center" style="width: 10%;">EOT (50%)</th>
                            <th class="text-center" style="width: 10%;">UNEB Grade</th>
                            <th style="width: 31%;">Teacher Remarks &amp; Comments</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php $rowNum = 1; foreach ($studentsList as $st):
                            $botStatus = $existingMarks[$st->id]['bot_status'] ?? 'NOT_SUBMITTED';
                            $motStatus = $existingMarks[$st->id]['mot_status'] ?? 'NOT_SUBMITTED';
                            $eotStatus = $existingMarks[$st->id]['eot_status'] ?? 'NOT_SUBMITTED';

                            $mBot = $botStatus === 'NOT_SUBMITTED' ? '' : (float)($existingMarks[$st->id]['bot_mark'] ?? 0);
                            $mMot = $motStatus === 'NOT_SUBMITTED' ? '' : (float)($existingMarks[$st->id]['mot_mark'] ?? 0);
                            $mEot = $eotStatus === 'NOT_SUBMITTED' ? '' : (float)($existingMarks[$st->id]['eot_mark'] ?? 0);

                            $uneb = calculateUnebGrade((float)$mBot, (float)$mMot, (float)$mEot);

                            $mComment = $existingMarks[$st->id]['teacher_comment'] ?? '';
                            $mDosFeedback = $existingMarks[$st->id]['dos_feedback'] ?? '';

                            $userRole = Yii::$app->user->identity->role;

                            $botLocked = $userRole !== 'SCHOOL_ADMIN' && in_array($botStatus, ['APPROVED_SEALED', 'PENDING_REVIEW']);
                            $motLocked = $userRole !== 'SCHOOL_ADMIN' && in_array($motStatus, ['APPROVED_SEALED', 'PENDING_REVIEW']);
                            $eotLocked = $userRole !== 'SCHOOL_ADMIN' && in_array($eotStatus, ['APPROVED_SEALED', 'PENDING_REVIEW']);
                            $rowFullyLocked = $botLocked && $motLocked && $eotLocked;

                            $disableBot = $botLocked ? 'disabled' : '';
                            $disableMot = $motLocked ? 'disabled' : '';
                            $disableEot = $eotLocked ? 'disabled' : '';

                            [$botCellStyle, $botCellClass, $botIcon] = columnStatusMeta($botStatus);
                            [$motCellStyle, $motCellClass, $motIcon] = columnStatusMeta($motStatus);
                            [$eotCellStyle, $eotCellClass, $eotIcon] = columnStatusMeta($eotStatus);
                        ?>
                            <tr>
                                <td class="text-center tg-row-num tg-sticky-col tg-sticky-col-1"><?= $rowNum++ ?></td>
                                <td class="text-center tg-sticky-col tg-sticky-col-2">
                                    <?php if ($userRole === 'SCHOOL_ADMIN' || !$rowFullyLocked): ?>
                                        <input type="checkbox" name="selected_students[]" value="<?= $st->id ?>" class="form-check-input row-teacher-grid-checkbox">
                                    <?php else: ?>
                                        <i class="bi bi-lock-fill tg-locked-icon" title="Sealed and final"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="ps-3 fw-semibold tg-student-name tg-sticky-col tg-sticky-col-3">
                                    <?= Html::encode(ucwords($st->name)) ?>
                                </td>

                                <td class="<?= $botCellClass ?>" style="<?= $botCellStyle ?>">
                                    <div class="d-flex align-items-center">
                                        <input type="number" inputmode="decimal" name="Scores[<?= $st->id ?>][bot]" value="<?= $mBot ?>" <?= $disableBot ?> min="0" max="100" step="0.01" class="form-control form-control-sm text-center grading-input-score" data-weight="0.2" data-col="bot" data-row="<?= $st->id ?>">
                                        <?= $botIcon ?>
                                    </div>
                                    <input type="hidden" name="Scores[<?= $st->id ?>][bot_touched]" id="botTouched<?= $st->id ?>" value="0">
                                </td>
                                <td class="<?= $motCellClass ?>" style="<?= $motCellStyle ?>">
                                    <div class="d-flex align-items-center">
                                        <input type="number" inputmode="decimal" name="Scores[<?= $st->id ?>][mot]" value="<?= $mMot ?>" <?= $disableMot ?> min="0" max="100" step="0.01" class="form-control form-control-sm text-center grading-input-score" data-weight="0.3" data-col="mot" data-row="<?= $st->id ?>">
                                        <?= $motIcon ?>
                                    </div>
                                    <input type="hidden" name="Scores[<?= $st->id ?>][mot_touched]" id="motTouched<?= $st->id ?>" value="0">
                                </td>
                                <td class="<?= $eotCellClass ?>" style="<?= $eotCellStyle ?>">
                                    <div class="d-flex align-items-center">
                                        <input type="number" inputmode="decimal" name="Scores[<?= $st->id ?>][eot]" value="<?= $mEot ?>" <?= $disableEot ?> min="0" max="100" step="0.01" class="form-control form-control-sm text-center grading-input-score" data-weight="0.5" data-col="eot" data-row="<?= $st->id ?>">
                                        <?= $eotIcon ?>
                                    </div>
                                    <input type="hidden" name="Scores[<?= $st->id ?>][eot_touched]" id="eotTouched<?= $st->id ?>" value="0">
                                </td>
                                <td class="text-center">
                                    <span class="tg-uneb-badge live-row-uneb-badge"><?= $uneb['G'] ?></span>
                                </td>
                                <td>
                                    <input type="text" name="Scores[<?= $st->id ?>][comment]" value="<?= Html::encode($mComment) ?>" class="form-control form-control-sm tg-input">
                                </td>
                                <?php if ($showDosFeedbackColumn): ?>
                                    <td class="pe-3">
                                        <?php if (!empty($mDosFeedback)): ?>
                                            <div class="tg-dos-feedback">
                                                <i class="bi bi-chat-left-text-fill me-1"></i> <?= Html::encode($mDosFeedback) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small opacity-50">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?= Html::endForm() ?>
    <?php else: ?>
        <div class="tg-empty-alert p-4 text-center rounded-3">
            <i class="bi bi-exclamation-triangle-fill d-block mb-2"></i>
            <strong>No active assignments found!</strong> You are not currently assigned to teach any subjects for the chosen filter configuration.
        </div>
    <?php endif; ?>

    </div>
</div>

<script>

function updateLiveGradeRow(inputElement) {
    const row = inputElement.closest('tr');
    const inputs = row.querySelectorAll('.grading-input-score');

    let weightedScore = 0;
    inputs.forEach(inp => {
        const val = parseFloat(inp.value) || 0;
        const weight = parseFloat(inp.getAttribute('data-weight')) || 0;
        weightedScore += (val * weight);
    });

    let unebLetter = 'F9';
    if (weightedScore >= 100) unebLetter = 'D1';
    else if (weightedScore >= 75) unebLetter = 'D2';
    else if (weightedScore >= 70) unebLetter = 'C3';
    else if (weightedScore >= 65) unebLetter = 'C4';
    else if (weightedScore >= 60) unebLetter = 'C5';
    else if (weightedScore >= 50) unebLetter = 'C6';
    else if (weightedScore >= 45) unebLetter = 'P7';
    else if (weightedScore >= 40) unebLetter = 'P8';

    row.querySelector('.live-row-uneb-badge').innerText = unebLetter;
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const masterCheckbox = document.getElementById('masterTeacherGridCheckboxSelector');
    const rowCheckboxes = document.querySelectorAll('.row-teacher-grid-checkbox');
    const gradingForm = document.getElementById('gradingSheetForm');
    const gradingInputs = document.querySelectorAll('.grading-input-score');
    const searchBox = document.getElementById('teacherStudentSearchBox');
    const table = document.getElementById('teacherGradingTable');

    if (masterCheckbox) {
        masterCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = masterCheckbox.checked;
                }
            });
        });
    }

    gradingInputs.forEach((input) => {
        input.addEventListener('input', function() {
            updateLiveGradeRow(this);
            const col = this.getAttribute('data-col');
            const rowId = this.getAttribute('data-row');
            const touchedField = document.getElementById(col + 'Touched' + rowId);
            if (touchedField) {
                touchedField.value = '1';
            }
        });
    });

    if (searchBox && table) {
        searchBox.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    }

    function fireTeacherSubmitAction(submissionMode) {
        if (submissionMode === 'SUBMIT_SELECTED') {
            const checkedCount = document.querySelectorAll('.row-teacher-grid-checkbox:checked').length;
            if (checkedCount === 0) {
                alert("No rows selected! Please check the boxes next to the students whose marks you want to send.");
                return;
            }
        }

        const confirmationMessage = submissionMode === 'SUBMIT_ALL'
            ? "Are you sure you want to submit marks for ALL students in this class stream?"
            : "Are you sure you want to submit marks ONLY for the selected students?";

        if (confirm(confirmationMessage)) {
            let modeInput = document.getElementById('teacherSubmissionModeTracker');
            if (!modeInput) {
                modeInput = document.createElement('input');
                modeInput.type = 'hidden';
                modeInput.name = 'submission_mode';
                modeInput.id = 'teacherSubmissionModeTracker';
                gradingForm.appendChild(modeInput);
            }
            modeInput.value = submissionMode;
            gradingForm.submit();
        }
    }

    const btnSubmitSelected = document.getElementById('btnTeacherSubmitSelected');
    const btnSubmitAll = document.getElementById('btnTeacherSubmitAll');

    if (btnSubmitSelected) {
        btnSubmitSelected.addEventListener('click', () => fireTeacherSubmitAction('SUBMIT_SELECTED'));
    }

    if (btnSubmitAll) {
        btnSubmitAll.addEventListener('click', () => fireTeacherSubmitAction('SUBMIT_ALL'));
    }
});
</script>

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

    .site-teacher-grading {
        background: #f4f7fb;
        min-height: 100vh;
    }

    .tg-page-container {
        max-width: 96rem;
        margin: 0 auto;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    /* ===== Header ===== */
    .tg-page-title {
        color: var(--kora-ink);
        font-weight: 700;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .tg-page-title i {
        color: var(--kora-blue-accent);
    }

    .tg-page-subtitle {
        color: var(--kora-muted);
        font-size: 0.88rem;
    }

    .tg-card, .tg-toolbar-card, .tg-table-card {
        background: #fff;
        border: 1px solid var(--kora-border) !important;
        box-shadow: 0 2px 10px rgba(15, 42, 82, 0.05);
    }

    .tg-label {
        color: var(--kora-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .tg-input {
        border: 1px solid var(--kora-border);
        border-radius: 8px;
    }

    .tg-input:focus {
        border-color: var(--kora-blue-accent);
        box-shadow: 0 0 0 0.2rem var(--kora-blue-soft);
    }

    .tg-btn-filter {
        background: var(--kora-blue-800);
        color: #fff;
        border-radius: 8px;
        border: none;
    }

    .tg-btn-filter:hover {
        background: var(--kora-blue-700);
        color: #fff;
    }

    .tg-class-badge {
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

    .tg-class-badge-divider {
        opacity: 0.5;
    }

    .tg-search-wrap {
        position: relative;
        min-width: 260px;
        flex: 1 1 260px;
    }

    .tg-search-wrap i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--kora-muted);
        font-size: 0.85rem;
    }

    .tg-search-wrap .form-control {
        padding-left: 2rem;
        width: 100%;
    }

    /* ===== Toolbar ===== */
    .tg-toolbar-card {
        background: linear-gradient(135deg, var(--kora-blue-900), var(--kora-blue-800));
        border: none !important;
    }

    .tg-toolbar-hint {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .tg-toolbar-hint i {
        color: #7dd3fc;
    }

    .tg-btn {
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        border: 1px solid transparent;
    }

    .tg-btn-primary {
        background: var(--kora-blue-accent);
        color: #fff;
    }

    .tg-btn-primary:hover {
        background: #2563eb;
        color: #fff;
    }

    .tg-btn-primary-outline {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        border-color: rgba(255, 255, 255, 0.35);
    }

    .tg-btn-primary-outline:hover {
        background: #fff;
        color: var(--kora-blue-800);
    }

    /* ===== Legend ===== */
    .tg-legend-label {
        color: var(--kora-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.7rem;
    }

    .tg-legend-item {
        color: var(--kora-ink);
        font-size: 0.8rem;
    }

    .legend-swatch {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 4px;
        border: 1px solid rgba(0,0,0,0.1);
    }
    .legend-swatch.cell-sealed   { background-color: #e3f4ea; }
    .legend-swatch.cell-pending  { background-color: #eaf1fb; }
    .legend-swatch.cell-rejected { background-color: #fbe6e8; }
    .legend-swatch.cell-blank    { background-color: #ffffff; }

    .tg-scroll-hint {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        color: var(--kora-muted);
        font-weight: 600;
    }

    #teacherGradingTable thead th {
        background: var(--kora-blue-900);
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border: none;
        padding: 0.8rem 0.6rem;
        position: sticky;
        top: 0;
        z-index: 2;
        white-space: nowrap;
    }

    #teacherGradingTable tbody td {
        padding: 0.6rem;
        border-bottom: 1px solid var(--kora-border);
        font-size: 0.85rem;
        vertical-align: middle;
    }

    #teacherGradingTable tbody tr:nth-child(even) {
        background: #fafbfd;
    }

    #teacherGradingTable tbody tr:hover {
        background: var(--kora-blue-soft);
    }

    #teacherGradingTable tbody tr:last-child td {
        border-bottom: none;
    }

    .tg-row-num {
        color: var(--kora-muted);
        font-size: 0.78rem;
    }

    .tg-student-name {
        color: var(--kora-ink);
    }

    .tg-locked-icon {
        color: #22c55e;
    }

    .tg-uneb-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.4rem;
        padding: 0.3rem 0.5rem;
        border-radius: 6px;
        background: var(--kora-blue-soft);
        color: var(--kora-blue-800);
        font-family: 'Courier New', monospace;
        font-weight: 800;
        font-size: 0.85rem;
    }

    .tg-dos-feedback {
        background: #fbe6e8;
        color: #842029;
        border-radius: 6px;
        padding: 0.4rem 0.6rem;
        font-size: 0.78rem;
        font-weight: 600;
    }

    #teacherGradingTable td .grading-input-score {
        background-color: transparent !important;
        border: 1px solid var(--kora-border);
        border-radius: 6px;
        min-width: 3.5rem;
    }

    #teacherGradingTable td .grading-input-score:focus {
        border-color: var(--kora-blue-accent);
        box-shadow: 0 0 0 0.15rem var(--kora-blue-soft);
    }

    .form-check-input {
        width: 1.1em;
        cursor: pointer;
    }
    .form-check-input:checked {
        background-color: var(--kora-blue-accent) !important;
        border-color: var(--kora-blue-accent) !important;
    }

    /* Pin No / Select / Name columns while scrolling horizontally on small screens */
    @media (max-width: 767.98px) {
        .tg-sticky-col {
            position: sticky;
            background: #fff;
            z-index: 1;
        }
        thead .tg-sticky-col {
            z-index: 3;
            background: var(--kora-blue-900);
        }
        #teacherGradingTable tbody tr:nth-child(even) .tg-sticky-col {
            background: #fafbfd;
        }
        .tg-sticky-col-1 { left: 0; min-width: 2.2rem; }
        .tg-sticky-col-2 { left: 2.2rem; min-width: 2.6rem; }
        .tg-sticky-col-3 {
            left: 4.8rem;
            min-width: 9rem;
            box-shadow: 2px 0 4px rgba(0,0,0,0.06);
        }
    }

    .tg-empty-alert {
        background: #fff8e6;
        border: 1px solid #f5d78e;
        color: #8a6400;
        box-shadow: 0 2px 10px rgba(15, 42, 82, 0.05);
    }

    .tg-empty-alert i {
        font-size: 1.8rem;
        color: #d99406;
    }
</style>