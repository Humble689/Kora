<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = $rollover->term_label . ' - Transactions';
?>
<div class="py-4 bg-light min-vh-100">
    <div class="container" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 fw-bold mb-1"><?= Html::encode($rollover->term_label) ?></h1>
                <p class="text-muted small mb-0">
                    <?= date('M j, Y', strtotime($window['start'])) ?> - <?= date('M j, Y', strtotime($window['end'])) ?>
                </p>
            </div>
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i> Back to Term History', ['site/term-history'], ['class' => 'btn btn-outline-secondary btn-sm rounded-3']) ?>
        </div>

        <?= Html::beginForm(['site/term-transactions', 'id' => $rollover->id], 'get', ['class' => 'mb-3']) ?>
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search student, type, reference..." value="<?= Html::encode($searchQuery) ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        <?= Html::endForm() ?>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0 small'],
                'layout' => "{items}\n<div class='p-3'>{pager}</div>",
                'columns' => [
                    ['attribute' => 'created_at', 'label' => 'Date', 'value' => fn($m) => date('Y-m-d H:i', strtotime($m->created_at))],
                    ['attribute' => 'student.name', 'label' => 'Student', 'value' => fn($m) => $m->student->name ?? '-'],
                    'transaction_type',
                    'payment_channel',
                    ['attribute' => 'amount', 'label' => 'Amount', 'value' => fn($m) => 'UGX ' . number_format((float) $m->amount, 0)],
                    'status',
                ],
            ]) ?>
        </div>
    </div>
</div>