<?php
use yii\helpers\Html;
$this->title = 'Term Report - ' . $rollover->term_label;
?>
<style>
body { font-family: Arial, sans-serif; padding: 30px; color: #111; }
h1 { font-size: 20px; margin-bottom: 4px; }
.meta { color: #666; font-size: 13px; margin-bottom: 8px; }
.note { background: #fef9c3; border: 1px solid #eab308; padding: 10px 14px; font-size: 12px; border-radius: 6px; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #ddd; }
th { background: #f5f5f5; text-transform: uppercase; font-size: 11px; letter-spacing: .04em; }
.right { text-align: right; }
</style>

<h1><?= Html::encode($rollover->term_label) ?></h1>
<div class="meta">
    Started <?= date('F j, Y', strtotime($rollover->created_at)) ?>
    &middot; <?= $rollover->students_billed ?> students
    &middot; UGX <?= number_format((float) $rollover->base_tuition_fees, 0) ?> per student
    &middot; Status: <?= Html::encode($rollover->status) ?>
</div>

<?php if ($hasDetails): ?>
    <table>
        <thead>
            <tr><th>#</th><th>Student</th><th>Payment Code</th><th class="right">Amount Billed</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rollover->details as $i => $detail): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= Html::encode($detail->student->name ?? 'Unknown') ?></td>
                    <td><?= Html::encode($detail->student->payment_code ?? '-') ?></td>
                    <td class="right">UGX <?= number_format((float) $detail->amount_billed, 0) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="note">
        Detailed per-student billing wasn't recorded for this term. Showing all transactions logged during this term's date range instead.
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Date</th><th>Type</th><th>Reference</th><th class="right">Amount</th></tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
                <tr><td colspan="5">No transactions were recorded during this term's date range.</td></tr>
            <?php else: ?>
                <?php foreach ($transactions as $i => $tx): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($tx->created_at)) ?></td>
                        <td><?= Html::encode($tx->transaction_type) ?></td>
                        <td><?= Html::encode($tx->external_reference) ?></td>
                        <td class="right">UGX <?= number_format((float) $tx->amount, 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>window.print();</script>