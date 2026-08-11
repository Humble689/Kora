<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

$this->title = 'KORA Privacy Policy';
?>

<div class="site-legal-page py-5 bg-light min-vh-100">
    <div class="container" style="max-width: 900px;">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h1 class="h3 fw-bold mb-3"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted mb-4">Effective date: August 10, 2026</p>

            <p>KORA values your privacy. This Privacy Policy explains what information we collect, how we use it, and your choices when using the platform.</p>

            <h2 class="h5 fw-bold mt-4">1. Information We Collect</h2>
            <p>We may collect account information (such as usernames and roles), student records, payment transaction data, and system activity logs needed to operate and secure KORA.</p>

            <h2 class="h5 fw-bold mt-4">2. How We Use Information</h2>
            <p>We use collected information to provide services, process payments, maintain records, generate reports, improve platform reliability, and support authorized school operations.</p>

            <h2 class="h5 fw-bold mt-4">3. Data Sharing</h2>
            <p>KORA does not sell personal data. Information is shared only with authorized institutional users and service providers as necessary for operations, legal compliance, or security enforcement.</p>

            <h2 class="h5 fw-bold mt-4">4. Data Security</h2>
            <p>We apply reasonable administrative and technical safeguards to protect data from unauthorized access, disclosure, alteration, or destruction. No system can be guaranteed completely secure.</p>

            <h2 class="h5 fw-bold mt-4">5. Data Retention</h2>
            <p>We retain data for as long as needed to provide services, comply with legal obligations, resolve disputes, and enforce agreements.</p>

            <h2 class="h5 fw-bold mt-4">6. Your Rights</h2>
            <p>Depending on applicable law, you may request access, correction, or deletion of certain personal information. Requests should be directed to your school administrator or support desk.</p>

            <h2 class="h5 fw-bold mt-4">7. Policy Updates</h2>
            <p>We may update this Privacy Policy periodically. Material updates will be reflected by revising the effective date on this page.</p>

            <h2 class="h5 fw-bold mt-4">8. Contact</h2>
            <p>For privacy-related questions, please contact your institution administrator or platform support desk.</p>

            <div class="mt-4 pt-3 border-top">
                <?= Html::a('Back to Login', ['site/login'], ['class' => 'btn btn-outline-dark btn-sm']) ?>
            </div>
        </div>
    </div>
</div>
