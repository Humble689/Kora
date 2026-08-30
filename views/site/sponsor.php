<?php
/** @var yii\web\View $this */
/** @var string $firstName */
/** @var string $classLevel */
/** @var string $sponsorCode */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Sponsor ' . $firstName;
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css');

$initial = strtoupper(substr($firstName, 0, 1));
?>
<style>
.sponsor-page {
    min-height: 100vh;
    background: linear-gradient(180deg, var(--theme-soft, #f0fdf4) 0%, #f7f8fa 55%);
    display: flex;
    align-items: center;
    padding: 3rem 1rem;
}
.sponsor-shell { max-width: 440px; margin: 0 auto; width: 100%; }

.sponsor-brand {
    text-align: center;
    margin-bottom: 1.5rem;
}
.sponsor-brand .brand-mark {
    font-weight: 800;
    font-size: 1.1rem;
    color: var(--theme-primary, #16a34a);
    letter-spacing: .02em;
}
.sponsor-brand .brand-sub {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #9ca3af;
    font-weight: 700;
}

.sponsor-card {
    background: #fff;
    border: 1px solid var(--theme-border, #eceef1);
    border-radius: 20px;
    padding: 2rem 1.75rem;
    box-shadow: 0 10px 30px rgba(16, 24, 40, .06);
}

.student-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--theme-primary, #16a34a), #22c55e);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.5rem;
    margin: 0 auto .9rem;
}
.student-name { text-align: center; font-weight: 800; font-size: 1.25rem; color: var(--theme-text, #111827); margin-bottom: .15rem; }
.student-meta { text-align: center; color: #9ca3af; font-size: .85rem; margin-bottom: 1.5rem; }

.amount-presets { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; margin-bottom: .9rem; }
.amount-chip {
    border: 1px solid var(--theme-border, #e5e7eb);
    background: #fafafa;
    color: var(--theme-text, #374151);
    font-weight: 700;
    font-size: .8rem;
    padding: .55rem .3rem;
    border-radius: 10px;
    cursor: pointer;
    transition: all .12s;
}
.amount-chip:hover { border-color: var(--theme-primary, #16a34a); }
.amount-chip.selected { background: var(--theme-primary, #16a34a); border-color: var(--theme-primary, #16a34a); color: #fff; }

.sponsor-field label { font-size: .78rem; font-weight: 700; color: var(--theme-muted, #6b7280); text-transform: uppercase; letter-spacing: .04em; margin-bottom: .35rem; display: block; }
.sponsor-field input {
    width: 100%;
    border: 1px solid var(--theme-border, #e5e7eb);
    border-radius: 12px;
    padding: .7rem .9rem;
    font-size: 1rem;
    font-weight: 700;
    background: #fafafa;
}
.sponsor-field input:focus { outline: none; border-color: var(--theme-primary, #16a34a); background: #fff; }
.sponsor-field { margin-bottom: 1.1rem; }

.btn-sponsor-send {
    width: 100%;
    background: var(--theme-primary, #16a34a);
    border: none;
    color: #fff;
    font-weight: 800;
    padding: .85rem;
    border-radius: 12px;
    font-size: .95rem;
    transition: opacity .12s;
}
.btn-sponsor-send:hover { opacity: .92; }
.btn-sponsor-send:disabled { opacity: .6; }

.trust-note {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    background: var(--theme-soft, #f9fafb);
    border-radius: 12px;
    padding: .75rem .9rem;
    margin-top: 1.1rem;
    font-size: .76rem;
    color: var(--theme-muted, #6b7280);
    line-height: 1.4;
}
.trust-note i { color: var(--theme-primary, #16a34a); margin-top: .1rem; }

.sponsor-result { margin-top: .9rem; }
.sponsor-success { text-align: center; padding: 1rem 0; }
.sponsor-success i { font-size: 2.5rem; color: var(--theme-primary, #16a34a); margin-bottom: .6rem; display: block; }
.sponsor-success h5 { font-weight: 800; color: var(--theme-text, #111827); }
.sponsor-success p { color: #9ca3af; font-size: .85rem; }
</style>

<div class="sponsor-page">
    <div class="sponsor-shell">

        <div class="sponsor-brand">
            <div class="brand-mark">KORA</div>
            <div class="brand-sub">Student Sponsorship</div>
        </div>

        <div class="sponsor-card" id="sponsorCardBody">
            <div class="student-avatar"><?= Html::encode($initial) ?></div>
            <div class="student-name"><?= Html::encode($firstName) ?></div>
            <div class="student-meta"><?= Html::encode($classLevel) ?> &middot; Canteen Wallet</div>

            <div id="sponsorFormBlock">
                <div class="sponsor-field">
                    <label>Choose an amount (UGX)</label>
                    <div class="amount-presets">
                        <button type="button" class="amount-chip" onclick="selectPreset(5000, this)">5,000</button>
                        <button type="button" class="amount-chip" onclick="selectPreset(10000, this)">10,000</button>
                        <button type="button" class="amount-chip" onclick="selectPreset(20000, this)">20,000</button>
                        <button type="button" class="amount-chip" onclick="selectPreset(50000, this)">50,000</button>
                    </div>
                    <input type="number" id="sponsorAmount" placeholder="Or enter a custom amount" min="500">
                </div>

                <div class="sponsor-field">
                    <label>Your name <span style="font-weight:500; text-transform:none;">(optional)</span></label>
                    <input type="text" id="sponsorNameInput" placeholder="Leave blank to give anonymously">
                </div>

                <button class="btn-sponsor-send" id="sponsorSubmitBtn" onclick="submitSponsorGift()">
                    <i class="bi bi-heart-fill me-1"></i> Send Gift
                </button>

                <div class="trust-note">
                    <i class="bi bi-shield-check"></i>
                    <div>Your gift is added directly to <?= Html::encode($firstName) ?>'s school canteen wallet and can only be spent at their school. No account or personal details are shared with the school beyond what you enter here.</div>
                </div>

                <div id="sponsorResult" class="sponsor-result"></div>
            </div>

            <div id="sponsorSuccessBlock" class="sponsor-success d-none">
                <i class="bi bi-check-circle-fill"></i>
                <h5>Gift Sent!</h5>
                <p id="sponsorSuccessMessage">Thank you for supporting <?= Html::encode($firstName) ?>.</p>
                <button class="btn-sponsor-send mt-2" style="background: var(--theme-muted, #6b7280);" onclick="resetSponsorForm()">Send Another Gift</button>
            </div>
        </div>
    </div>
</div>

<script>
function selectPreset(amount, btn) {
    document.getElementById('sponsorAmount').value = amount;
    document.querySelectorAll('.amount-chip').forEach(c => c.classList.remove('selected'));
    btn.classList.add('selected');
}

document.getElementById('sponsorAmount').addEventListener('input', () => {
    document.querySelectorAll('.amount-chip').forEach(c => c.classList.remove('selected'));
});

function submitSponsorGift() {
    const amount = document.getElementById('sponsorAmount').value;
    const resultBox = document.getElementById('sponsorResult');
    const btn = document.getElementById('sponsorSubmitBtn');

    if (!amount || amount <= 0) {
        resultBox.innerHTML = '<div class="alert alert-warning py-2 mb-0 small">Please choose or enter an amount.</div>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

    const params = new URLSearchParams();
    params.append('amount', amount);
    params.append('sponsor_name', document.getElementById('sponsorNameInput').value);
    params.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->getCsrfToken() ?>');

    fetch('<?= Url::toRoute(['site/sponsor-topup', 'code' => $sponsorCode]) ?>', {
        method: 'POST',
        body: params,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-heart-fill me-1"></i> Send Gift';

        if (data.success) {
            document.getElementById('sponsorFormBlock').classList.add('d-none');
            document.getElementById('sponsorSuccessBlock').classList.remove('d-none');
            document.getElementById('sponsorSuccessMessage').innerText = data.message;
        } else {
            resultBox.innerHTML = `<div class="alert alert-danger py-2 mb-0 small">${data.message}</div>`;
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-heart-fill me-1"></i> Send Gift';
        resultBox.innerHTML = '<div class="alert alert-danger py-2 mb-0 small">Something went wrong. Please try again.</div>';
    });
}

function resetSponsorForm() {
    document.getElementById('sponsorAmount').value = '';
    document.getElementById('sponsorNameInput').value = '';
    document.querySelectorAll('.amount-chip').forEach(c => c.classList.remove('selected'));
    document.getElementById('sponsorResult').innerHTML = '';
    document.getElementById('sponsorSuccessBlock').classList.add('d-none');
    document.getElementById('sponsorFormBlock').classList.remove('d-none');
}
</script>