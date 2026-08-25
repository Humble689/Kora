<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Schools[] $schools */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Master SaaS Control Panel';

$workingId = Yii::$app->session->get('super_admin_school_id');
$workingSchool = null;
if ($workingId) {
    $workingSchool = \app\models\Schools::findOne((int) $workingId);
}
?>

<div class="site-school-registry bg-light py-4 min-vh-100">
    <div class="container-fluid px-3 px-md-4" style="max-width: 90rem;">

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

        <?php if ($workingSchool): ?>
            <div class="alert alert-info border-0 shadow-sm rounded-3 mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <i class="bi bi-building me-2"></i>
                    Working school: <strong><?= Html::encode($workingSchool->name) ?></strong>
                    <span class="text-muted small ms-1">(#<?= (int) $workingSchool->id ?>)</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= Url::toRoute(['site/bursar']) ?>" class="btn btn-sm btn-primary rounded-3">
                        <i class="bi bi-receipt-cutoff me-1"></i> Open Collections Ledger
                    </a>
                    <a href="<?= Url::toRoute(['site/school-admin']) ?>" class="btn btn-sm btn-outline-primary rounded-3">
                        Admin Dashboard
                    </a>
                    <a href="<?= Url::toRoute(['site/clear-school-context']) ?>" class="btn btn-sm btn-outline-secondary rounded-3">
                        Clear context
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-3">
                <i class="bi bi-info-circle me-2"></i>
                No school selected. Use <strong>Switch into school</strong> on a row below to operate bursar / academic desks for that tenant.
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom pb-3 gap-3">
            <h1 class="h3 fw-bold text-dark mb-0">SaaS Master School Registry</h1>
            <a href="<?= Url::toRoute(['site/create-school']) ?>" class="btn btn-primary fw-bold rounded-3 kora-onboard-btn">+ Onboard New School</a>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden kora-card-contained">

            <!-- Desktop / tablet: full table -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>School Name</th>
                            <th>Settlement Account</th>
                            <th>Target Base Fees</th>
                            <th>Contact Email</th>
                            <th style="min-width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schools)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No schools have been onboarded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schools as $school): ?>
                                <?php $isActive = $workingId !== null && (int) $workingId === (int) $school->id; ?>
                                <tr class="<?= $isActive ? 'table-primary' : '' ?>">
                                    <td><?= (int) $school->id ?></td>
                                    <td class="fw-bold">
                                        <?= Html::encode($school->name) ?>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success ms-1">Working here</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-monospace text-muted"><?= Html::encode($school->bank_account) ?></td>
                                    <td class="font-monospace fw-bold text-success">UGX <?= number_format((float) $school->base_tuition_fees, 0) ?></td>
                                    <td><?= Html::encode($school->contact_email) ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if ($isActive): ?>
                                                <span class="btn btn-sm btn-success py-0 px-2 rounded disabled">Working here</span>
                                            <?php else: ?>
                                                <a href="<?= Url::toRoute(['site/switch-school', 'id' => $school->id]) ?>"
                                                   class="btn btn-sm btn-primary py-0 px-2 rounded">
                                                    Switch into school
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= Url::toRoute(['site/edit-school', 'id' => $school->id]) ?>"
                                               class="btn btn-sm btn-outline-secondary py-0 px-2 rounded">
                                                Edit Specifications
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile: stacked school cards -->
            <div class="d-md-none">
                <?php if (empty($schools)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        No schools have been onboarded yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($schools as $school): ?>
                        <?php $isActive = $workingId !== null && (int) $workingId === (int) $school->id; ?>
                        <div class="kora-school-card border-bottom p-3 <?= $isActive ? 'bg-primary bg-opacity-10' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="fw-bold text-dark">
                                    <?= Html::encode($school->name) ?>
                                    <?php if ($isActive): ?>
                                        <span class="badge bg-success ms-1">Working here</span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-muted small text-nowrap">#<?= (int) $school->id ?></span>
                            </div>

                            <div class="small mb-1">
                                <span class="text-muted">Settlement Account:</span>
                                <span class="font-monospace"><?= Html::encode($school->bank_account) ?></span>
                            </div>

                            <div class="small mb-1">
                                <span class="text-muted">Target Base Fees:</span>
                                <span class="font-monospace fw-bold text-success">UGX <?= number_format((float) $school->base_tuition_fees, 0) ?></span>
                            </div>

                            <div class="small mb-3">
                                <span class="text-muted">Contact:</span>
                                <?= Html::encode($school->contact_email) ?>
                            </div>

                            <div class="d-grid gap-2">
                                <?php if ($isActive): ?>
                                    <button type="button" class="btn btn-sm btn-success rounded" disabled>Working here</button>
                                <?php else: ?>
                                    <a href="<?= Url::toRoute(['site/switch-school', 'id' => $school->id]) ?>"
                                       class="btn btn-sm btn-primary rounded">
                                        Switch into school
                                    </a>
                                <?php endif; ?>
                                <a href="<?= Url::toRoute(['site/edit-school', 'id' => $school->id]) ?>"
                                   class="btn btn-sm btn-outline-secondary rounded">
                                    Edit Specifications
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .kora-card-contained {
        overflow: hidden;
    }
    .kora-school-card:last-child {
        border-bottom: none !important;
    }
    .kora-school-card:nth-child(odd):not(.bg-primary) {
        background-color: #fafbfd;
    }

    @media (max-width: 575.98px) {
        .site-school-registry .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        .site-school-registry h1.h3 {
            font-size: 1.2rem;
        }
        .kora-onboard-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>