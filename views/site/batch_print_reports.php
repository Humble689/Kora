<?php
/** @var yii\web\View $this */
/** @var app\models\Students[] $students */
/** @var array $batchGrades */
/** @var string $term */
/** @var int $year */
/** @var string $classLevel */

use yii\helpers\Html;

// 💡 HIGH-SPEED SINGLE-SWEEP UNEB CONVERSION MATRIX
function getBatchUnebGrade($bot, $mot, $eot, $hasBot, $hasMot, $hasEot) {
    $totalWeight = 0; 
    $earnedPoints = 0;
    
    if ($hasBot && $bot > 0) { $earnedPoints += ($bot * 0.2); $totalWeight += 0.2; }
    if ($hasMot && $mot > 0) { $earnedPoints += ($mot * 0.3); $totalWeight += 0.3; }
    if ($hasEot && $eot > 0) { $earnedPoints += ($eot * 0.5); $totalWeight += 0.5; }
    
    if ($totalWeight === 0) return ['G' => 'F9', 'P' => 9, 'R' => 'Fail 9'];
    $finalScore = ($earnedPoints / $totalWeight);
    
    if ($finalScore >= 80) return ['G' => 'D1', 'P' => 1, 'R' => 'Distinction 1'];
    if ($finalScore >= 75) return ['G' => 'D2', 'P' => 2, 'R' => 'Distinction 2'];
    if ($finalScore >= 70) return ['G' => 'C3', 'P' => 3, 'R' => 'Credit 3'];
    if ($finalScore >= 65) return ['G' => 'C4', 'P' => 4, 'R' => 'Credit 4'];
    if ($finalScore >= 60) return ['G' => 'C5', 'P' => 5, 'R' => 'Credit 5'];
    if ($finalScore >= 50) return ['G' => 'C6', 'P' => 6, 'R' => 'Credit 6'];
    if ($finalScore >= 45) return ['G' => 'P7', 'P' => 7, 'R' => 'Pass 7'];
    if ($finalScore >= 40) return ['G' => 'P8', 'P' => 8, 'R' => 'Pass 8'];
    return ['G' => 'F9', 'P' => 9, 'R' => 'Fail 9'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kora Continuous Spooler Run — <?= Html::encode($classLevel) ?></title>
    <link rel="stylesheet" href="https://jsdelivr.net">
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .page-break-container { background: #fff; max-width: 820px; padding: 40px; margin: 0 auto 30px auto; border-radius: 6px; box-shadow: 0 4px 8px rgba(0,0,0,0.02); position: relative; }
        .table-bordered th, .table-bordered td { border: 2px solid #000 !important; }
        
        /* 🖨️ STOPS PAGES FROM CUTTING MID-CARD */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .page-break-container { 
                page-break-after: always !important;
                box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; margin: 0 !important; 
            }
        }
    </style>
</head>
<body>

<div class="container text-center mb-4 no-print" style="max-width: 820px;">
    <div class="alert alert-info py-2 rounded-3 small mb-2">
        <i class="bi bi-info-circle-fill"></i> Spooler active. Compiled <strong><?= count($students) ?> Cleared Report Sheets</strong> sequentially.
    </div>
    <button onclick="window.print()" class="btn btn-primary fw-bold px-4 rounded-3 shadow-sm w-100 py-2">
        <i class="bi bi-printer-fill me-1"></i> Launch High-Volume Print Spooler (Ctrl + P)
    </button>
</div>

<?php 
foreach ($students as $student): 
    $gradesList = $batchGrades[$student->id] ?? [];
    
    // Check available columns in a clean single loop pass
    $hasBotData = false; 
    $hasMotData = false; 
    $hasEotData = false;
    foreach ($gradesList as $g) {
        if ((float)($g['bot_mark'] ?? 0) > 0) $hasBotData = true;
        if ((float)($g['mot_mark'] ?? 0) > 0) $hasMotData = true;
        if ((float)($g['eot_mark'] ?? 0) > 0) $hasEotData = true;
    }
    
    // Extract points array
    $allPoints = []; 
    $totalValidSubjects = 0; 
    $cumulativePoints = 0;
    
    foreach ($gradesList as $g) {
        $u = getBatchUnebGrade((float)$g['bot_mark'], (float)$g['mot_mark'], (float)$g['eot_mark'], $hasBotData, $hasMotData, $hasEotData);
        $allPoints[] = $u['P'];
        
        $rowAvg = (($g['bot_mark'] * 0.2) + ($g['mot_mark'] * 0.3) + ($g['eot_mark'] * 0.5));
        $cumulativePoints += ($rowAvg >= 75 ? 3.0 : ($rowAvg >= 50 ? 2.0 : 1.0));
        $totalValidSubjects++;
    }
    
    $summaryHeader = "Competency Score"; 
    $summaryValue = "0.00 / 3.0"; 
    $divisionText = "Incomplete";
    
    if ($totalValidSubjects > 0) {
        if (strpos($classLevel, 'Primary') !== false) {
            $agg = array_sum(array_slice($allPoints, 0, 4));
            $summaryHeader = "PLE Aggregate Summary"; 
            $summaryValue = $agg . " Pts";
            $divisionText = ($agg <= 12) ? "Division 1" : (($agg <= 24) ? "Division 2" : "Division 3");
        } else {
            $avg = round(($cumulativePoints / $totalValidSubjects), 2);
            $summaryHeader = "NLSC Average Grade"; 
            $summaryValue = number_format($avg, 2) . " / 3.0";
            $divisionText = ($avg >= 2.5) ? "Grade G (Good)" : (($avg >= 1.5) ? "Grade S (Satisfactory)" : "Grade B (Basic)");
        }
    }
?>
    <div class="page-break-container" style="font-family: 'Times New Roman', serif;">
        <!-- Institutional Header Section -->
        <div class="text-center border-bottom border-4 border-dark pb-2 mb-3">
            <h2 class="fw-bold text-dark mb-0 fs-3 text-uppercase"><?= $student->school ? Html::encode($student->school->name) : 'KORA EDUCATION CENTRE' ?></h2>
            <h4 class="fw-semibold text-secondary small fs-6 text-uppercase tracking-wider mb-1">Official Continuous Terminal Report Card</h4>
            <div class="badge bg-dark px-2.5 py-1 font-monospace text-xs"><?= Html::encode($term) ?> — YEAR <?= $year ?></div>
        </div>

        <!-- Student Biodata Block Grid -->
        <div class="row g-2 mb-3 border border-2 border-dark p-2 bg-light rounded mx-0 text-xs">
            <div class="col-7"><strong>Student Name:</strong> <span class="font-monospace fw-bold text-uppercase"><?= Html::encode($student->name) ?></span></div>
            <div class="col-5 text-end"><strong>Kora Billing Code:</strong> <span class="fw-bold font-monospace bg-white border border-dark px-1.5"><?= $student->payment_code ?></span></div>
            <div class="col-7"><strong>Class Level Stream:</strong> <span class="fw-semibold"><?= Html::encode($student->class_level) ?></span></div>
            <div class="col-5 text-end"><strong>Status Flag:</strong> <span class="text-success fw-bold uppercase">Cleared for Issue</span></div>
        </div>

        <!-- Aggregation Panel Ribbon -->
        <div class="row g-2 mb-3 mx-0 text-dark bg-white border border-2 border-dark rounded p-2 text-center align-items-center text-xs">
            <div class="col-6 border-end border-dark border-opacity-20 py-1">
                <span class="text-uppercase font-monospace text-muted d-block small" style="font-size: 0.65rem;"><?= $summaryHeader ?></span>
                <h5 class="font-monospace fw-bold mb-0 text-dark"><?= $summaryValue ?></h5>
            </div>
            <div class="col-6 py-1">
                <span class="text-uppercase font-monospace text-muted d-block small" style="font-size: 0.65rem;">National standard Class Standard</span>
                <h5 class="font-monospace fw-bold text-primary mb-0"><?= $divisionText ?></h5>
            </div>
        </div>

        <!-- Detailed Marks Registry Matrix Table -->
        <table class="table table-bordered border-dark text-dark align-middle table-sm" style="font-size: 0.8rem; border-width: 2px !important;">
            <thead class="table-secondary text-center font-monospace fw-bold">
                <tr>
                    <th>Subject Course Allocation Stream</th>
                    <?php if ($hasBotData): ?> <th>BOT (20%)</th> <?php endif; ?>
                    <?php if ($hasMotData): ?> <th>MOT (30%)</th> <?php endif; ?>
                    <?php if ($hasEotData): ?> <th>EOT (50%)</th> <?php endif; ?>
                    <th>Grade</th>
                    <th>Tutor Assessment Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gradesList as $g): 
                    $b = (float)$g['bot_mark']; 
                    $m = (float)$g['mot_mark']; 
                    $e = (float)$g['eot_mark'];
                    $uneb = getBatchUnebGrade($b, $m, $e, $hasBotData, $hasMotData, $hasEotData);
                ?>
                    <tr>
                        <td class="fw-bold ps-2"><?= Html::encode($g['subject_name']) ?></td>
                        <?php if ($hasBotData): ?> <td class="text-center font-monospace"><?= $b ?></td> <?php endif; ?>
                        <?php if ($hasMotData): ?> <td class="text-center font-monospace"><?= $m ?></td> <?php endif; ?>
                        <?php if ($hasEotData): ?> <td class="text-center font-monospace"><?= $e ?></td> <?php endif; ?>
                        <td class="text-center font-monospace fw-bold bg-light">
                            <?php 
                            if (strpos($classLevel, 'Primary') !== false) { 
                                echo $uneb['G']; 
                            } else {
                                $rAvg = (($b * 0.2) + ($m * 0.3) + ($e * 0.5));
                                echo ($rAvg >= 75) ? "3.0" : (($rAvg >= 50) ? "2.0" : "1.0");
                            }
                            ?>
                        </td>
                        <td class="ps-2 text-muted italic small"><?= Html::encode($g['teacher_comment'] ?: $uneb['R']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Signatures Matrix Footer Footer -->
        <div class="row pt-4 text-center fs-7 text-xs" style="margin-top: 50px !important;">
            <div class="col-6 border-top border-dark pt-1" style="border-top-style: dashed !important;"><strong>Classroom Tutor Signature</strong></div>

        </div>
    </div>
<?php endforeach; ?>

</body>
</html>
