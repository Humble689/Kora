<?php
/** @var yii\web\View $this */
/** @var app\models\Students $student */
/** @var array $gradesList */
/** @var string $term */
/** @var int $year */

use yii\helpers\Html;

$this->title = 'Academic Report — ' . $student->name;

$hasBotData = false;
$hasMotData = false;
$hasEotData = false;

foreach ($gradesList as $g) {
    if ((float)$g['bot_mark'] > 0) $hasBotData = true;
    if ((float)$g['mot_mark'] > 0) $hasMotData = true;
    if ((float)$g['eot_mark'] > 0) $hasEotData = true;
}

function calculateReportUneb($bot, $mot, $eot, $hasBot, $hasMot, $hasEot) {
    $totalWeight = 0;
    $earnedPoints = 0;

    if ($hasBot && $bot > 0) { $earnedPoints += ($bot * 0.2); $totalWeight += 0.2; }
    if ($hasMot && $mot > 0) { $earnedPoints += ($mot * 0.3); $totalWeight += 0.3; }
    if ($hasEot && $eot > 0) { $earnedPoints += ($eot * 0.5); $totalWeight += 0.5; }

    if ($totalWeight === 0) return ['G' => 'Incomplete', 'R' => 'No assessment data recorded.'];

    $finalScore = ($earnedPoints / $totalWeight);
    
    if ($finalScore >= 80) return ['G' => 'D1', 'R' => 'Excellent performance'];
    if ($finalScore >= 75) return ['G' => 'D2', 'R' => 'Very good progress'];
    if ($finalScore >= 70) return ['G' => 'C3', 'R' => 'Good tracking'];
    if ($finalScore >= 65) return ['G' => 'C4', 'R' => 'Clear competence'];
    if ($finalScore >= 60) return ['G' => 'C5', 'R' => 'Fairly good results'];
    if ($finalScore >= 50) return ['G' => 'C6', 'R' => 'Pass achieved'];
    if ($finalScore >= 45) return ['G' => 'P7', 'R' => 'Modest pass achieved'];
    if ($finalScore >= 40) return ['G' => 'P8', 'R' => 'Weak pass results'];
    return ['G' => 'F9', 'R' => 'Remedial attention needed'];
}

$isLocked = (float)$student->tuition_balance > 0;
?>

<div class="site-view-report-card bg-white p-4 mx-auto rounded shadow-sm position-relative my-4" style="max-width: 800px; min-height: 1050px; font-family: 'Times New Roman', serif;">
    
    <?php if ($isLocked): ?>
        <div class="financial-lock-watermark-overlay">
            <div class="watermark-text-angle">FEES OWING — REPORT WITHHELD</div>
            <div class="watermark-sub-notice alert alert-danger border-0 shadow">
                <i class="bi bi-shield-lock-fill me-2 fs-4"></i>
                <strong>Access Restricted Block:</strong> This student's official academic report card has been automatically withheld by the system because they have an outstanding tuition balance of <strong>UGX <?= number_format((float)$student->tuition_balance, 0) ?></strong>. Clear the balance to unlock printing clearance variables.
            </div>
        </div>
    <?php endif; ?>

    
    <div class="text-center border-bottom border-3 border-dark pb-3 mb-4">
        <h2 class="fw-bold tracking-uppercase text-dark mb-1 m-0 fs-3"><?= $student->school ? Html::encode($student->school->name) : 'MINISTRY OF EDUCATION' ?></h2>
        <h3 class="fw-semibold text-secondary small fs-6 uppercase tracking-wider mb-2">Official Terminal Report Assessment Sheet</h3>
        <div class="badge bg-dark px-3 py-1.5 text-sm"><?= Html::encode($term) ?> — ACADEMIC YEAR <?= $year ?></div>
    </div>

    <div class="row g-2 mb-4 border p-3 bg-light rounded rounded-2 fs-6">
        <div class="col-7"><strong>Student Full Name:</strong> <span class="text-dark "><?= Html::encode($student->name) ?></span></div>
        <div class="col-5 text-end"><strong>SchoolPay Code:</strong> <span class="fw-bold bg-white border px-2 py-0.5 rounded"><?= $student->payment_code ?></span></div>
        <div class="col-7"><strong>Classroom Stream Assignment:</strong> <span class="text-dark fw-semibold"><?= Html::encode($student->class_level) ?></span></div>
        <div class="col-5 text-end"><strong>System Status:</strong> <span class="text-success fw-bold uppercase">Enrolled</span></div>
    </div>

    <table class="table table-bordered border-dark text-dark align-middle fs-6" style="border-width: 2px !important;">
        <thead class="table-secondary text-center text-uppercase fw-bold">
            <tr>
                <th style="width: 35%;">Subject Course</th>
                
                <?php if ($hasBotData): ?> <th style="width: 12%;">BOT (20)</th> <?php endif; ?>
                <?php if ($hasMotData): ?> <th style="width: 12%;">MOT (30)</th> <?php endif; ?>
                <?php if ($hasEotData): ?> <th style="width: 12%;">EOT (50)</th> <?php endif; ?>
                
                <th style="width: 12%;">Grade</th>
                <th style="width: 25%;">Assigned Assessment Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($gradesList)): ?>
                <tr>
                    <td colspan="<?= 2 + ($hasBotData?1:0) + ($hasMotData?1:0) + ($hasEotData?1:0) ?>" class="text-center py-5 text-muted italic">
                        No academic examination scores have been recorded or finalized for this term cycle yet.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($gradesList as $g): 
                    $bot = (float)$g['bot_mark'];
                    $mot = (float)$g['mot_mark'];
                    $eot = (float)$g['eot_mark'];
                    $uneb = calculateReportUneb($bot, $mot, $eot, $hasBotData, $hasMotData, $hasEotData);
                ?>
                    <tr>
                        <td class="fw-bold ps-2"><?= Html::encode($g['subject_name']) ?></td>
                        
                        <?php if ($hasBotData): ?> <td class="text-center"><?= $bot ?></td> <?php endif; ?>
                        <?php if ($hasMotData): ?> <td class="text-center"><?= $mot ?></td> <?php endif; ?>
                        <?php if ($hasEotData): ?> <td class="text-center"><?= $eot ?></td> <?php endif; ?>
                        
                        <td class="text-center fw-bold fs-5 bg-light"><?= $uneb['G'] ?></td>
                        <td class="ps-2 text-muted small italic"><?= Html::encode($g['teacher_comment'] ?: $uneb['R']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row pt-5 mt-5 fs-6" style="margin-top: 150px !important;">
        <div class="col-6 text-center border-top pt-2" style="border-top: 1px dashed #212529 !important;">
            <div class="fw-bold text-dark mb-0">Classroom Tutor Signature</div>
            <small class="text-muted text-xs">Verification Check Completed</small>
        </div>
        <div class="col-6 text-center border-top pt-2" style="border-top: 1px dashed #212529 !important;">
            <div class="fw-bold text-dark mb-0">Director of Studies (D.O.S.) Stamp</div>
            <small class="text-muted text-xs">Official Seal Authorized Matrix</small>
        </div>
    </div>
</div>

<style>
.financial-lock-watermark-overlay {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(255, 255, 255, 0.94); z-index: 9999;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    border-radius: 8px; user-select: none; pointer-events: all; padding: 2rem; box-sizing: border-box;
}
.watermark-text-angle {
    position: absolute; font-size: 3.5rem; font-weight: 900;
    color: rgba(220, 53, 69, 0.15); transform: rotate(-30deg);
    white-space: nowrap; text-transform: uppercase; font-family: sans-serif;
    letter-spacing: 0.1em; pointer-events: none;
}
.watermark-sub-notice { max-width: 500px; width: 100%; text-align: center; position: relative; z-index: 10000; font-family: sans-serif; }
@media print { .financial-lock-watermark-overlay { -webkit-print-color-adjust: exact; print-color-adjust: exact; } body { background: #fff !important; } }
</style>
