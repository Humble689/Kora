<?php
/** @var yii\web\View $this */
/** @var app\models\Students $student */
/** @var array $gradesList */
/** @var string $term */
/** @var int $year */
/** @var int|null $classPosition   Rank within class (Primary only) - requires controller wiring, see note below */
/** @var int|null $classSize       Number of students ranked (Primary only) - requires controller wiring */

use yii\helpers\Html;

$this->title = 'Academic Report ' . $student->name;

// These two are optional and only meaningful for Primary. They won't exist
// yet unless the controller action is updated to compute and pass them -
// see computePrimaryClassRanking() note at the end of this response.
$classPosition = $classPosition ?? null;
$classSize = $classSize ?? null;

$hasBotData = false;
$hasMotData = false;
$hasEotData = false;

foreach ($gradesList as $g) {
    if ((float)$g['bot_mark'] > 0) $hasBotData = true;
    if ((float)$g['mot_mark'] > 0) $hasMotData = true;
    if ((float)$g['eot_mark'] > 0) $hasEotData = true;
}

// Only show the weight suffix ("(20)") when more than one term's marks are
// present - a single-term sheet doesn't need the weighting spelled out.
$activeTermCount = ($hasBotData ? 1 : 0) + ($hasMotData ? 1 : 0) + ($hasEotData ? 1 : 0);
$botColumnLabel = $activeTermCount > 1 ? 'BOT (20)' : 'BOT';
$motColumnLabel = $activeTermCount > 1 ? 'MOT (30)' : 'MOT';
$eotColumnLabel = $activeTermCount > 1 ? 'EOT (50)' : 'EOT';

function getUnebDetails($bot, $mot, $eot, $hasBot, $hasMot, $hasEot) {
    $totalWeight = 0;
    $earnedPoints = 0;

    if ($hasBot && $bot > 0) { $earnedPoints += ($bot * 0.2); $totalWeight += 0.2; }
    if ($hasMot && $mot > 0) { $earnedPoints += ($mot * 0.3); $totalWeight += 0.3; }
    if ($hasEot && $eot > 0) { $earnedPoints += ($eot * 0.5); $totalWeight += 0.5; }

    if ($totalWeight === 0) return ['G' => 'F9', 'P' => 9, 'R' => 'Fail', 'S' => 0.0];

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

$classLevel = $student->class_level;
$summaryHeaderLabel = "Overall Performance Summary";
$summaryValueBlock = "Incomplete Records";
$divisionLabel = "N/A";
$secondSummaryLabel = "Awarding Classification Award";
$promotionNote = null;

// A-Level subsidiary subjects: General Paper, ICT, and Sub-Math each earn
// 1 point if passed at C6 (50 marks) or better - capped at 2 points total.
$subsidiarySubjects = ['General Paper', 'ICT', 'Sub-Math'];

$allPointsArray = [];
$allRawScoresArray = [];
$aLevelCorePoints = 0;
$subsidiaryPoints = 0;
$failsCoreSubject = false;

foreach ($gradesList as $g) {
    $u = getUnebDetails((float)$g['bot_mark'], (float)$g['mot_mark'], (float)$g['eot_mark'], $hasBotData, $hasMotData, $hasEotData);
    $allPointsArray[] = $u['P'];
    $allRawScoresArray[] = $u['S'];

    // S3-S4: Ministry policy auto-promotes students to the next class even
    // if they fail English or Mathematics - flagged as a note, not used to
    // change the computed aggregate/division itself.
    if (in_array($g['subject_name'], ['English', 'Mathematics']) && $u['G'] === 'F9') {
        $failsCoreSubject = true;
    }

    // Core parameters grouping calculation for A-level strings
    if (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false) {
        if (in_array($g['subject_name'], $subsidiarySubjects)) {
            // Pass threshold is C6 or better (score >= 50) - max 1 point each
            if ($u['P'] <= 6) $subsidiaryPoints += 1;
        } else {
            // Convert standard UNEB grades to A-Level Points values
            if ($u['G'] === 'D1' || $u['G'] === 'D2') {
                $aLevelCorePoints += 6; // Grade A
            } elseif ($u['G'] === 'C3') {
                $aLevelCorePoints += 5; // Grade B
            } elseif ($u['G'] === 'C4') {
                $aLevelCorePoints += 4; // Grade C
            } elseif ($u['G'] === 'C5') {
                $aLevelCorePoints += 3; // Grade D
            } elseif ($u['G'] === 'C6') {
                $aLevelCorePoints += 2; // Grade E
            } elseif ($u['G'] === 'P7' || $u['G'] === 'P8') {
                $aLevelCorePoints += 1; // Grade O
            }
        }
    }
}

// execute LEVEL SPECIFIC COMPILES
if (!empty($allPointsArray)) {

    if (strpos($classLevel, 'Primary') !== false) {
        // PLE-style: aggregate of the first 4 core subjects' grade points.
        $aggregate = array_sum(array_slice($allPointsArray, 0, 4));
        $summaryHeaderLabel = "Total Aggregates";
        $summaryValueBlock = $aggregate . " ";

        if ($aggregate <= 12) $divisionLabel = "Division I";
        elseif ($aggregate <= 23) $divisionLabel = "Division II";
        elseif ($aggregate <= 29) $divisionLabel = "Division III";
        elseif ($aggregate <= 34) $divisionLabel = "Division IV";
        else $divisionLabel = "Division U (Ungraded)";
    }

    elseif (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false) {
        // A-Level Rule: Core Combination Points + Subsidiaries (Max 20 Points Scale)
        $totalPoints = min(20, ($aLevelCorePoints + min(2, $subsidiaryPoints)));
        $summaryHeaderLabel = "A-Level National Weight Points";
        $summaryValueBlock = $totalPoints . " / 20 Points";

        if ($totalPoints >= 15) $divisionLabel = "Principal Class I";
        elseif ($totalPoints >= 10) $divisionLabel = "Principal Class II";
        else $divisionLabel = "Class III Certificate";
    }

    elseif (strpos($classLevel, 'Senior 1') !== false || strpos($classLevel, 'Senior 2') !== false) {
        // S1-S2: overall average score across all subjects, not an aggregate.
        $averageScore = array_sum($allRawScoresArray) / count($allRawScoresArray);
        $avgGrade = getUnebDetails($averageScore, 0, 0, true, false, false);

        $summaryHeaderLabel = "Overall Average";
        $summaryValueBlock = number_format($averageScore, 1) . "%";
        $secondSummaryLabel = "Average Grade";
        $divisionLabel = $avgGrade['G'] . ' - ' . $avgGrade['R'];
    }

    else {
        // S3-S4: O-Level Rule: best 8 of the 10 subjects taken, dynamically.
        sort($allPointsArray, SORT_NUMERIC);
        $best8Points = array_slice($allPointsArray, 0, 8);
        $aggregate = array_sum($best8Points);

        // Pad aggregates if student takes fewer than 8 total subjects courses
        if (count($best8Points) < 8) {
            $aggregate += (8 - count($best8Points)) * 9;
        }

        $summaryHeaderLabel = "O-Level UCE Best 8 Aggregate";
        $summaryValueBlock = "Agg " . $aggregate;

        if ($aggregate <= 32) $divisionLabel = "Division I";
        elseif ($aggregate <= 45) $divisionLabel = "Division II";
        elseif ($aggregate <= 58) $divisionLabel = "Division III";
        elseif ($aggregate <= 72) $divisionLabel = "Division IV";
        else $divisionLabel = "Division U (Ungraded)";

        if ($failsCoreSubject) {
            $promotionNote = "Automatically promoted to the next class per school policy, despite a failing grade in a core subject (English/Mathematics).";
        }
    }
}

// Primary-only: total raw marks across the first 4 core subjects, out of 400.
$totalMarksOutOf400 = null;
if (strpos($classLevel, 'Primary') !== false && !empty($allRawScoresArray)) {
    $totalMarksOutOf400 = array_sum(array_slice($allRawScoresArray, 0, 4));
}

$isLocked = (float)$student->tuition_balance > 0;
?>


<div class="site-view-report-card bg-white p-4 mx-auto rounded shadow-sm position-relative my-4" style="max-width: 800px; min-height: 1050px; font-family: 'Times New Roman', serif;">

    <?php if ($isLocked): ?>
        <div class="financial-lock-watermark-overlay">
            <div class="watermark-text-angle">FEES OWING  REPORT WITHHELD</div>
            <div class="watermark-sub-notice alert alert-danger border-0 shadow">
                <i class="bi bi-shield-lock-fill me-2 fs-4"></i>
                <strong>Access Restricted Block:</strong> This student's official academic report card has been automatically withheld by the system because they have an outstanding tuition balance of <strong>UGX <?= number_format((float)$student->tuition_balance, 0) ?></strong>. Clear the balance to unlock printing clearance variables.
            </div>
        </div>
    <?php endif; ?>


    <div class="text-center border-bottom border-3 border-dark pb-3 mb-4">
        <h2 class="fw-bold tracking-uppercase text-dark mb-1 m-0 fs-3"><?= $student->school ? Html::encode($student->school->name) : 'MINISTRY OF EDUCATION' ?></h2>
        <h3 class="fw-semibold text small fs-6 uppercase tracking-wider mb-2">Official Terminal Report Assessment Sheet</h3>
        <div class=" bg-white px-3 py-1.5 text-sm"><?= Html::encode($term) ?> ACADEMIC YEAR <?= $year ?></div>
    </div>

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

        <div class="row g-2 flex-grow-1 align-content-start">
            <div class="col-7"><strong>Student Full Name:</strong> <span class="text-dark "><?= Html::encode(ucfirst($student->name)) ?></span></div>
            <div class="col-5 text-end"><strong>SchoolPay Code:</strong> <span class="fw-bold bg-white border px-2 py-0.5 rounded"><?= $student->payment_code ?></span></div>
            <div class="col-7"><strong>Classroom Stream Assignment:</strong> <span class="text-dark fw-semibold"><?= Html::encode($student->class_level) ?></span></div>
            <div class="col-5 text-end"><strong>System Status:</strong> <span class="text-success fw-bold uppercase">Enrolled</span></div>
            <?php if (!empty($student->sex)): ?>
                <div class="col-7"><strong>Sex:</strong> <span class="text-dark"><?= strtoupper($student->sex) === 'FEMALE' ? 'Female' : 'Male' ?></span></div>
            <?php endif; ?>
            <?php if (strpos($classLevel, 'Primary') !== false && $classPosition !== null): ?>
                <div class="col-5 text"><strong>Class Position:</strong> <span class="fw-bold text-primary"><?= (int)$classPosition ?>/<?= (int)$classSize ?></span></div>
            <?php endif; ?>
        </div>
    </div>



    <table class="table table-bordered border-dark text-dark align-middle fs-6" style="border-width: 2px !important;">
        <thead class="table-light text-center text-uppercase fw-bold">
            <tr>
                <th style="width: 35%;">Subject Course</th>

                <?php if ($hasBotData): ?> <th style="width: 12%;"><?= $botColumnLabel ?></th> <?php endif; ?>
                <?php if ($hasMotData): ?> <th style="width: 12%;"><?= $motColumnLabel ?></th> <?php endif; ?>
                <?php if ($hasEotData): ?> <th style="width: 12%;"><?= $eotColumnLabel ?></th> <?php endif; ?>

                <th style="width: 12%;">Grade</th>
                <th style="width: 25%;">Assigned Assessment Remarks</th>
            </tr>
        </thead>
        <tbody>
                        <?php if (empty($gradesList)): ?>
                <tr><td colspan="<?= 3 + ($hasBotData?1:0) + ($hasMotData?1:0) + ($hasEotData?1:0) ?>" class="text-center py-5 text-muted font-monospace italic">No examination scores finalized.</td></tr>
            <?php else: ?>
                <?php foreach ($gradesList as $g):
                    $bot = (float)$g['bot_mark'];
                    $mot = (float)$g['mot_mark'];
                    $eot = (float)$g['eot_mark'];
                    // Pull full details matching the dynamic mapping engine
                    $uneb = getUnebDetails($bot, $mot, $eot, $hasBotData, $hasMotData, $hasEotData);
                ?>
                    <tr>
                        <td class="fw-bold font-monospace ps-2 text-dark"><?= Html::encode($g['subject_name']) ?></td>
                        <?php if ($hasBotData): ?> <td class="text-center font-monospace"><?= $bot ?></td> <?php endif; ?>
                        <?php if ($hasMotData): ?> <td class="text-center font-monospace"><?= $mot ?></td> <?php endif; ?>
                        <?php if ($hasEotData): ?> <td class="text-center font-monospace"><?= $eot ?></td> <?php endif; ?>
                        <td class="text-center font-monospace fw-bold fs-5 bg-light text-dark"><?= $uneb['G'] ?></td>
                        <td class="ps-2 text-muted small italic"><?= Html::encode($g['teacher_comment'] ?: $uneb['R']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

        </tbody>
    </table>

      <?php if (strpos($classLevel, 'Primary') !== false && ($totalMarksOutOf400 !== null || $classPosition !== null)): ?>
        <div class="row g-3 mb-4 mx-0 text-dark bg-light border rounded p-3 text-center align-items-center">
            <?php if ($totalMarksOutOf400 !== null): ?>
                <div class="col-12 col-md-6">
                    <span class="text-uppercase small tracking-wider d-block">Total Marks</span>
                    <h5 class="fw-bold text-dark mb-0 mt-1"><?= number_format($totalMarksOutOf400, 0) ?> / 400</h5>
                </div>
            <?php endif; ?>
            <?php if ($classPosition !== null): ?>
                <div class="col-12 col-md-6">
                    <span class="text-uppercase small tracking-wider d-block">Position In Class</span>
                    <h5 class="fw-bold text-dark mb-0 mt-1"><?= (int)$classPosition ?> / <?= (int)$classSize ?></h5>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    &nbsp;

    <div class="row g-3 mb-4 mx-0 text-dark bg-white border border-2 border-dark rounded p-3 text-center align-items-center">
        <div class="col-12 col-md-6 border-end border-md border-dark border-opacity-20">
            <span class="text-uppercase  small tracking-wider text d-block"><?= $summaryHeaderLabel ?></span>
            <h4 class="h2  fw-black text-dark mb-0 mt-1"><?= $summaryValueBlock ?></h4>
        </div>
        <div class="col-12 col-md-6">
            <span class="text-uppercase font-monospace small tracking-wider text-light d-block"><?= $secondSummaryLabel ?></span>
            <h4 class="h2 font-monospace fw-black text-primary mb-0 mt-1"><?= $divisionLabel ?></h4>
        </div>
    </div>

  

    <?php if ($promotionNote): ?>
        <div class="alert alert-warning border-0 py-2 px-3 mb-4 small">
            <i class="bi bi-info-circle-fill me-1"></i> <?= Html::encode($promotionNote) ?>
        </div>
    <?php endif; ?>

    <div class="row pt-5 mt-5 fs-6" style="margin-top: 150px !important;">
        <div class="col-6 text-center border-top pt-2" style="border-top: 1px dashed #212529 !important;">
            <div class="fw-bold text-dark mb-0">Classroom Tutor Signature</div>
            <small class="text-muted text-xs">Verification Check Completed</small>
        </div>
        <div class="col-6 text-center border-top pt-2" style="border-top: 1px dashed #212529 !important;">
            <div class="fw-bold text-dark mb-0">Director of Studies (D.O.S.) Stamp</div>
            <small class="text-muted">Official Seal Authorized Matrix</small>
        </div>
    </div>

    <div class="mt-5 pt-3 border-top border-dark border-opacity-25 report-key-block">
        <?php if (strpos($classLevel, 'Primary') !== false): ?>
            <div class="border border-dark p-3">
                <h6 class="report-key-heading">Key To Grading</h6>
                <table class="table table-bordered border-dark text-center mb-2 report-key-table">
                    <thead>
                        <tr class="table-light">
                            <th>Grade</th><th>D1</th><th>D2</th><th>C3</th><th>C4</th><th>C5</th><th>C6</th><th>P7</th><th>P8</th><th>F9</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="table-light">Score Range</th>
                            <td>80-100</td><td>75-79</td><td>70-74</td><td>65-69</td><td>60-64</td><td>50-59</td><td>45-49</td><td>40-44</td><td>00-39</td>
                        </tr>
                    </tbody>
                </table>
                <p class="report-key-note mb-0">
                    <strong>Awarding Aggregates:</strong> Division I 4&ndash;12 pts &middot; Division II 13&ndash;23 pts &middot; Division III 24&ndash;29 pts &middot; Division IV 30&ndash;34 pts &middot; Division U 35+ pts (Ungraded).
                    <em>Lower aggregate points indicate better academic standing.</em>
                </p>
            </div>
        <?php elseif (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false): ?>
            <div class="border border-dark p-3">
                <h6 class="report-key-heading">Official UACE A-Level Principal Points Conversion Key</h6>
                <table class="table table-bordered border-dark text-center mb-2 report-key-table">
                    <thead>
                        <tr class="table-light">
                            <th>Grade</th><th>D1 / D2</th><th>C3</th><th>C4</th><th>C5</th><th>C6</th><th>P7 / P8</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="table-light">Letter</th>
                            <td>A</td><td>B</td><td>C</td><td>D</td><td>E</td><td>O</td>
                        </tr>
                        <tr>
                            <th class="table-light">Points</th>
                            <td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td>
                        </tr>
                    </tbody>
                </table>
                <p class="report-key-note mb-0">
                    <strong>UACE Points Rules:</strong> Core Principal maximum = 18 pts (3 subjects &times; 6 pts). Subsidiary pass (General Paper / ICT / Sub-Math, C6 or better i.e. 50+ marks) = 1 pt each, capped at 2 pts.
                    <em>Total maximum scale = 20 points.</em>
                </p>
            </div>
        <?php elseif (strpos($classLevel, 'Senior 1') !== false || strpos($classLevel, 'Senior 2') !== false): ?>
            <div class="border border-dark p-3">
                <h6 class="report-key-heading">Key To Grading</h6>
                <table class="table table-bordered border-dark text-center mb-2 report-key-table">
                    <thead>
                        <tr class="table-light">
                            <th>Grade</th><th>D1</th><th>D2</th><th>C3</th><th>C4</th><th>C5</th><th>C6</th><th>P7</th><th>P8</th><th>F9</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="table-light">Score Range</th>
                            <td>80-100</td><td>75-79</td><td>70-74</td><td>65-69</td><td>60-64</td><td>50-59</td><td>45-49</td><td>40-44</td><td>00-39</td>
                        </tr>
                    </tbody>
                </table>
                <p class="report-key-note mb-0">
                    Lower Secondary reporting uses the <strong>overall average</strong> across all subjects rather than an aggregate, in line with the competency-based curriculum.
                </p>
            </div>
        <?php else: ?>
            <div class="border border-dark p-3">
                <h6 class="report-key-heading">Official UCE O-Level Best 8 Grading Key</h6>
                <table class="table table-bordered border-dark text-center mb-2 report-key-table">
                    <thead>
                        <tr class="table-light">
                            <th>Grade</th><th>D1</th><th>D2</th><th>C3</th><th>C4</th><th>C5</th><th>C6</th><th>P7</th><th>P8</th><th>F9</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="table-light">Score Range</th>
                            <td>80-100</td><td>75-79</td><td>70-74</td><td>65-69</td><td>60-64</td><td>50-59</td><td>45-49</td><td>40-44</td><td>00-39</td>
                        </tr>
                    </tbody>
                </table>
                <p class="report-key-note mb-0">
                    <strong>Awarding Aggregates:</strong> Division I 8&ndash;32 pts &middot; Division II 33&ndash;45 pts &middot; Division III 46&ndash;58 pts &middot; Division IV 59&ndash;72 pts &middot; Division U 73+ pts (Ungraded). Best 8 of 10 subjects counted.
                    <em>Failing English or Mathematics does not block automatic promotion.</em>
                </p>
            </div>
        <?php endif; ?>
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
.fs-7 { font-size: 0.8rem !important; }
.text-xxs { font-size: 0.72rem !important; }

.report-key-block {
    font-family: 'Times New Roman', serif;
}

.report-key-heading {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    text-decoration: underline;
    text-underline-offset: 3px;
    margin-bottom: 0.75rem;
    color: #212529;
}

.report-key-table {
    font-size: 0.8rem;
}

.report-key-table th,
.report-key-table td {
    padding: 0.35rem 0.4rem;
    vertical-align: middle;
}

.report-key-table thead th {
    font-weight: 700;
    background-color: #f1f1f1 !important;
}

.report-key-table tbody th {
    font-weight: 700;
    text-align: left;
    white-space: nowrap;
}

.report-key-note {
    font-size: 0.78rem;
    color: #333;
    line-height: 1.5;
}

.report-key-note em {
    color: #555;
}

@media print {
    .report-key-block {
        break-inside: avoid;
    }
}

</style>