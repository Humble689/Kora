<?php

use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\StudentLookup $model */

$this->title = 'KORA - Smart Parent Portal';

$this->registerLinkTag(['rel' => 'preconnect', 'href' => 'https://fonts.googleapis.com']);
$this->registerLinkTag(['rel' => 'preconnect', 'href' => 'https://fonts.gstatic.com', 'crossorigin' => true]);
$this->registerCssFile('https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&display=swap');

$this->registerCssFile('@web/fonts/icomoon/style.css');
$this->registerCssFile('@web/fonts/flaticon/font/flaticon.css');
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css');
$this->registerCssFile('@web/css/tiny-slider.css');
$this->registerCssFile('@web/css/aos.css');
$this->registerCssFile('@web/css/glightbox.min.css');
$this->registerCssFile('@web/css/flatpickr.min.css');
$this->registerCssFile('@web/css/style.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$this->registerJsFile('@web/js/bootstrap.bundle.min.js', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile('@web/js/tiny-slider.js', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile('@web/js/flatpickr.min.js', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile('@web/js/aos.js', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile('@web/js/glightbox.min.js', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile('@web/js/navbar.js', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile('@web/js/counter.js', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile('@web/js/custom.js', ['depends' => [\yii\web\JqueryAsset::class], 'position' => \yii\web\View::POS_END]);
?>

<div class="site-mobile-menu site-navbar-target">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close">
            <span class="icofont-close js-menu-toggle"></span>
        </div>
    </div>
    <div class="site-mobile-menu-body"></div>
</div>

<?= $this->render('_navibar') ?>

<div class="hero overlay" id="verification">
    <img src="<?= Yii::getAlias('@web/images/blob.svg') ?>" alt="" class="img-fluid blob">
    <div class="container">
        <div class="row align-items-center justify-content-between pt-5">
            <div class="col-lg-6 text-center text-lg-start pe-lg-5" data-aos="fade-up">
                <h1 class="heading text-white mb-3">Pay School Fees and Wallet Topups in Minutes</h1>
                <p class="text-white mb-4">Use your child payment code to verify the student profile and securely pay tuition or load pocket money without logging in.</p>

                <div class="bg-white rounded p-4 shadow-sm mb-4">
                    <?php $form = ActiveForm::begin([
                        'id' => 'lookupForm',
                        'action' => ['site/lookup'],
                        'options' => ['onsubmit' => 'executeLookup(event)'],
                    ]); ?>

                        <div class="mb-3">
                            <?= $form->field($model, 'payment_code')->textInput([
                                'id' => 'paymentCodeInput',
                                'maxlength' => 10,
                                'class' => 'form-control form-control-lg font-monospace fw-bold text-center',
                                'placeholder' => 'Enter 10-digit payment code',
                                'autocomplete' => 'off',
                            ])->label('Student Payment Code', ['class' => 'form-label fw-semibold text-secondary']) ?>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <span>Verify Student Account</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="img-wrap">
                    <img src="<?= Yii::getAlias('@web/images/img-1.jpg') ?>" alt="Parent payment dashboard" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section pt-4">
    <div class="container">
        <div id="financialInterface" class="d-none">
            <div class="alert alert-success border-0 rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>Student file localized successfully.</div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 bg-white">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Student File</h5>
                <div class="row g-2 small">
                    <div class="col-5 text-muted">Full Name:</div>
                    <div class="col-7 fw-bold text-end text-dark" id="displayProfileName">-</div>

                    <div class="col-5 text-muted">Institution:</div>
                    <div class="col-7 fw-bold text-end text-dark text-truncate" id="displaySchoolName">-</div>

                    <div class="col-5 text-muted">Class/Level:</div>
                    <div class="col-7 fw-bold text-end text-dark" id="displayClassLevel">-</div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="bg-dark p-2">
                    <nav class="nav nav-pills nav-fill" id="serviceTabs" role="tablist">
                        <button class="nav-link active fw-bold py-2 rounded-3" id="tuition-tab" data-bs-toggle="tab" data-bs-target="#tuition-panel" type="button" role="tab">
                            <i class="bi bi-wallet2 me-1"></i> Tuition
                        </button>
                        <button class="nav-link fw-bold py-2 rounded-3" id="swallet-tab" data-bs-toggle="tab" data-bs-target="#swallet-panel" type="button" role="tab">
                            <i class="bi bi-piggy-bank me-1"></i> S-Wallet
                        </button>
                    </nav>
                </div>

                <div class="card-body p-4 bg-white tab-content" id="serviceTabsContent">
                    <div class="tab-pane fade show active" id="tuition-panel" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3 mb-4 border">
                            <span class="text-muted fw-semibold small">Outstanding Balance:</span>
                            <span class="fs-4 fw-black text-danger font-monospace">UGX <span id="displayTuitionBalance">0</span></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Amount to Deposit (UGX)</label>
                            <input type="number" id="tuitionInputAmount" class="form-control form-control-lg font-monospace fw-bold" min="1000">
                        </div>
                        <button onclick="processSimulatedPayment('TUITION')" class="btn btn-success btn-lg w-100 rounded-3 fw-bold shadow-sm">
                            Authorize Tuition Payment
                        </button>
                    </div>

                    <div class="tab-pane fade" id="swallet-panel" role="tabpanel">
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted fw-semibold small">Pocket Money Wallet:</span>
                                <span class="fs-5 fw-bold text-success font-monospace">UGX <span id="displaySwalletBalance">0</span></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2 text-muted">
                                <span>Daily Spending Limit Cap:</span>
                                <span class="fw-bold text-dark font-monospace">UGX <span id="displaySpendLimit">0</span></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Top-up Value (UGX)</label>
                            <input type="number" id="swalletInputAmount" class="form-control form-control-lg font-monospace fw-bold" min="500">
                        </div>
                        <button onclick="processSimulatedPayment('POCKET_MONEY')" class="btn btn-success btn-lg w-100 rounded-3 fw-bold shadow-sm">
                            Load Pocket Money Wallet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section" id="services">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-7 mb-4 mb-lg-0" data-aos="fade-up">
                <img src="<?= Yii::getAlias('@web/images/img-3.jpg') ?>" alt="Image" class="img-fluid rounded">
            </div>
            <div class="col-lg-4 ps-lg-2" data-aos="fade-up" data-aos-delay="100">
                <div class="mb-5">
                    <h2 class="text-black h4">Make payment fast and smooth.</h2>
                    <p>Parents can verify, pay, and track balances from one secure public portal while protected admin workspaces remain behind authentication.</p>
                </div>
                <div class="d-flex mb-3 service-alt">
                    <div>
                        <span class="bi-wallet-fill me-4"></span>
                    </div>
                    <div>
                        <h3>Tuition Collections</h3>
                        <p>Clear outstanding balances through guided payment actions tied directly to student records.</p>
                    </div>
                </div>

                <div class="d-flex mb-3 service-alt">
                    <div>
                        <span class="bi-pie-chart-fill me-4"></span>
                    </div>
                    <div>
                        <h3>Pocket Money Control</h3>
                        <p>Top-up school wallet balances and maintain daily spend guardrails for student spending.</p>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="section sec-features">
    <div class="container">
        <div class="row g-5">
            <div class="col-12 col-sm-6 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                <div class="feature d-flex">
                    <span class="bi-bag-check-fill"></span>
                    <div>
                        <h3>Parent-First Experience</h3>
                        <p>A frictionless public flow designed for families, guardians, and sponsors handling school payments quickly.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature d-flex">
                    <span class="bi-wallet-fill"></span>
                    <div>
                        <h3>Real-Time Balances</h3>
                        <p>Live tuition and wallet data are displayed after verification through secured AJAX communication.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature d-flex">
                    <span class="bi-pie-chart-fill"></span>
                    <div>
                        <h3>Role Separation</h3>
                        <p>Super Admin, Bursar, and Canteen POS modules remain isolated behind strict login gates.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section" id="insights">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 order-lg-2 mb-4 mb-lg-0" data-aos="fade-up">
                <img src="<?= Yii::getAlias('@web/images/img-2.jpg') ?>" alt="Image" class="img-fluid">
            </div>
            <div class="col-lg-5 pe-lg-5" data-aos="fade-up" data-aos-delay="100">
                <div class="mb-5">
                    <h2 class="text-black h4">Straight-forward school financing</h2>
                </div>
                <div class="d-flex mb-3 service-alt">
                    <div>
                        <span class="bi-wallet-fill me-4"></span>
                    </div>
                    <div>
                        <h3>Instant Parent Lookup</h3>
                        <p>Payment-code verification opens student billing details without redirecting to a separate page.</p>
                    </div>
                </div>

                <div class="d-flex mb-3 service-alt">
                    <div>
                        <span class="bi-pie-chart-fill me-4"></span>
                    </div>
                    <div>
                        <h3>Low-Friction Checkout</h3>
                        <p>Submit tuition or S-Wallet topups from the same workspace immediately after validation.</p>
                    </div>
                </div>

                <div class="d-flex mb-3 service-alt">
                    <div>
                        <span class="bi-bag-check-fill me-4"></span>
                    </div>
                    <div>
                        <h3>Audit Friendly Flow</h3>
                        <p>Backend processing and role permissions stay untouched while the front-end experience is modernized.</p>
                    </div>h
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section sec-cta overlay" style="background-image: url('<?= Yii::getAlias('@web/images/img-3.jpg') ?>')">
    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="0">
                <h2 class="heading">Need help with a payment code?</h2>
                <p>Parents can begin with the verification field above. Administrative teams continue to work through authenticated dashboards only.</p>
            </div>
            <div class="col-lg-5 text-end" data-aos="fade-up" data-aos-delay="100">
                <a href="#verification" class="btn btn-outline-white-reverse">Verify Student Account</a>
            </div>
        </div>
    </div>
</div>

<div class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="widget">
                    <h3>About</h3>
                    <p>SchoolPay helps parents pay tuition and student spending balances from one streamlined portal.</p>
                </div>
                <div class="widget">
                    <address>SchoolPay Financial Platform</address>
                    <ul class="list-unstyled links">
                        <li><a href="#verification">Parent Portal</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#insights">Insights</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="widget">
                    <h3>Platform</h3>
                    <ul class="list-unstyled float-start links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#services">Collections</a></li>
                        <li><a href="#insights">Wallets</a></li>
                    </ul>
                    <ul class="list-unstyled float-start links">
                        <li><a href="#">Security</a></li>
                        <li><a href="#">Compliance</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="widget">
                    <h3>Social</h3>
                    <ul class="list-unstyled social">
                        <li><a href="#"><span class="icon-instagram"></span></a></li>
                        <li><a href="#"><span class="icon-twitter"></span></a></li>
                        <li><a href="#"><span class="icon-facebook"></span></a></li>
                        <li><a href="#"><span class="icon-linkedin"></span></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <p>Copyright &copy;<script>document.write(new Date().getFullYear());</script>. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</div>

<div id="overlayer"></div>
<div class="loader">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script>
function executeLookup(event) {
    event.preventDefault();
    
    const form = document.getElementById('lookupForm');
    const submitBtn = document.getElementById('submitBtn');
    const interfaceContainer = document.getElementById('financialInterface');
    
    const inputCodeValue = document.getElementById('paymentCodeInput').value.trim();

    submitBtn.disabled = false;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> <span>Verifying...</span>`;

    const params = new URLSearchParams();
    params.append('StudentLookup[payment_code]', inputCodeValue);
    params.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->getCsrfToken() ?>');

    fetch(form.action, {
        method: 'POST',
        body: params,
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest' 
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned HTTP status code ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span>Verify Student Account</span> <i class="bi bi-arrow-right"></i>`;

        if (data.success) {
            document.getElementById('displayProfileName').innerText = data.name;
            document.getElementById('displaySchoolName').innerText = data.school_name;
            document.getElementById('displayClassLevel').innerText = data.class_level;
            document.getElementById('displayTuitionBalance').innerText = data.tuition_balance;
            document.getElementById('displaySwalletBalance').innerText = data.swallet_balance;
            document.getElementById('displaySpendLimit').innerText = data.daily_spend_limit;

            const existingBtn = document.getElementById('dynamicDashboardLinkBtn');
            if (existingBtn) { existingBtn.remove(); }

            const dashboardLinkBtn = `<a id="dynamicDashboardLinkBtn" href="${window.location.origin}/site/student-dashboard?code=${inputCodeValue}" class="btn btn-outline-dark btn-sm w-100 mt-3 rounded-3 fw-bold"><i class="bi bi-journal-text me-1"></i> Open Official Statement Dashboard</a>`;
            document.getElementById('displayClassLevel').insertAdjacentHTML('afterend', dashboardLinkBtn);
            
            // Set input defaults matching current balances
            document.getElementById('tuitionInputAmount').value = data.tuition_balance.replace(/,/g, '');
            document.getElementById('swalletInputAmount').value = 20000; // Generic placeholder topup

            // Reveal hidden workspace panels smoothly
            interfaceContainer.classList.remove('d-none');
            interfaceContainer.scrollIntoView({ behavior: 'smooth' });
        } else {
            interfaceContainer.classList.add('d-none');
            alert(data.message);
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span>Verify Student Account</span> <i class="bi bi-arrow-right"></i>`;
        alert("System Verification Error: " + error.message);
    });
}

function processSimulatedPayment(type) {
    const code = document.getElementById('paymentCodeInput').value;
    const amountInput = type === 'TUITION' 
        ? document.getElementById('tuitionInputAmount') 
        : document.getElementById('swalletInputAmount');
    
    const amount = amountInput.value;

    if (!amount || amount <= 0) {
        alert("Please enter a valid monetary execution value.");
        return;
    }

    if (!confirm(`Are you sure you want to process this payment of UGX ${Number(amount).toLocaleString()}?`)) {
        return;
    }

    const params = new URLSearchParams();
    params.append('payment_code', code);
    params.append('amount', amount);
    params.append('type', type);
    params.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->getCsrfToken() ?>');

    fetch('<?= \yii\helpers\Url::toRoute(['site/process-payment']) ?>', {
        method: 'POST',
        body: params,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned HTTP status code ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            
            document.getElementById('displayTuitionBalance').innerText = data.new_tuition;
            document.getElementById('displaySwalletBalance').innerText = data.new_swallet;
            
            if(type === 'TUITION') {
                document.getElementById('tuitionInputAmount').value = data.new_tuition.replace(/,/g, '');
            } else {
                amountInput.value = '';
            }
        } else {
            alert("Payment Processing Failed: " + data.message);
        }
    })
    .catch(error => {
        alert("Network clearing communications channel failure: " + error.message);
    });
}
</script>



<style>
    #main > .container, 
    #main > .container-fluid {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0 !important;
        max-width: 100% !important;
    }
    
    #verification {
        margin-left: 0 !important;
        margin-top: 0 !important;
        margin-right: 0 !important;
        width: 100% !important;
        min-height: 90vh;
        display: flex;
        align-items: center;
        padding-top: 80px !important; 
        padding-bottom: 60px !important;
    }

    .blob {
        position: absolute;
        z-index: 1;
        opacity: 0.1;
    }
    
    .container {
        position: relative;
        z-index: 2;
    }
</style>
