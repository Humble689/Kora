<?php

declare(strict_types=1);

/** @var app\models\Transactions $tx */

use yii\bootstrap5\Html;

$school = $tx->student->school ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt — <?= Html::encode($tx->external_reference) ?></title>
    <style>
        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { margin: 0; }
        }
        body {
            font-family: 'Courier New', monospace;
            width: 78mm;
            margin: 0 auto;
            padding: 8px;
            font-size: 12px;
            color: #000;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .school-name { font-size: 14px; font-weight: bold; }
        .footer { margin-top: 10px; font-size: 10px; }
        .no-print { text-align: center; margin-top: 12px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print();">

    <div class="center">
        <div class="school-name"><?= $school ? Html::encode($school->name) : 'School' ?></div>
        <div>Official Payment Receipt</div>
    </div>

    <div class="line"></div>

    <table>
        <tr><td>Date</td><td class="right"><?= date('Y-m-d H:i', strtotime($tx->created_at)) ?></td></tr>
        <tr><td>Receipt No.</td><td class="right"><?= Html::encode($tx->external_reference) ?></td></tr>
        <tr><td>Student</td><td class="right"><?= $tx->student ? Html::encode($tx->student->name) : 'Unknown' ?></td></tr>
        <?php if ($tx->student && $tx->student->payment_code): ?>
        <tr><td>Payment Code</td><td class="right"><?= Html::encode($tx->student->payment_code) ?></td></tr>
        <?php endif; ?>
        <tr><td>Type</td><td class="right"><?= Html::encode($tx->transaction_type) ?></td></tr>
        <tr><td>Channel</td><td class="right"><?= Html::encode($tx->payment_channel) ?></td></tr>
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td class="bold">Amount Paid</td>
            <td class="right bold">UGX <?= number_format((float)$tx->amount, 0) ?></td>
        </tr>
    </table>

    <div class="line"></div>

    <?php if ($tx->transaction_type === 'TUITION' && $tx->student): ?>
        <table>
            <tr><td>Balance After</td><td class="right">UGX <?= number_format((float)$tx->student->tuition_balance, 0) ?></td></tr>
        </table>
        <div class="line"></div>
    <?php endif; ?>

    <div class="center footer">
        Thank you for your payment.<br>
        Printed by: <?= Html::encode(Yii::$app->user->identity->username ?? 'Bursar') ?><br>
        <?= date('Y-m-d H:i:s') ?>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Print</button>
    </div>

</body>
</html>