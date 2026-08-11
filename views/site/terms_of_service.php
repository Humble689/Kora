<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

$this->title = 'KORA Terms of Service';
?>

<div class="site-legal-page py-5 bg-light min-vh-100">
    <div class="container" style="max-width: 900px;">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h1 class="h3 fw-bold mb-3"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted mb-4">Effective date: August 10, 2026</p>

            <p>Welcome to KORA. These Terms of Service govern your use of the KORA platform, including school payment features, student wallet services, and institutional dashboards.</p>

            <h2 class="h5 fw-bold mt-4">1. Acceptance of Terms</h2>
            <p>By accessing or using KORA, you agree to be bound by these Terms. If you do not agree, please do not use the platform.</p>

            <h2 class="h5 fw-bold mt-4">2. User Accounts</h2>
            <p>You are responsible for maintaining the confidentiality of your login credentials and for all activity under your account. You must provide accurate information and promptly update account details when they change.</p>

            <h2 class="h5 fw-bold mt-4">3. Permitted Use</h2>
            <p>You agree to use KORA only for lawful educational and financial management purposes. You may not misuse the platform, interfere with services, or attempt unauthorized access to any account, network, or data.</p>

            <h2 class="h5 fw-bold mt-4">4. Payments and Wallet Transactions</h2>
            <p>All tuition and wallet transactions recorded in KORA are subject to your institution's policies. Users must verify transaction details before submission. KORA is not responsible for errors caused by incorrect input provided by users.</p>

            <h2 class="h5 fw-bold mt-4">5. Data and Records</h2>
            <p>KORA stores operational records such as payment history, student balances, and account actions to support platform functionality, reporting, and audit requirements.</p>

            <h2 class="h5 fw-bold mt-4">6. Service Availability</h2>
            <p>We aim to keep KORA available and reliable, but uninterrupted access is not guaranteed. Maintenance, updates, or technical issues may cause temporary service interruptions.</p>

            <h2 class="h5 fw-bold mt-4">7. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, KORA is provided on an "as is" and "as available" basis. We are not liable for indirect, incidental, or consequential damages arising from platform use.</p>

            <h2 class="h5 fw-bold mt-4">8. Changes to These Terms</h2>
            <p>We may update these Terms from time to time. Continued use of KORA after updates means you accept the revised Terms.</p>

            <h2 class="h5 fw-bold mt-4">9. Contact</h2>
            <p>If you have questions about these Terms, please contact your institution administrator or platform support desk.</p>

            <div class="mt-4 pt-3 border-top">
                <?= Html::a('Back to Login', ['site/login'], ['class' => 'btn btn-outline-dark btn-sm']) ?>
            </div>
        </div>
    </div>
</div>
