<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $searchQuery */

$this->title = 'Canteen Transaction History';

$todayStart = date('Y-m-d 00:00:00');
$todayEnd = date('Y-m-d 23:59:59');

$todayQuery = \app\models\Transactions::find()
    ->where(['transaction_type' => 'CANTEEN_SPEND'])
    ->andWhere(['between', 'created_at', $todayStart, $todayEnd]);

$todayCount = (clone $todayQuery)->count();
$todayTotal = (float) (clone $todayQuery)->sum('amount');
$allTimeTotal = (float) \app\models\Transactions::find()
    ->where(['transaction_type' => 'CANTEEN_SPEND', 'school_id' => $currentSchoolId])
    ->sum('amount');
?>

<div class="site-canteen-transactions bg-light py-4 min-vh-100">
    <div class="container-fluid px-3 px-md-4" style="max-width: 90rem;">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 border-bottom pb-3 gap-2" style="border-color: var(--theme-border) !important;">
        <div>
            <span class="text-uppercase fw-bold small d-block mb-1" style="font-size: 11px; color: var(--theme-primary); letter-spacing: 0.08em;">
                Audit &amp; Reporting
            </span>
            <h1 class="h3 fw-bold mb-1" style="color: var(--theme-text);">Canteen Transaction History</h1>
            <p class="text-muted small mb-0">Complete record of canteen wallet deductions across all schools, most recent first.</p>
        </div>
        <div class="text-end d-none d-md-block">
            <i class="bi bi-shield-check fs-3" style="color: var(--theme-primary);"></i>
        </div>
    </div>

    <!-- Summary stat cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-3 shadow-sm p-3 h-100" style="background: var(--theme-soft);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: var(--theme-primary);">
                        <i class="bi bi-receipt text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.06em;">Today's Sales</div>
                        <div class="h5 fw-bold mb-0" style="color: var(--theme-text);"><?= $todayCount ?> transaction<?= $todayCount === 1 ? '' : 's' ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-3 shadow-sm p-3 h-100" style="background: var(--theme-soft);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: #16a34a;">
                        <i class="bi bi-cash-coin text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.06em;">Today's Revenue</div>
                        <div class="h5 fw-bold mb-0" style="color: var(--theme-text);">UGX <?= number_format($todayTotal, 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-3 shadow-sm p-3 h-100" style="background: var(--theme-soft);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: #7c3aed;">
                        <i class="bi bi-bar-chart-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.06em;">All-Time Total</div>
                        <div class="h5 fw-bold mb-0" style="color: var(--theme-text);">UGX <?= number_format($allTimeTotal, 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions table card -->
    <div class="card border-0 rounded-3 shadow-sm overflow-hidden kora-card-contained">
        <div class="card-header bg-white border-0 py-3 px-4" style="border-bottom: 1px solid var(--theme-border) !important;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h6 class="fw-bold mb-0 text-uppercase" style="font-size: 12px; letter-spacing: 0.05em; color: var(--theme-muted);">
                    <i class="bi bi-list-ul me-1"></i> Transaction Log
                </h6>

                <?php Pjax::begin(['id' => 'canteen-tx-pjax', 'timeout' => 8000]); ?>

                <?= Html::beginForm(['site/canteen-transactions'], 'get', ['class' => 'd-flex', 'style' => 'min-width: 280px;']) ?>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white" style="border-color: var(--theme-border);"><i class="bi bi-search text-muted"></i></span>
                        <?= Html::textInput('q', $searchQuery, [
                            'class' => 'form-control',
                            'placeholder' => 'Search student, school, or terminal...',
                            'style' => 'border-color: var(--theme-border);',
                        ]) ?>
                        <?php if (!empty($searchQuery)): ?>
                            <?= Html::a('<i class="bi bi-x-lg"></i>', ['site/canteen-transactions'], [
                                'class' => 'btn btn-outline-secondary',
                                'style' => 'border-color: var(--theme-border);',
                                'title' => 'Clear search',
                            ]) ?>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-sm fw-bold text-white" style="background: var(--theme-primary); border-color: var(--theme-primary);">
                            Search
                        </button>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="card-body p-0">

            <!-- Desktop / tablet: full grid -->
            <div class="table-responsive d-none d-md-block">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                    'headerRowOptions' => ['style' => 'background: var(--theme-soft);'],
                    'layout' => "{items}\n<div class='px-4 py-3'>{pager}</div>",
                    'pager' => [
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                    ],
                    'emptyText' => Html::tag('div', '<i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>' . (!empty($searchQuery) ? 'No transactions match "' . Html::encode($searchQuery) . '".' : 'No canteen transactions found.'), [
                        'class' => 'text-center text-muted py-5',
                    ]),
                    'columns' => [
                        [
                            'attribute' => 'created_at',
                            'label' => 'Date / Time',
                            'format' => 'datetime',
                            'headerOptions' => ['class' => 'text-uppercase small fw-bold px-4', 'style' => 'font-size: 11px; letter-spacing: 0.04em; color: var(--theme-muted); border: none;'],
                            'contentOptions' => ['class' => 'px-4 small text-muted'],
                        ],
                        [
                            'label' => 'School',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-uppercase small fw-bold', 'style' => 'font-size: 11px; letter-spacing: 0.04em; color: var(--theme-muted); border: none;'],
                            'value' => function ($model) {
                                $name = $model->student?->school?->name ?? null;
                                return $name
                                    ? Html::tag('span', Html::encode($name), ['class' => 'badge rounded-pill', 'style' => 'background: var(--theme-soft); color: var(--theme-primary); font-weight: 600; font-size: 11px;'])
                                    : Html::tag('span', 'Unassigned', ['class' => 'badge rounded-pill bg-light text-muted', 'style' => 'font-size: 11px;']);
                            },
                        ],
                        [
                            'label' => 'Student',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $name = $model->student?->name ?? 'Unknown';
                                $classLevel = $model->student?->class_level ?? '';
                                return Html::tag('div', Html::encode($name), ['class' => 'fw-semibold', 'style' => 'color: var(--theme-text);'])
                                    . ($classLevel ? Html::tag('div', Html::encode($classLevel), ['class' => 'text-muted', 'style' => 'font-size: 11px;']) : '');
                            },
                        ],
                        [
                            'attribute' => 'amount',
                            'label' => 'Amount',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return 'UGX ' . Html::tag('span', number_format((float) $model->amount, 0), ['class' => 'fw-bold', 'style' => 'color: var(--theme-text);']);
                            },
                            'headerOptions' => ['class' => 'text-uppercase small fw-bold text-end', 'style' => 'font-size: 11px; letter-spacing: 0.04em; color: var(--theme-muted); border: none;'],
                            'contentOptions' => ['class' => 'text-end'],
                        ],
                        [
                            'attribute' => 'external_reference',
                            'label' => 'Reference',
                            'headerOptions' => ['class' => 'text-uppercase small fw-bold', 'style' => 'font-size: 11px; letter-spacing: 0.04em; color: var(--theme-muted); border: none;'],
                            'contentOptions' => ['class' => 'small text-muted font-monospace'],
                        ],
                        [
                            'label' => 'Terminal',
                            'value' => function ($model) {
                                return $model->device?->label ?? $model->device?->device_uid ?? '-';
                            },
                            'headerOptions' => ['class' => 'text-uppercase small fw-bold', 'style' => 'font-size: 11px; letter-spacing: 0.04em; color: var(--theme-muted); border: none;'],
                            'contentOptions' => ['class' => 'small text-muted'],
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-uppercase small fw-bold pe-4', 'style' => 'font-size: 11px; letter-spacing: 0.04em; color: var(--theme-muted); border: none;'],
                            'contentOptions' => ['class' => 'pe-4'],
                            'value' => function ($model) {
                                $isSuccess = $model->status === 'SUCCESS';
                                $icon = $isSuccess ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                                $bg = $isSuccess ? '#dcfce7' : '#fee2e2';
                                $fg = $isSuccess ? '#166534' : '#991b1b';
                                return Html::tag('span', "<i class='bi $icon me-1'></i>" . Html::encode($model->status), [
                                    'class' => 'badge rounded-pill',
                                    'style' => "background: $bg; color: $fg; font-weight: 600; font-size: 11px; padding: 0.4em 0.75em;",
                                ]);
                            },
                        ],
                    ],
                ]) ?>
            </div>

            <!-- Mobile: stacked transaction cards -->
            <div class="d-md-none">
                <?php $mobileModels = $dataProvider->getModels(); ?>
                <?php if (empty($mobileModels)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <?= !empty($searchQuery) ? 'No transactions match "' . Html::encode($searchQuery) . '".' : 'No canteen transactions found.' ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($mobileModels as $model):
                        $schoolName = $model->student?->school?->name ?? null;
                        $studentName = $model->student?->name ?? 'Unknown';
                        $classLevel = $model->student?->class_level ?? '';
                        $terminal = $model->device?->label ?? $model->device?->device_uid ?? '-';
                        $isSuccess = $model->status === 'SUCCESS';
                        $statusIcon = $isSuccess ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                        $statusBg = $isSuccess ? '#dcfce7' : '#fee2e2';
                        $statusFg = $isSuccess ? '#166534' : '#991b1b';
                    ?>
                        <div class="kora-canteen-card border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-semibold" style="color: var(--theme-text);"><?= Html::encode($studentName) ?></div>
                                    <?php if ($classLevel): ?>
                                        <div class="text-muted" style="font-size: 11px;"><?= Html::encode($classLevel) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="fw-bold text-nowrap" style="color: var(--theme-text);">UGX <?= number_format((float) $model->amount, 0) ?></div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <?php if ($schoolName): ?>
                                    <span class="badge rounded-pill" style="background: var(--theme-soft); color: var(--theme-primary); font-weight: 600; font-size: 11px;"><?= Html::encode($schoolName) ?></span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-light text-muted" style="font-size: 11px;">Unassigned</span>
                                <?php endif; ?>
                                <span class="badge rounded-pill" style="background: <?= $statusBg ?>; color: <?= $statusFg ?>; font-weight: 600; font-size: 11px; padding: 0.4em 0.75em;">
                                    <i class="bi <?= $statusIcon ?> me-1"></i><?= Html::encode($model->status) ?>
                                </span>
                            </div>

                            <div class="small text-muted mb-1"><?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at)) ?></div>
                            <div class="small text-muted mb-1">Terminal: <?= Html::encode($terminal) ?></div>
                            <div class="small text-muted font-monospace">Ref: <?= Html::encode($model->external_reference) ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="px-3 py-3">
                        <?= LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'options' => ['class' => 'pagination pagination-sm mb-0 flex-wrap justify-content-center'],
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php Pjax::end(); ?>
        </div>
    </div>

    </div>
</div>

<style>
    .kora-card-contained {
        overflow: hidden;
    }
    .kora-canteen-card:last-child {
        border-bottom: none !important;
    }
</style>