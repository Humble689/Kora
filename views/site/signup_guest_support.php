<?php
/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'KORA Portal-Institutional Onboarding Desk';
?>

<div class="site-signup-support bg-light py-5 min-vh-100 d-flex align-items-center">
    <div class="container" style="max-width: 550px;">
        <div class="card shadow-sm border-0 rounded-4 p-4 text-center bg-white">
            
            <div class="mb-3 text-warning display-4">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            
            <h1 class="h4 fw-bold text-dark mb-2">Secure Merchant Gateway</h1>
            <p class="text-muted small px-3">Public registration is disabled. Staff accounts must be provisioned internally by an authorized School Master Administrator.</p>
            
            <div class="alert alert-secondary border-0 p-3 my-4 rounded-3 bg-white text-start small">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-building-add text-primary me-1"></i> Want to onboard your school?</h6>
                <p class="text-secondary mb-0">To integrate your campus currency wallets, tuition payment codes, and UNEB grading ledger engines, contact our system operations team directly:</p>
                <div class="mt-3  text-dark fw-bold border-top pt-2">
                    <a href="mailto:marktravis689@gmail.com" class="text-decoration-none">
                        <i class="bi bi-envelope-at-fill text-secondary me-1"></i>
                        marktravis689@gmail.com
                    </a><br>            
                           <i class="bi bi-telephone-fill text-secondary me-1"></i> +256 761 091 666
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <?= Html::a('<i class="bi bi-box-arrow-in-right me-1"></i> Open Staff Login Workspace', ['site/login'], ['class' => 'btn btn-primary fw-bold rounded-3 py-2 shadow-sm']) ?>
                <?= Html::a('Return to Parent Portal Search', ['site/index'], ['class' => 'btn btn-link text-decoration-none small text-muted']) ?>
            </div>

        </div>
    </div>
</div>
