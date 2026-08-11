<?php
/** @var yii\web\View $this */
/** @var array $assignments */
/** @var array $activeAssignment */
/** @var string $selectedTerm */
/** @var app\models\Students[] $studentsList */
/** @var array $existingMarks */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Grading Operations Desk';

function calculateUnebGrade($bot, $mot, $eot) {
    // Weighting: 20% BOT + 30% MOT + 50% EOT
    $finalScore = ($bot * 0.2) + ($mot * 0.3) + ($eot * 0.5);
    
    if ($finalScore >= 80) return ['G' => 'D1', 'R' => 'Excellent'];
    if ($finalScore >= 75) return ['G' => 'D2', 'R' => 'Very Good'];
    if ($finalScore >= 70) return ['G' => 'C3', 'R' => 'Good'];
    if ($finalScore >= 65) return ['G' => 'C4', 'R' => 'Competent'];
    if ($finalScore >= 60) return ['G' => 'C5', 'R' => 'Fairly Good'];
    if ($finalScore >= 50) return ['G' => 'C6', 'R' => 'Pass'];
    if ($finalScore >= 45) return ['G' => 'P7', 'R' => 'Modest Pass'];
    if ($finalScore >= 40) return ['G' => 'P8', 'R' => 'Weak Pass'];
    return ['G' => 'F9', 'R' => 'Fail'];
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
        <?= Html::beginForm(['site/submit-marks'], 'post', ['id' => 'gradingSheetForm']) ?>
            <input type="hidden" name="class_level" value="<?= Html::encode($activeAssignment['class_level']) ?>">
            <input type="hidden" name="subject_name" value="<?= Html::encode($activeAssignment['subject_name']) ?>">
            <input type="hidden" name="term" value="<?= Html::encode($selectedTerm) ?>">
            <input type="hidden" name="assignment_id" value="<?= (int)$activeAssignment['id'] ?>">

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-dark text-xs">
                                               <tr>
                                <th class="ps-4" style="width: 25%;">Student Full Name</th>
                                <th class="text-center" style="width: 10%;">BOT (20%)</th>
                                <th class="text-center" style="width: 10%;">MOT (30%)</th>
                                <th class="text-center" style="width: 10%;">EOT (50%)</th>
                                <th class="text-center" style="width: 10%;">UNEB Grade</th>
                                <th class="pe-4" style="width: 35%;">Teacher Remarks & Competency Assessment Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentsList as $st): 
                                $mBot = (float)($existingMarks[$st->id]['bot_mark'] ?? 0);
                                $mMot = (float)($existingMarks[$st->id]['mot_mark'] ?? 0);
                                $mEot = (float)($existingMarks[$st->id]['eot_mark'] ?? 0);
                                $mComment = $existingMarks[$st->id]['teacher_comment'] ?? '';
                                $currentStatus = $existingMarks[$st->id]['status'] ?? '';
                                $isSealed = ($currentStatus === 'APPROVED_SEALED');
                                $isRejected = ($currentStatus === 'REJECTED_AMEND');
                                $uneb = calculateUnebGrade($mBot, $mMot, $mEot);
                            ?>
                                <tr class="<?= $isSealed ? 'table-light text-muted' : '' ?>">
                                    <td class="ps-4 fw-bold text-dark">
                                        <?= Html::encode($st->name) ?>
                                        <?php if ($isSealed): ?>
                                            <span class="badge bg-success ms-1 small text-white" title="DOS Sealed"><i class="bi bi-lock-fill"></i> Sealed</span>
                                        <?php elseif ($isRejected): ?>
                                            <span class="badge bg-danger ms-1 small text-white" title="Revision Required"><i class="bi bi-exclamation-circle"></i> Revision</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="number" name="Scores[<?= $st->id ?>][bot]" value="<?= $mBot ?>" min="0" max="100" class="form-control form-control-sm text-center grading-input-score" <?= $isSealed ? 'disabled' : '' ?> data-weight="0.2" oninput="updateLiveGradeRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" name="Scores[<?= $st->id ?>][mot]" value="<?= $mMot ?>" min="0" max="100" class="form-control form-control-sm text-center grading-input-score" <?= $isSealed ? 'disabled' : '' ?> data-weight="0.3" oninput="updateLiveGradeRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" name="Scores[<?= $st->id ?>][eot]" value="<?= $mEot ?>" min="0" max="100" class="form-control form-control-sm text-center grading-input-score" <?= $isSealed ? 'disabled' : '' ?> data-weight="0.5" oninput="updateLiveGradeRow(this)">
                                    </td>
                                    <td class="text-center fw-bold">
                                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fs-6 live-row-uneb-badge"><?= $uneb['G'] ?></span>
                                    </td>
                                    <td class="pe-4">
                                        <input type="text" name="Scores[<?= $st->id ?>][comment]" value="<?= Html::encode($mComment) ?>" class="form-control form-control-sm" placeholder="e.g., Attentive child, good progress" <?= $isSealed ? 'disabled' : '' ?>>
                                    </td>
                                    
                                </tr>
    <div class="card card-body border-0 shadow-sm rounded-3 mb-4 p-4">
        <h2 class="h4 fw-bold mb-1 text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Academic Assessment Grading Grid</h2>
        <p class="text-muted small mb-0">Select your active subject tier workflow below to manage terminal assessment grids.</p>
    </div>

    <?php 
    $dosFeedbackNote = '';
    if (!empty($activeAssignment) && !empty($selectedTerm)) {
        $dosFeedbackNote = Yii::$app->db->createCommand(
            "SELECT dos_feedback FROM academic_marks 
             WHERE school_id = :sid AND class_level = :cls AND subject_name = :sub AND term = :trm AND status = 'REJECTED_AMEND' AND dos_feedback IS NOT NULL 
             LIMIT 1"
        )->bindValues([
            ':sid' => Yii::$app->user->identity->school_id,
            ':cls' => $activeAssignment['class_level'],
            ':sub' => $activeAssignment['subject_name'],
            ':trm' => $selectedTerm
        ])->queryScalar();
    }

    if (!empty($dosFeedbackNote)): 
    ?>
        <div class="alert alert-danger border-start border-danger border-4 shadow-sm mb-4 rounded-3 p-3 bg-white">
            <h6 class="fw-bold text-danger mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Revision Required by Director of Studies</h6>
            <p class="mb-0 fs-6 text-dark bg-light p-2.5 rounded border  mt-2">
                "<?= Html::encode($dosFeedbackNote) ?>"
            </p>
        </div>
    <?php endif; ?>

    <!-- Assignments & Terms Filtering Selector Box -->
    <div class="card card-body border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">

                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-primary btn-lg fw-bold rounded-3 px-4 shadow-sm"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Submit Marks Sheet to D.O.S. Queue</button>
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
/**
 * Dynamic front-end calculator to display UNEB tier codes live as the teacher types scores
 */
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
    if (weightedScore >= 80) unnebletter = 'D1';
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
