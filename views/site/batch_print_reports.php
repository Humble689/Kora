<?php
/** @var yii\web\View $this */
/** @var app\models\Students[] $students */
/** @var array $batchGrades */
/** @var string $term */
/** @var int $year */
/** @var string $classLevel */
/** @var array $classPositionMap  student_id => position, Primary only */
/** @var int|null $classSize      Primary only */

use yii\helpers\Html;

$classPositionMap = $classPositionMap ?? [];
$classSize = $classSize ?? null;

// Same grading engine as the single report card view (view-report-card.php) —
// kept in sync so batch-printed cards and individually-viewed cards never
// disagree on a grade or division.
function getBatchUnebGrade($bot, $mot, $eot, $hasBot, $hasMot, $hasEot) {
    $totalWeight = 0;
    $earnedPoints = 0;

    if ($hasBot && $bot > 0) { $earnedPoints += ($bot * 0.2); $totalWeight += 0.2; }
    if ($hasMot && $mot > 0) { $earnedPoints += ($mot * 0.3); $totalWeight += 0.3; }
    if ($hasEot && $eot > 0) { $earnedPoints += ($eot * 0.5); $totalWeight += 0.5; }

    if ($totalWeight === 0) return ['G' => 'F9', 'P' => 9, 'R' => 'Fail 9', 'S' => 0.0];

    $finalScore = ($earnedPoints / $totalWeight);

    if ($finalScore >= 80) return ['G' => 'D1', 'P' => 1, 'R' => 'Distinction 1', 'S' => $finalScore];
    if ($finalScore >= 75) return ['G' => 'D2', 'P' => 2, 'R' => 'Distinction 2', 'S' => $finalScore];
    if ($finalScore >= 70) return ['G' => 'C3', 'P' => 3, 'R' => 'Credit 3', 'S' => $finalScore];
    if ($finalScore >= 65) return ['G' => 'C4', 'P' => 4, 'R' => 'Credit 4', 'S' => $finalScore];
    if ($finalScore >= 60) return ['G' => 'C5', 'P' => 5, 'R' => 'Credit 5', 'S' => $finalScore];
    if ($finalScore >= 50) return ['G' => 'C6', 'P' => 6, 'R' => 'Credit 6', 'S' => $finalScore];
    if ($finalScore >= 45) return ['G' => 'P7', 'P' => 7, 'R' => 'Pass 7', 'S' => $finalScore];
    if ($finalScore >= 40) return ['G' => 'P8', 'P' => 8, 'R' => 'Pass 8', 'S' => $finalScore];
    return ['G' => 'F9', 'P' => 9, 'R' => 'Fail 9', 'S' => $finalScore];
}

$subsidiarySubjects = ['General Paper', 'ICT', 'Sub-Math'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kora Continuous Spooler Run — <?= Html::encode($classLevel) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .page-break-container { background: #fff; max-width: 820px; padding: 40px; margin: 0 auto 30px auto; border-radius: 6px; box-shadow: 0 4px 8px rgba(0,0,0,0.02); position: relative; }
        .table-bordered th, .table-bordered td { border: 2px solid #000 !important; }

        .report-key-block { font-family: 'Times New Roman', serif; }
        .report-key-heading {
            font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; text-decoration: underline; text-underline-offset: 3px;
            margin-bottom: 0.6rem; color: #212529;
        }
        .report-key-table { font-size: 0.76rem; }
        .report-key-table th, .report-key-table td { padding: 0.3rem 0.35rem; vertical-align: middle; }
        .report-key-table thead th { font-weight: 700; background-color: #f1f1f1 !important; }
        .report-key-table tbody th { font-weight: 700; text-align: left; white-space: nowrap; }
        .report-key-note { font-size: 0.74rem; color: #333; line-height: 1.45; }
        .report-key-note em { color: #555; }

        /* 🖨️ STOPS PAGES FROM CUTTING MID-CARD */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .page-break-container {
                page-break-after: always !important;
                box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; margin: 0 !important;
            }
            .report-key-block { break-inside: avoid; }
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

    $hasBotData = false;
    $hasMotData = false;
    $hasEotData = false;
    foreach ($gradesList as $g) {
        if ((float)($g['bot_mark'] ?? 0) > 0) $hasBotData = true;
        if ((float)($g['mot_mark'] ?? 0) > 0) $hasMotData = true;
        if ((float)($g['eot_mark'] ?? 0) > 0) $hasEotData = true;
    }

    $activeTermCount = ($hasBotData ? 1 : 0) + ($hasMotData ? 1 : 0) + ($hasEotData ? 1 : 0);
    $botColumnLabel = $activeTermCount > 1 ? 'BOT (20)' : 'BOT';
    $motColumnLabel = $activeTermCount > 1 ? 'MOT (30)' : 'MOT';
    $eotColumnLabel = $activeTermCount > 1 ? 'EOT (50)' : 'EOT';

    $allPointsArray = [];
    $allRawScoresArray = [];
    $aLevelCorePoints = 0;
    $subsidiaryPoints = 0;
    $failsCoreSubject = false;

    foreach ($gradesList as $g) {
        $u = getBatchUnebGrade((float)$g['bot_mark'], (float)$g['mot_mark'], (float)$g['eot_mark'], $hasBotData, $hasMotData, $hasEotData);
        $allPointsArray[] = $u['P'];
        $allRawScoresArray[] = $u['S'];

        if (in_array($g['subject_name'], ['English', 'Mathematics']) && $u['G'] === 'F9') {
            $failsCoreSubject = true;
        }

        if (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false) {
            if (in_array($g['subject_name'], $subsidiarySubjects)) {
                if ($u['P'] <= 6) $subsidiaryPoints += 1;
            } else {
                if ($u['G'] === 'D1' || $u['G'] === 'D2') { $aLevelCorePoints += 6; }
                elseif ($u['G'] === 'C3') { $aLevelCorePoints += 5; }
                elseif ($u['G'] === 'C4') { $aLevelCorePoints += 4; }
                elseif ($u['G'] === 'C5') { $aLevelCorePoints += 3; }
                elseif ($u['G'] === 'C6') { $aLevelCorePoints += 2; }
                elseif ($u['G'] === 'P7' || $u['G'] === 'P8') { $aLevelCorePoints += 1; }
            }
        }
    }

    $summaryHeader = "Overall Performance Summary";
    $summaryValue = "Incomplete Records";
    $divisionText = "N/A";
    $secondSummaryLabel = "Awarding Classification Award";
    $promotionNote = null;

    if (!empty($allPointsArray)) {

        if (strpos($classLevel, 'Primary') !== false) {
            $agg = array_sum(array_slice($allPointsArray, 0, 4));
            $summaryHeader = "Total Aggregates";
            $summaryValue = $agg . " Pts";

            if ($agg <= 12) $divisionText = "Division I";
            elseif ($agg <= 23) $divisionText = "Division II";
            elseif ($agg <= 29) $divisionText = "Division III";
            elseif ($agg <= 34) $divisionText = "Division IV";
            else $divisionText = "Division U (Ungraded)";
        }

        elseif (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false) {
            $totalPoints = min(20, ($aLevelCorePoints + min(2, $subsidiaryPoints)));
            $summaryHeader = "A-Level National Weight Points";
            $summaryValue = $totalPoints . " / 20 Pts";

            if ($totalPoints >= 15) $divisionText = "Principal Class I";
            elseif ($totalPoints >= 10) $divisionText = "Principal Class II";
            else $divisionText = "Class III Certificate";
        }

        elseif (strpos($classLevel, 'Senior 1') !== false || strpos($classLevel, 'Senior 2') !== false) {
            $averageScore = array_sum($allRawScoresArray) / count($allRawScoresArray);
            $avgGrade = getBatchUnebGrade($averageScore, 0, 0, true, false, false);

            $summaryHeader = "Overall Average";
            $summaryValue = number_format($averageScore, 1) . "%";
            $secondSummaryLabel = "Average Grade";
            $divisionText = $avgGrade['G'] . ' - ' . $avgGrade['R'];
        }

        else {
            $sortedPoints = $allPointsArray;
            sort($sortedPoints, SORT_NUMERIC);
            $best8Points = array_slice($sortedPoints, 0, 8);
            $agg = array_sum($best8Points);
            if (count($best8Points) < 8) {
                $agg += (8 - count($best8Points)) * 9;
            }

            $summaryHeader = "O-Level UCE Best 8 Aggregate";
            $summaryValue = "Agg " . $agg;

            if ($agg <= 32) $divisionText = "Division I";
            elseif ($agg <= 45) $divisionText = "Division II";
            elseif ($agg <= 58) $divisionText = "Division III";
            elseif ($agg <= 72) $divisionText = "Division IV";
            else $divisionText = "Division U (Ungraded)";

            if ($failsCoreSubject) {
                $promotionNote = "Automatically promoted to the next class per school policy, despite a failing grade in a core subject (English/Mathematics).";
            }
        }
    }

    $totalMarksOutOf400 = null;
    if (strpos($classLevel, 'Primary') !== false && !empty($allRawScoresArray)) {
        $totalMarksOutOf400 = array_sum(array_slice($allRawScoresArray, 0, 4));
    }
    $classPosition = $classPositionMap[$student->id] ?? null;
?>
    <div class="page-break-container" style="font-family: 'Times New Roman', serif;">
        <!-- Institutional Header Section -->
        <div class="text-center border-bottom border-4 border-dark pb-2 mb-3">
            <h2 class="fw-bold text-dark mb-0 fs-3 text-uppercase"><?= $student->school ? Html::encode($student->school->name) : 'KORA EDUCATION CENTRE' ?></h2>
            <h4 class="fw-semibold text-secondary small fs-6 text-uppercase tracking-wider mb-1">Official Continuous Terminal Report Card</h4>
            <div class="badge bg-dark px-2.5 py-1 font-monospace text-xs"><?= Html::encode($term) ?> — YEAR <?= $year ?></div>
        </div>

        <!-- Student Biodata Block Grid -->
         <div class="d-flex align-items-stretch gap-3 mb-4 border p-3 bg-light rounded rounded-2 fs-6">
        <?php if (!empty($student->profile_photo)): ?>
            <img src="<?= $student->profile_photo ?>"
                 alt="<?= Html::encode($student->name) ?>"
                 class="border border-dark rounded flex-shrink-0"
                 style="width:96px;height:96px;object-fit:cover;">
        <?php else: ?>
            <div class="border border-dark rounded bg-white d-flex align-items-center justify-content-center text-muted flex-shrink-0"
                 style="width:96px;height:96px;">
                <i class="bi bi-person-fill" style="font-size:2.5rem;"></i>
            </div>
            <?php endif; ?>
            <div class="row g-2 flex-grow-1 align-content-start mx-0">
                <div class="col-7"><strong>Student Name:</strong> <span class="font-monospace fw-bold text-uppercase"><?= Html::encode($student->name) ?></span></div>
                <div class="col-5 text-end"><strong>Kora Billing Code:</strong> <span class="fw-bold font-monospace bg-white border border-dark px-1.5"><?= $student->payment_code ?></span></div>
                <div class="col-7"><strong>Class Level Stream:</strong> <span class="fw-semibold"><?= Html::encode($student->class_level) ?></span></div>
                <div class="col-5 text-end"><strong>Status Flag:</strong> <span class="text-success fw-bold uppercase">Cleared for Issue</span></div>
                <?php if (!empty($student->sex)): ?>
                    <div class="col-7"><strong>Sex:</strong> <span><?= strtoupper($student->sex) === 'FEMALE' ? 'Female' : 'Male' ?></span></div>
                <?php endif; ?>
                <?php if (strpos($classLevel, 'Primary') !== false && $classPosition !== null): ?>
                    <div class="col-5 text"><strong>Class Position:</strong> <span class="fw-bold text-primary"><?= (int)$classPosition ?>/<?= (int)$classSize ?></span></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detailed Marks Registry Matrix Table -->
        <table class="table table-bordered border-dark text-dark align-middle table-sm" style="font-size: 0.8rem; border-width: 2px !important;">
            <thead class="table-secondary text-center font-monospace fw-bold">
                <tr>
                    <th>Subject Course Allocation Stream</th>
                    <?php if ($hasBotData): ?> <th><?= $botColumnLabel ?></th> <?php endif; ?>
                    <?php if ($hasMotData): ?> <th><?= $motColumnLabel ?></th> <?php endif; ?>
                    <?php if ($hasEotData): ?> <th><?= $eotColumnLabel ?></th> <?php endif; ?>
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
                        <td class="text-center font-monospace fw-bold bg-light"><?= $uneb['G'] ?></td>
                        <td class="ps-2 text-muted italic small"><?= Html::encode($g['teacher_comment'] ?: $uneb['R']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (strpos($classLevel, 'Primary') !== false && $totalMarksOutOf400 !== null): ?>
            <div class="row g-2 mb-3 mx-0 text-dark bg-light border rounded p-2 text-center align-items-center text-xs">
                <div class="col-12">
                    <span class="text-uppercase small" style="font-size: 0.65rem;">Total Marks</span>
                    <span class="fw-bold ms-1"><?= number_format($totalMarksOutOf400, 0) ?> / 400</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Aggregation Panel Ribbon -->
        <div class="row g-2 mb-3 mx-0 text-dark bg-white border border-2 border-dark rounded p-2 text-center align-items-center text-xs">
            <div class="col-6 border-end border-dark border-opacity-20 py-1">
                <span class="text-uppercase font-monospace text-muted d-block small" style="font-size: 0.65rem;"><?= $summaryHeader ?></span>
                <h5 class="font-monospace fw-bold mb-0 text-dark"><?= $summaryValue ?></h5>
            </div>
            <div class="col-6 py-1">
                <span class="text-uppercase font-monospace text-muted d-block small" style="font-size: 0.65rem;"><?= $secondSummaryLabel ?></span>
                <h5 class="font-monospace fw-bold text-primary mb-0"><?= $divisionText ?></h5>
            </div>
        </div>

        <?php if ($promotionNote): ?>
            <div class="alert alert-warning border-0 py-1 px-2 mb-3" style="font-size: 0.72rem;">
                <i class="bi bi-info-circle-fill me-1"></i> <?= Html::encode($promotionNote) ?>
            </div>
        <?php endif; ?>

        <!-- Signatures Matrix Footer -->
        <div class="row pt-4 text-center fs-7 text-xs" style="margin-top: 50px !important;">
            <div class="col-6 border-top border-dark pt-1" style="border-top-style: dashed !important;"><strong>Classroom Tutor Signature</strong></div>
            <div class="col-6 border-top border-dark pt-1" style="border-top-style: dashed !important;"><strong>Director of Studies (D.O.S.) Stamp</strong></div>
        </div>

        <!-- Grading Key -->
        <div class="mt-4 pt-3 border-top border-dark border-opacity-25 report-key-block">
            <?php if (strpos($classLevel, 'Primary') !== false): ?>
                <div class="border border-dark p-2">
                    <h6 class="report-key-heading">Key To Grading</h6>
                    <table class="table table-bordered border-dark text-center mb-2 report-key-table">
                        <thead>
                            <tr class="table-light"><th>Grade</th><th>D1</th><th>D2</th><th>C3</th><th>C4</th><th>C5</th><th>C6</th><th>P7</th><th>P8</th><th>F9</th></tr>
                        </thead>
                        <tbody>
                            <tr><th class="table-light">Score Range</th><td>80-100</td><td>75-79</td><td>70-74</td><td>65-69</td><td>60-64</td><td>50-59</td><td>45-49</td><td>40-44</td><td>00-39</td></tr>
                        </tbody>
                    </table>
                    <p class="report-key-note mb-0">
                        <strong>Awarding Aggregates:</strong> Division I 4&ndash;12 &middot; Division II 13&ndash;23 &middot; Division III 24&ndash;29 &middot; Division IV 30&ndash;34 &middot; Division U 35+ (Ungraded).
                    </p>
                </div>
            <?php elseif (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false): ?>
                <div class="border border-dark p-2">
                    <h6 class="report-key-heading">Official UACE A-Level Principal Points Key</h6>
                    <table class="table table-bordered border-dark text-center mb-2 report-key-table">
                        <thead>
                            <tr class="table-light"><th>Grade</th><th>D1 / D2</th><th>C3</th><th>C4</th><th>C5</th><th>C6</th><th>P7 / P8</th></tr>
                        </thead>
                        <tbody>
                            <tr><th class="table-light">Letter</th><td>A</td><td>B</td><td>C</td><td>D</td><td>E</td><td>O</td></tr>
                            <tr><th class="table-light">Points</th><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td></tr>
                        </tbody>
                    </table>
                    <p class="report-key-note mb-0">
                        <strong>UACE Rules:</strong> Core Principal max = 18 pts. Subsidiary pass (General Paper / ICT / Sub-Math, C6+) = 1 pt each, capped at 2. <em>Max scale = 20 pts.</em>
                    </p>
                </div>
            <?php elseif (strpos($classLevel, 'Senior 1') !== false || strpos($classLevel, 'Senior 2') !== false): ?>
                <div class="border border-dark p-2">
                    <h6 class="report-key-heading">Key To Grading</h6>
                    <table class="table table-bordered border-dark text-center mb-2 report-key-table">
                        <thead>
                            <tr class="table-light"><th>Grade</th><th>D1</th><th>D2</th><th>C3</th><th>C4</th><th>C5</th><th>C6</th><th>P7</th><th>P8</th><th>F9</th></tr>
                        </thead>
                        <tbody>
                            <tr><th class="table-light">Score Range</th><td>80-100</td><td>75-79</td><td>70-74</td><td>65-69</td><td>60-64</td><td>50-59</td><td>45-49</td><td>40-44</td><td>00-39</td></tr>
                        </tbody>
                    </table>
                    <p class="report-key-note mb-0">Lower Secondary uses the <strong>overall average</strong> across all subjects, not an aggregate.</p>
                </div>
            <?php else: ?>
                <div class="border border-dark p-2">
                    <h6 class="report-key-heading">Official UCE O-Level Best 8 Grading Key</h6>
                    <table class="table table-bordered border-dark text-center mb-2 report-key-table">
                        <thead>
                            <tr class="table-light"><th>Grade</th><th>D1</th><th>D2</th><th>C3</th><th>C4</th><th>C5</th><th>C6</th><th>P7</th><th>P8</th><th>F9</th></tr>
                        </thead>
                        <tbody>
                            <tr><th class="table-light">Score Range</th><td>80-100</td><td>75-79</td><td>70-74</td><td>65-69</td><td>60-64</td><td>50-59</td><td>45-49</td><td>40-44</td><td>00-39</td></tr>
                        </tbody>
                    </table>
                    <p class="report-key-note mb-0">
                        <strong>Awarding Aggregates:</strong> Division I 8&ndash;32 &middot; Division II 33&ndash;45 &middot; Division III 46&ndash;58 &middot; Division IV 59&ndash;72 &middot; Division U 73+ (Ungraded). Best 8 of 10 subjects.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

</body>
</html>