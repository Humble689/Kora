<?php
declare(strict_types=1);
/** @var yii\web\View $this */
/** @var array $assignments */
/** @var array $activeAssignment */
/** @var string $selectedTerm */
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
            return ['background-color:#e6f6ec;', 'cell-sealed', '<i class="bi bi-check-circle-fill text-success ms-1" title="Sealed"></i>'];
        case 'REJECTED_AMEND':
            return ['background-color:#fdecea;', 'cell-rejected', '<i class="bi bi-x-circle-fill text-danger ms-1" title="Returned for fix"></i>'];
        case 'PENDING_REVIEW':
            return ['background-color:#eceff1;', 'cell-pending', '<i class="bi bi-hourglass-split text-secondary ms-1" title="Pending review"></i>'];
        default:
            return ['', '', ''];
    }
}
?>

<div class="site-teacher-grading bg-light py-3 min-vh-100">

    <div class="card card-body border-0 shadow-sm rounded-3 mb-4 p-4">
        <h2 class="h4 fw-bold mb-1 text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Academic Assessment Grading Grid</h2>
        <p class="text-muted small mb-0">Select your active subject tier workflow below to manage terminal assessment grids.</p>
    </div>

    <div class="card card-body border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
        <form method="get" action="<?= Url::toRoute(['site/teacher-grading']) ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-secondary">Active Subject & Classroom Allocation</label>
                <select name="assignment_id" class="form-select form-select-sm fw-bold" onchange="this.form.submit()">
                    <?php foreach ($assignments as $asg): ?>
                        <option value="<?= $asg['id'] ?>" <?= $activeAssignment && (int)$asg['id'] === (int)$activeAssignment['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($asg['class_level'] . ' - ' . $asg['subject_name']) ?>
                        </option>
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
                <button type="submit" class="btn btn-sm btn-dark w-100 fw-bold"><i class="bi bi-arrow-clockwise"></i> Reload Sheet</button>
            </div>
        </form>
    </div>

<?php if ($activeAssignment && !empty($studentsList)): ?>

    <div class="mb-2">
        <input type="text" id="teacherStudentSearchBox" class="form-control form-control-sm"
               placeholder="Search by student name...">
    </div>

    <?= Html::beginForm(['site/submit-marks'], 'post', ['id' => 'gradingSheetForm']) ?>
        <input type="hidden" name="class_level" value="<?= Html::encode($activeAssignment['class_level']) ?>">
        <input type="hidden" name="subject_name" value="<?= Html::encode($activeAssignment['subject_name']) ?>">
        <input type="hidden" name="term" value="<?= Html::encode($selectedTerm) ?>">
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

        <div class="card card-body border-0 shadow-sm rounded-3 p-3 mb-3 bg-dark text-white">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-6">
                    <span class="text-black fw-bold small"><i class="bi bi-info-circle-fill text-info me-1"></i> Check specific student rows to send partial grades, or commit all rows at once.</span>
                </div>
                <div class="col-12 col-md-6 text-md-end d-flex gap-2 justify-content-md-end">
                    <button type="button" id="btnTeacherSubmitSelected" class="btn btn-sm btn-primary fw-bold px-3 rounded-2 shadow-sm"><i class="bi bi-check-square-fill"></i> Submit Selected Rows</button>
                    <button type="button" id="btnTeacherSubmitAll" class="btn btn-sm btn-outline-success fw-bold px-3 rounded-2"><i class="bi bi-cloud-arrow-up-fill"></i> Submit All Rows</button>
                </div>
            </div>
        </div>

        <!-- Legend / key -->
        <div class="d-flex flex-wrap gap-3 align-items-center mb-3 px-1">
            <span class="small fw-bold text-secondary text-uppercase">Key:</span>
            <span class="d-flex align-items-center small"><span class="legend-swatch cell-sealed me-1"></span> Sealed</span>
            <span class="d-flex align-items-center small"><span class="legend-swatch cell-pending me-1"></span> Pending Review</span>
            <span class="d-flex align-items-center small"><span class="legend-swatch cell-rejected me-1"></span> Rejected / Returned for Fix</span>
            <span class="d-flex align-items-center small"><span class="legend-swatch cell-blank me-1"></span> Not Submitted</span>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-3">
            <div class="table-responsive">
                <table id="teacherGradingTable" class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark text-xs font-monospace uppercase">
                        <tr>
                        <?php if ($showDosFeedbackColumn): ?>
                            <th class="text-center" style="width: 3%;">No</th>
                            <th class="text-center" style="width: 4%;">Select</th>
                            <th class="ps-4" style="width: 18%;">Student Full Name</th>
                            <th class="text-center" style="width: 7%;">BOT (20%)</th>
                            <th class="text-center" style="width: 7%;">MOT (30%)</th>
                            <th class="text-center" style="width: 7%;">EOT (50%)</th>
                            <th class="text-center" style="width: 7%;">UNEB Grade</th>
                            <th class="pe-4" style="width: 25%;">Teacher Remarks & Comments</th>
                            <th class="text-center text-danger" style="width: 22%;"><h6 class="fw-bold text-danger mb-1">DOS Comment</h6></th>
                        <?php else: ?>
                            <th class="text-center" style="width: 3%;">No</th>
                            <th class="text-center" style="width: 4%;">Select</th>
                            <th class="ps-3" style="width: 22%;">Student Full Name</th>
                            <th class="text-center" style="width: 10%;">BOT (20%)</th>
                            <th class="text-center" style="width: 10%;">MOT (30%)</th>
                            <th class="text-center" style="width: 10%;">EOT (50%)</th>
                            <th class="text-center" style="width: 10%;">UNEB Grade</th>
                            <th style="width: 31%;">Teacher Remarks & Comments</th>
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
                                <td class="text-center text-muted"><?= $rowNum++ ?></td>
                                <td class="text-center">
                                    <?php if ($userRole === 'SCHOOL_ADMIN' || !$rowFullyLocked): ?>
                                        <input type="checkbox" name="selected_students[]" value="<?= $st->id ?>" class="form-check-input border-dark row-teacher-grid-checkbox">
                                    <?php else: ?>
                                        <i class="bi bi-lock-fill text-success small" title="Sealed and final"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="ps-3 fw-bold text-dark">
                                    <?= Html::encode(ucwords($st->name)) ?>
                                </td>

                                <td class="<?= $botCellClass ?>" style="<?= $botCellStyle ?>">
                                    <div class="d-flex align-items-center ">
                                        <input type="text" name="Scores[<?= $st->id ?>][bot]" value="<?= $mBot ?>" <?= $disableBot ?> min="0" max="100" class="form-control form-control-sm text-center grading-input-score" data-weight="0.2" data-col="bot" data-row="<?= $st->id ?>">
                                        <?= $botIcon ?>
                                    </div>
                                    <input type="hidden" name="Scores[<?= $st->id ?>][bot_touched]" id="botTouched<?= $st->id ?>" value="0">
                                </td>
                                <td class="<?= $motCellClass ?>" style="<?= $motCellStyle ?>">
                                    <div class="d-flex align-items-center">
                                        <input type="text" name="Scores[<?= $st->id ?>][mot]" value="<?= $mMot ?>" <?= $disableMot ?> min="0" max="100" class="form-control form-control-sm text-center grading-input-score" data-weight="0.3" data-col="mot" data-row="<?= $st->id ?>">
                                        <?= $motIcon ?>
                                    </div>
                                    <input type="hidden" name="Scores[<?= $st->id ?>][mot_touched]" id="motTouched<?= $st->id ?>" value="0">
                                </td>
                                <td class="<?= $eotCellClass ?>" style="<?= $eotCellStyle ?>">
                                    <div class="d-flex align-items-center">
                                        <input type="text" name="Scores[<?= $st->id ?>][eot]" value="<?= $mEot ?>" <?= $disableEot ?> min="0" max="100" class="form-control form-control-sm text-center grading-input-score" data-weight="0.5" data-col="eot" data-row="<?= $st->id ?>">
                                        <?= $eotIcon ?>
                                    </div>
                                    <input type="hidden" name="Scores[<?= $st->id ?>][eot_touched]" id="eotTouched<?= $st->id ?>" value="0">
                                </td>
                                <td class="text-center font-monospace fw-bold">
                                    <span class="badge bg-light text-dark border px-2.5 py-1.5 fs-6 live-row-uneb-badge"><?= $uneb['G'] ?></span>
                                </td>
                                <td>
                                    <input type="text" name="Scores[<?= $st->id ?>][comment]" value="<?= Html::encode($mComment) ?>" class="form-control form-control-sm">
                                </td>
                                <?php if ($showDosFeedbackColumn): ?>
                                    <td class="pe-3">
                                        <?php if (!empty($mDosFeedback)): ?>
                                            <div class="p-1.5 rounded bg-white text-danger form-control form-control-sm font-monospace">
                                                <i class="bi bi-chat-left-text-fill me-1"></i> <?= Html::encode($mDosFeedback) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted font-monospace text-xs opacity-50"></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

           
        <?= Html::endForm() ?>
    <?php else: ?>
        <div class="alert alert-warning border-0 p-4 shadow-sm text-center rounded-3">
            <i class="bi bi-exclamation-trianglefs-3 d-block mb-2"></i>
            <strong>No active assignments found!</strong> You are not currently assigned to teach any subjects for the chosen filter configuration.
        </div>
    <?php endif; ?>
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
.bg-success-subtle { background-color: #d1e7dd !important; color: #0f5132 !important; }
.bg-warning-subtle { background-color: #fff3cd !important; color: #664d03 !important; }
.bg-danger-subtle { background-color: #f8d7da !important; color: #842029 !important; }
.bg-info-subtle { background-color: #cff4fc !important; color: #055160 !important; }
.bg-secondary-subtle { background-color: #e2e3e5 !important; color: #41464b !important; }

/* Mark cell status paint — matches the key */
.cell-sealed   { background-color: #4cc58e !important; }   /* light green */
.cell-pending  { background-color: #e9ecef !important; }   /* light grey */
.cell-rejected { background-color: #f8d7da !important; }   /* light red */

.legend-swatch {
    display: inline-block;
    width: 14px;
    height: 14px;
    border-radius: 3px;
    border: 1px solid rgba(0,0,0,0.15);
}
.legend-swatch.cell-sealed   { background-color: #e6f6ec; }
.legend-swatch.cell-pending  { background-color: #eceff1; }
.legend-swatch.cell-rejected { background-color: #fdecea; }
.legend-swatch.cell-blank    { background-color: #ffffff; }

#teacherGradingTable td .grading-input-score {
    background-color: transparent !important;
}

.form-check-input {
    width: 1.1em;
}
.form-check-input:checked {
    background-color: #198754 !important;
    border-color: #198754 !important;
}
</style>