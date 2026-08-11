<?php
/** @var yii\web\View $this */
/** @var app\models\Students $student */

$schoolName = $student->school ? $student->school->name : 'N/A';
$tuitionBalance = number_format((float)$student->tuition_balance, 0);
$swalletBalance = number_format((float)$student->swallet_balance, 0);
?>

<div style="font-family: sans-serif; color: #333; padding-top: 10px;">
    
    <div style="text-align: center; border-bottom: 2px solid #222; padding-bottom: 10px; margin-bottom: 25px;">
        <h2 style="margin: 0; text-transform: uppercase; color: #111; letter-spacing: 0.5px;">Transaction Statement</h2>
    </div>
    
    <table style="width: 100%; margin-bottom: 35px; font-size: 14px; border-collapse: collapse;">
        <tr>
            <td style="padding: 6px 0; font-weight: bold; width: 30%;">Student Name:</td>
            <td style="padding: 6px 0; color: #222;"><?= htmlspecialchars($student->name) ?></td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-weight: bold;">Institution:</td>
            <td style="padding: 6px 0; color: #222;"><?= htmlspecialchars($schoolName) ?></td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-weight: bold;">Class Level:</td>
            <td style="padding: 6px 0; color: #222;"><?= htmlspecialchars($student->class_level) ?></td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-weight: bold;">Payment Code:</td>
            <td style="padding: 6px 0; font-weight: bold; color: #000; font-size: 15px;"><?= htmlspecialchars($student->payment_code) ?></td>
        </tr>
    </table>

    <div style="background: #fcfcfc; border: 1px solid #ddd; padding: 18px; border-radius: 4px;">
        <table style="width: 100%; font-size: 15px; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee;">Current Tuition Balance:</td>
                <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #b12704; border-bottom: 1px solid #eee;">
                    UGX <?= $tuitionBalance ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">Current S-Wallet Balance:</td>
                <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #007600;">
                    UGX <?= $swalletBalance ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; font-size: 10px; color: #888; margin-top: 45px; border-top: 1px solid #eee; padding-top: 10px;">
        This document serves as an official accounting snapshot from the system records.
    </div>

</div>
