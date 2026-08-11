<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Students[] $allStudents */
/** @var yii\data\Pagination $studentPages */
/** @var string $searchKeyword */
/** @var string $balanceFilter */

use yii\bootstrap5\LinkPager;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Registered Student Directory';
?>

<div class="site-students-directory bg-light py-4 min-vh-100">
    <div class="container-fluid" style="max-width: 90rem;">

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
            <div class="card-header bg-primary text-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="card-title h6 fw-bold mb-0">
                    <i class="bi bi-people-fill me-1"></i> Registered Student Directory
                </h5>
                
                <div class="d-flex flex-wrap align-items-center gap-2 justify-content-md-end flex-grow-1" style="max-width: 600px;">
                    <a href="<?= Url::toRoute(['site/export-students', 'q' => $searchKeyword, 'balance_status' => $balanceFilter]) ?>" class="btn btn-sm btn-light text-primary d-flex align-items-center gap-1 fw-bold shadow-sm"><i class="bi bi-file-earmark-excel-fill"></i> Save Directory</a>
                    
                    <form method="get" action="<?= Url::toRoute(['site/students-directory']) ?>" class="d-flex m-0 gap-1 align-items-center flex-grow-1">
                        <select name="balance_status" class="form-select form-select-sm fw-bold text-dark" style="max-width: 150px;" onchange="this.form.submit()">
                            <option value="ALL" <?= $balanceFilter === 'ALL' ? 'selected' : '' ?>>All Students</option>
                            <option value="OWING" <?= $balanceFilter === 'OWING' ? 'selected' : '' ?>>Owing Only</option>
                            <option value="CLEARED" <?= $balanceFilter === 'CLEARED' ? 'selected' : '' ?>>Fully Cleared</option>
                        </select>
                        <div class="input-group input-group-sm">
                            <input type="text" name="q" value="<?= Html::encode($searchKeyword) ?>" class="form-control bg-white text-dark border-0 rounded-start-2" placeholder="Search name or code..." autocomplete="off">
                            <?php if (!empty($searchKeyword) || $balanceFilter !== 'ALL'): ?>
                                <a href="<?= Url::toRoute(['site/students-directory']) ?>" class="btn btn-sm btn-light border-0 d-flex align-items-center"><i class="bi bi-x"></i></a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-dark fw-bold border-0 px-3" style="background-color: grey !important;">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 small">
                    <thead class="table-light text-secondary uppercase border-bottom">
                        <tr>
                            <th>No</th>
                            <th class="ps-4">Student Name</th>
                            <th>Class Level</th>
                            <th>10-Digit Payment Code</th>
                            <th>Tuition Balance Due</th>
                            <th>S-Wallet Balance</th>
                            <th class="text-end pe-4">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allStudents)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No student profiles match the filter criteria under this institution.</td>
                            </tr>
                        <?php else: ?>
                                        <?php foreach ($allStudents as $i=>$st): ?>
                                <tr>
                                    <td>
                                        <?= $i+1 ?>
                                    </td>
                                    
                                    <td class="ps-4 fw-bold text-dark"><?= Html::encode($st->name) ?></td>
                                    
                                    <td><span class="badge bg-secondary px-2.5 py-1"><?= Html::encode($st->class_level) ?></span></td>
                                    
                                    <td>
                                        <span class=text-dark bg-light px-2.5 py-1 rounded border fw-bold tracking-wide">
                                            <?= Html::encode($st->payment_code) ?>
                                        </span>
                                    </td>
                                    
                                    <td class="fw-bold text-danger">UGX <?= number_format((float)$st->tuition_balance, 0) ?></td>
                                    
                                    <td class="fw-bold text-success">UGX <?= number_format((float)$st->swallet_balance, 0) ?></td>
                                    
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            
                                            <a href="<?= Url::toRoute(['site/edit-student', 'id' => $st->id]) ?>" class="btn btn-outline-secondary btn-sm rounded-2 fw-semibold d-flex align-items-center gap-1">
                                                <i class="bi bi-pencil-square"></i> Edit Profile
                                            </a>
                                            
                                            <a href="<?= Url::toRoute(['site/student-dashboard', 'code' => $st->payment_code]) ?>" class="btn btn-outline-dark btn-sm rounded-2 fw-semibold d-flex align-items-center gap-1" target="_blank">
                                                <i class="bi bi-eye"></i> View File
                                            </a>
                                            
                                            <?php if (($st->status ?? 'ACTIVE') === 'ACTIVE'): ?>
                                                <a href="<?= Url::toRoute(['site/mark-no-show', 'id' => $st->id]) ?>" 
                                                   class="btn btn-outline-warning btn-sm rounded-2 fw-semibold d-flex align-items-center gap-1"
                                                   onclick="return confirm('Reconcile Student Accounting Fields?\n\nAre you sure you want to mark \'<?= Html::encode($st->name) ?>\' as a No-Show? This will automatically drop their tuition balance fee charge to UGX 0.');">
                                                    <i class="bi bi-person-x-fill"></i> Mark No-Show
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-dark text-white px-2.5 py-1.5 rounded-2 fw-bold text-xs">No-Show Registered</span>
                                            <?php endif; ?>

                                            <?= Html::beginForm(['site/delete-student', 'id' => $st->id], 'post', [
                                                'class' => 'm-0 d-inline-block',
                                                'onsubmit' => "return confirm('CRITICAL WARNING:\n\nAre you completely sure you want to permanently delete student profile: \"{$st->name}\"?\nThis will completely wipe out their transactional ledger stream records history logs! This action is irreversible.');"
                                            ]) ?>
                                                <button type="submit"  class="btn btn-outline-danger btn-sm rounded-2 fw-semibold d-flex align-items-center gap-1">
                                                    <i class="bi bi-trash3-fill"></i> Delete Student
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
            <div class="card-footer bg-white d-flex justify-content-center pt-3 border-top">
                <?= LinkPager::widget([
                    'pagination' => $studentPages,
                    'maxButtonCount' => 5,
                    'options' => ['class' => 'pagination pagination-sm mb-0']
                ]) ?>
            </div>
        </div>
    </div>
</div>
