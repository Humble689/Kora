<?php
/** @var yii\web\View $this */
/** @var app\models\Students $student */
/** @var array $gradesList */
/** @var string $term */
/** @var int $year */

use yii\helpers\Html;

$this->title = 'Academic Report ' . $student->name;

$hasBotData = false;
$hasMotData = false;
$hasEotData = false;

foreach ($gradesList as $g) {
    if ((float)$g['bot_mark'] > 0) $hasBotData = true;
    if ((float)$g['mot_mark'] > 0) $hasMotData = true;
    if ((float)$g['eot_mark'] > 0) $hasEotData = true;
}

function getUnebDetails($bot, $mot, $eot, $hasBot, $hasMot, $hasEot) {
    $totalWeight = 0;
    $earnedPoints = 0;

    if ($hasBot && $bot > 0) { $earnedPoints += ($bot * 0.2); $totalWeight += 0.2; }
    if ($hasMot && $mot > 0) { $earnedPoints += ($mot * 0.3); $totalWeight += 0.3; }
    if ($hasEot && $eot > 0) { $earnedPoints += ($eot * 0.5); $totalWeight += 0.5; }

    if ($totalWeight === 0) return ['G' => 'F9', 'P' => 9, 'R' => 'Fail'];

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

$classLevel = $student->class_level;
$summaryHeaderLabel = "Overall Performance Summary";
$summaryValueBlock = "Incomplete Records";
$divisionLabel = "N/A";

$allPointsArray = [];
$aLevelCorePoints = 0;
$subsidiaryPoints = 0;

foreach ($gradesList as $g) {
    $u = getUnebDetails((float)$g['bot_mark'], (float)$g['mot_mark'], (float)$g['eot_mark'], $hasBotData, $hasMotData, $hasEotData);
    $allPointsArray[] = $u['P'];

    // Core parameters grouping calculation for A-level strings
    if (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false) {
        if (in_array($g['subject_name'], ['General Paper', 'Sub-Math', 'Sub-ICT'])) {
            if ($u['P'] <= 8) $subsidiaryPoints += 1; // Passes earn max 1 point each
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

    }}
}

// execute LEVEL SPECIFIC COMPILES
if (!empty($allPointsArray)) {
    if (strpos($classLevel, 'Primary') !== false) {
        $aggregate = array_sum(array_slice($allPointsArray, 0, 4));
        $summaryHeaderLabel = "Total Aggregates";
        $summaryValueBlock = $aggregate . " ";

        if ($aggregate <= 12) $divisionLabel = "Division 1";
        elseif ($aggregate <= 24) $divisionLabel = "Division 2";
        elseif ($aggregate <= 28) $divisionLabel = "Division 3";
        else $divisionLabel = "Division 4";
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
    else {
        // O-Level Rule: Isolate and sum the BEST 8 subjects points values dynamically
        sort($allPointsArray, SORT_NUMERIC);
        $best8Points = array_slice($allPointsArray, 0, 8);
        $aggregate = array_sum($best8Points);

        // Pad aggregates if student takes fewer than 8 total subjects courses
        if (count($best8Points) < 8) {
            $aggregate += (8 - count($best8Points)) * 9;
        }

        $summaryHeaderLabel = "O-Level UCE Best 8 Aggregate";
        $summaryValueBlock = "Agg " . $aggregate;

        if ($aggregate <= 32) $divisionLabel = "Division 1";
        elseif ($aggregate <= 45) $divisionLabel = "Division 2";
        elseif ($aggregate <= 58) $divisionLabel = "Division 3";
        else $divisionLabel = "Division 4";
    }
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
        </div>
    </div>



    <table class="table table-bordered border-dark text-dark align-middle fs-6" style="border-width: 2px !important;">
        <thead class="table-light text-center text-uppercase fw-bold">
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
    &nbsp;

    <div class="row g-3 mb-4 mx-0 text-dark bg-white border border-2 border-dark rounded p-3 text-center align-items-center">
        <div class="col-12 col-md-6 border-end border-md border-dark border-opacity-20">
            <span class="text-uppercase  small tracking-wider text d-block"><?= $summaryHeaderLabel ?></span>
            <h4 class="h2  fw-black text-dark mb-0 mt-1"><?= $summaryValueBlock ?></h4>
        </div>
        <div class="col-12 col-md-6">
            <span class="text-uppercase font-monospace small tracking-wider text-light d-block">Awarding Classification Award</span>
            <h4 class="h2 font-monospace fw-black text-primary mb-0 mt-1"><?= $divisionLabel ?></h4>
        </div>
    </div>

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

    <div class="mt-5 pt-3 border-top border-light border-opacity-20 no-print "style="width: 750px; height: 15550px;">
        <?php if (strpos($classLevel, 'Primary') !== false): ?>
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-dark text-xs mb-2 uppercase tracking-wider"><i class="bi bi-key-fill text-warning me-1"></i> Grading Key</h6>
                <div class="row g-2 text-center text-xs  mb-2">
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1">80-100 : D1</span></div>
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1">75-79 : D2</span></div>
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1 ">70-74 : C3</span></div>
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1 ">65-69 : C4</span></div>
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1 ">60-64 : C5</span></div>
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1 ">50-59 : C6</span></div>
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1">45-49 : P7</span></div>
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1">40-44 : P8</span></div>
                    <div class="col-4 col-md-1.3"><span class=" bg-light px-2 py-1 ">00-39 : F9</span></div>
                </div>
                <div class="border-top pt-2 mt-2 fs-7 text-muted">
                    <strong>Awarding Aggregates:</strong> Div 1: 4-12 Points | Div 2: 13-24 Points | Div 3: 25-28 Points | Div 4: 29-32 Points. <span class="text-xs text-primary fw-bold">(Lower aggregate points indicate better academic standing)</span>
                </div>
            </div>
        <?php elseif (strpos($classLevel, 'Senior 5') !== false || strpos($classLevel, 'Senior 6') !== false): ?>
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-dark font-monospace text-xs mb-2 uppercase tracking-wider"> Official UACE A-Level Principal Points Conversion Key</h6>
                <div class="row g-2 text-center text-xs mb-2">
                    <div class="col-4 col-md-2"><div class="p-1 rounded fw-bold">D1 / D2 (A) : 6 Pts</div></div>
                    <div class="col-4 col-md-2"><div class="p-1  rounded fw-bold">C3 (B) : 5 Pts</div></div>
                    <div class="col-4 col-md-2"><div class="p-1   rounded fw-bold">C4 (C) : 4 Pts</div></div>
                    <div class="col-4 col-md-2"><div class="p-1 text-dark rounded fw-bold">C5 (D) : 3 Pts</div></div>
                    <div class="col-4 col-md-2"><div class="p-1   rounded fw-bold">C6 (E) : 2 Pts</div></div>
                    <div class="col-4 col-md-2"><div class="p-1  text-dark rounded fw-bold">P7/P8 (O) : 1 Pt</div></div>
                </div>
                <div class="border-top pt-2 mt-2 fs-7 text-muted ">
                    <strong>UACE Points Rules:</strong> Core Principal Maximum Score = 18 Points (3 Subjects × 6 Pts). General Paper Pass = 1 Point | Subsidiary Math/ICT Pass = 1 Point. <span class="text-xs fw-bold">Total Maximum Scale Matrix = 20 Points.</span>
                </div>
            </div>
        <?php else: ?>
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-dark  text-xs mb-2 uppercase tracking-wider"><i class="bi bi-key-fill text-warning me-1"></i> Official UCE O-Level Best 8 Grading Key</h6>
                <div class="text-muted small font-monospace">
                    Div 1: 8-32 Points | Div 2: 33-45 Points | Div 3: 46-58 Points | Div 4: 59-72 Points. Grades scaled from Distinction 1 (best) down to Fail 9.
                </div>
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

</style>