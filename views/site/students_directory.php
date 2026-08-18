<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Students[] $allStudents */
/** @var yii\data\Pagination $studentPages */
/** @var string $searchKeyword */
/** @var string $balanceFilter */
/** @var string $classLevel */
/** @var string[] $classLevels */

use yii\bootstrap5\LinkPager;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Registered Student Directory';

$totalCount = $studentPages->totalCount ?? count($allStudents);
$pageStart  = $totalCount > 0 ? ($studentPages->getOffset() + 1) : 0;
$pageEnd    = min($studentPages->getOffset() + $studentPages->getLimit(), $totalCount);

// Class-level sort state, e.g. ?sort=class_level or ?sort=-class_level (Yii2 Sort convention)
$currentSort   = Yii::$app->request->get('sort', '');
$isClassAsc    = $currentSort === 'class_level';
$isClassDesc   = $currentSort === '-class_level';
$nextClassSort = $isClassAsc ? '-class_level' : 'class_level';
$classSortUrl  = Url::toRoute([
    'site/students-directory',
    'q' => $searchKeyword,
    'balance_status' => $balanceFilter,
    'class_level' => $classLevel,
    'sort' => $nextClassSort,
]);
?>

<div class="site-students-directory bg-light py-4 min-vh-100">
    <div class="container-fluid" style="max-width: 90rem;">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
            <div class="card-header bg-primary text-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="card-title h6 fw-bold mb-0">
                    <i class="bi bi-people-fill me-1"></i> Registered Student Directory
                </h5>

                <div class="d-flex flex-wrap align-items-center gap-2 justify-content-md-end flex-grow-1" style="max-width: 760px;">
                    <a href="<?= Url::toRoute(['site/export-students', 'q' => $searchKeyword, 'balance_status' => $balanceFilter, 'class_level' => $classLevel, 'sort' => $currentSort]) ?>" class="btn btn-sm btn-light text-primary d-flex align-items-center gap-1 fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-excel-fill"></i> Save Directory
                    </a>

                    <form method="get" action="<?= Url::toRoute(['site/students-directory']) ?>" class="d-flex m-0 gap-1 align-items-center flex-grow-1 flex-wrap">
                        <input type="hidden" name="sort" value="<?= Html::encode($currentSort) ?>">
                        <select name="class_level" class="form-select form-select-sm fw-bold text-dark" style="max-width: 150px;" onchange="this.form.submit()">
                            <option value="ALL" <?= $classLevel === 'ALL' ? 'selected' : '' ?>>All Classes</option>
                            <?php foreach ($classLevels as $level): ?>
                                <option value="<?= Html::encode($level) ?>" <?= $classLevel === $level ? 'selected' : '' ?>><?= Html::encode($level) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="balance_status" class="form-select form-select-sm fw-bold text-dark" style="max-width: 150px;" onchange="this.form.submit()">
                            <option value="ALL" <?= $balanceFilter === 'ALL' ? 'selected' : '' ?>>All Students</option>
                            <option value="OWING" <?= $balanceFilter === 'OWING' ? 'selected' : '' ?>>Owing Only</option>
                            <option value="CLEARED" <?= $balanceFilter === 'CLEARED' ? 'selected' : '' ?>>Fully Cleared</option>
                        </select>
                        <div class="input-group input-group-sm" style="max-width: 260px;">
                            <input type="text" name="q" value="<?= Html::encode($searchKeyword) ?>" class="form-control bg-white text-dark border-0 rounded-start-2" placeholder="Search name or code..." autocomplete="off">
                            <?php if (!empty($searchKeyword) || $balanceFilter !== 'ALL' || $classLevel !== 'ALL' || $currentSort !== ''): ?>
                                <a href="<?= Url::toRoute(['site/students-directory']) ?>" class="btn btn-sm btn-light border-0 d-flex align-items-center"><i class="bi bi-x"></i></a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-dark fw-bold border-0 px-3">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 small">
                    <thead class="table-light text-secondary text-uppercase border-bottom">
                        <tr>
                            <th class="ps-3">No</th>
                            <th class="ps-4">Student Name</th>
                            <th>
                                <a href="<?= $classSortUrl ?>" class="text-decoration-none text-secondary text-uppercase d-inline-flex align-items-center gap-1">
                                    Class Level
                                    <?php if ($isClassAsc): ?>
                                        <i class="bi bi-sort-alpha-down text-primary"></i>
                                    <?php elseif ($isClassDesc): ?>
                                        <i class="bi bi-sort-alpha-up text-primary"></i>
                                    <?php else: ?>
                                        <i class="bi bi-arrow-down-up opacity-50"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>10-Digit Payment Code</th>
                            <th>Tuition Balance Due</th>
                            <th>S-Wallet Balance</th>
                            <th class="text-end pe-4">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allStudents)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No student profiles match the filter criteria under this institution.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allStudents as $i => $st): ?>
                                <?php
                                $isNoShow = ($st->status ?? 'ACTIVE') !== 'ACTIVE';
                                $tuitionBalance = (float)$st->tuition_balance;
                                ?>
                                <tr class="<?= $isNoShow ? 'opacity-75' : '' ?>">
                                    <td class="ps-3"><?= $studentPages->getOffset() + $i + 1 ?></td>

                                    <td class="ps-4 fw-bold text-dark">
                                        <?= Html::encode($st->name) ?>
                                        <?php if ($isNoShow): ?>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">No-Show</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><span class="text-grey fw-bold px-2 py-1"><?= Html::encode($st->class_level) ?></span></td>

                                    <td>
                                        <span class="text-dark bg-light px-2 py-1 rounded border fw-bold font-monospace" style="letter-spacing:.03em;">
                                            <?= Html::encode($st->payment_code) ?>
                                        </span>
                                    </td>

                                    <td class="fw-bold <?= $tuitionBalance > 0 ? 'text-danger' : 'text-success' ?>">
                                        UGX <?= number_format($tuitionBalance, 0) ?>
                                        <?php if ($tuitionBalance <= 0): ?>
                                            <i class="bi bi-check-circle-fill ms-1"></i>
                                        <?php endif; ?>
                                    </td>

                                    <td class="fw-bold text-success">UGX <?= number_format((float)$st->swallet_balance, 0) ?></td>

                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1 align-items-center flex-wrap justify-content-end">

                                            <a href="<?= Url::toRoute(['site/edit-student', 'id' => $st->id]) ?>" class="btn btn-outline-secondary btn-sm rounded-2 fw-semibold d-inline-flex align-items-center gap-1" title="Edit Profile">
                                                <i class="bi bi-pencil-square"></i> <span class="d-none d-lg-inline">Edit</span>
                                            </a>

                                            <a href="<?= Url::toRoute(['site/student-dashboard', 'code' => $st->payment_code]) ?>" class="btn btn-outline-dark btn-sm rounded-2 fw-semibold d-inline-flex align-items-center gap-1" target="_blank" title="View File">
                                                <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">View</span>
                                            </a>

                                            <?php if (!$isNoShow): ?>
                                                <?= Html::beginForm(['site/mark-no-show', 'id' => $st->id], 'post', [
                                                    'class' => 'm-0 d-inline-block',
                                                    'onsubmit' => "return confirm('Reconcile Student Accounting Fields?\n\nAre you sure you want to mark \'{$st->name}\' as a No-Show? This will automatically drop their tuition balance fee charge to UGX 0.');",
                                                ]) ?>
                                                    <button type="submi" class="btn btn-outline-warning btn-sm rounded-2 fw-semibold d-inline-flex align-items-center gap-1" title="Mark No-Show">
                                                        <i class="bi bi-person-x-fill"></i> <span class="d-none d-lg-inline">No-Show</span>
                                                    </button>
                                                <?= Html::endForm() ?>
                                            <?php endif; ?>

                                            <?= Html::beginForm(['site/delete-student', 'id' => $st->id], 'post', [
                                                'class' => 'm-0 d-inline-block',
                                                'onsubmit' => "return confirm('CRITICAL WARNING:\n\nAre you completely sure you want to permanently delete student profile: \"{$st->name}\"?\nThis will completely wipe out their transactional ledger stream records history logs! This action is irreversible.');",
                                            ]) ?>
                                                <button type="" class="btn btn-danger btn-sm rounded-2 fw-semibold d-inline-flex align-items-center gap-1" title="Delete Student">
                                                    <i class="bi bi-trash3-fill"></i> <span class="d-none d-lg-inline">Delete</span>
                                                </button>
                                            <?= Html::endForm() ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white d-flex flex-column flex-md-row justify-content-between align-items-center pt-3 border-top gap-2">
                <div class="text-muted small">
                    <?php if ($totalCount > 0): ?>
                        Showing <strong><?= $pageStart ?>&ndash;<?= $pageEnd ?></strong> of <strong><?= $totalCount ?></strong> students
                    <?php else: ?>
                        No students to show
                    <?php endif; ?>
                </div>
                <?= LinkPager::widget([
                    'pagination' => $studentPages,
                    'maxButtonCount' => 5,
                    'options' => ['class' => 'pagination pagination-sm mb-0'],
                ]) ?>
            </div>
        </div>
    </div>
</div>