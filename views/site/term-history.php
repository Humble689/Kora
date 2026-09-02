<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Term History';
?>
<style>
.term-page { background: #f7f8fa; min-height: 100vh; padding: 2.5rem 0; }
.term-container { max-width: 1000px; margin: 0 auto; padding: 0 1.5rem; }

.current-term-banner {
    background: linear-gradient(135deg, var(--theme-primary, #16a34a), #14532d);
    color: #fff;
    border-radius: 18px;
    padding: 1.75rem 2rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 10px 30px rgba(16, 24, 40, .1);
}
.current-term-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; opacity: .85; }
.current-term-name { font-size: 1.6rem; font-weight: 800; margin: .25rem 0 .35rem; }
.current-term-meta { font-size: .85rem; opacity: .85; }
.current-term-reversed { background: linear-gradient(135deg, #dc2626, #7f1d1d); }
.current-term-historical { background: linear-gradient(135deg, #6b7280, #374151); }

.term-card { background: #fff; border: 1px solid #eceef1; border-radius: 16px; overflow: hidden; }
.term-row { padding: 1.1rem 1.5rem; border-bottom: 1px solid #f1f2f4; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; }
.term-row:last-child { border-bottom: none; }
.term-row-label { font-weight: 700; color: #111827; }
.term-row-label a:hover { text-decoration: underline !important; }
.term-row-meta { font-size: .8rem; color: #9ca3af; }
.term-badge { font-size: .72rem; font-weight: 700; padding: .3rem .7rem; border-radius: 999px; }
.term-badge.active-badge { background: #ecfdf3; color: #16a34a; }
.term-badge.reversed-badge { background: #fef2f2; color: #dc2626; }
.term-badge.historical-badge { background: #f3f4f6; color: #6b7280; }
.empty-term { text-align: center; padding: 3rem 1rem; color: #9ca3af; }
</style>

<div class="term-page">
    <div class="term-container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 fw-bold mb-0">Term History</h1>
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i> Back', ['site/bursar'], ['class' => 'btn btn-outline-secondary btn-sm rounded-3']) ?>
        </div>

        <?php if ($currentTerm):
            $bannerClass = match ($currentTerm->status) {
                'ACTIVE' => '',
                'HISTORICAL' => 'current-term-historical',
                default => 'current-term-reversed',
            };
            $bannerEyebrow = match ($currentTerm->status) {
                'ACTIVE' => 'You Are Currently In',
                'HISTORICAL' => 'Most Recent Term On Record',
                default => 'Last Term Was Reversed — No Active Term',
            };
        ?>
            <div class="current-term-banner <?= $bannerClass ?>">
                <div class="current-term-eyebrow"><?= $bannerEyebrow ?></div>
                <div class="current-term-name"><?= Html::encode($currentTerm->term_label) ?></div>
                <div class="current-term-meta">
                    Started <?= date('F j, Y', strtotime($currentTerm->created_at)) ?>
                    &middot; <?= $currentTerm->students_billed ?> students billed
                    &middot; UGX <?= number_format((float) $currentTerm->base_tuition_fees, 0) ?> per student
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary">No term rollover has been recorded yet for this school.</div>
        <?php endif; ?>

        <div class="term-card">
            <?php if (empty($rollovers)): ?>
                <div class="empty-term">
                    <i class="bi bi-calendar3 fs-1 d-block mb-2"></i>
                    No term history yet.
                </div>
            <?php else: ?>
                <?php foreach ($rollovers as $r):
                    $badgeClass = match ($r->status) {
                        'ACTIVE' => 'active-badge',
                        'HISTORICAL' => 'historical-badge',
                        default => 'reversed-badge',
                    };
                    $badgeLabel = match ($r->status) {
                        'ACTIVE' => 'Active',
                        'HISTORICAL' => 'Historical',
                        default => 'Reversed',
                    };
                ?>
                    <div class="term-row">
                        <div>
                            <div class="term-row-label">
                                <?= Html::a(Html::encode($r->term_label), ['site/term-transactions', 'id' => $r->id], ['class' => 'text-decoration-none text-dark']) ?>
                            </div>
                            <div class="term-row-meta">
                                Started <?= date('M j, Y', strtotime($r->created_at)) ?>
                                by <?= Html::encode($r->performedBy->username ?? 'Unknown') ?>
                                &middot; <?= $r->students_billed ?> students &middot; UGX <?= number_format((float) $r->base_tuition_fees, 0) ?>
                                <?php if ($r->status === 'REVERSED'): ?>
                                    <br>Reversed <?= date('M j, Y', strtotime($r->reversed_at)) ?> by <?= Html::encode($r->reversedBy->username ?? 'Unknown') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="term-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                            <?= Html::a('<i class="bi bi-printer"></i>', ['site/print-term-report', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank', 'title' => 'Print report']) ?>
                            <?php if ($r->id === $mostRecentActiveId): ?>
                                <?= Html::beginForm(['site/reverse-term-rollover', 'id' => $r->id], 'post', ['class' => 'm-0', 'onsubmit' => "return confirm('Reverse " . Html::encode(addslashes($r->term_label)) . "? This will subtract the billed amount from every affected student\\'s tuition balance.');"]) ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Reverse this term">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>