<?php

declare(strict_types=1);

use yii\bootstrap5\Html;

$isGuest = Yii::$app->user->isGuest;
$role = !$isGuest ? Yii::$app->user->identity->role : null;
$username = !$isGuest ? Yii::$app->user->identity->username : '';
?>

<nav class="site-nav shadow-sm">
    <div class="container">
        <div class="menu-bg-wrap py-2">
            <div class="site-navigation">
                <div class="row g-0 align-items-center">
                    
                    <div class="col-3 col-lg-2">
                        <a href="<?= Yii::$app->homeUrl ?>" class="logo m-0 float-start text-decoration-none">
                            KORA<span class="text-primary">.</span>
                        </a>
                    </div>
                    
                    <div class="col-6 col-lg-8 text-center">
                        <ul class="js-clone-nav d-none d-lg-inline-block text-start site-menu mx-auto mb-0 list-unstyled d-flex align-items-center justify-content-center gap-4">
                            
                            <li class="active"><a href="<?= \yii\helpers\Url::toRoute(['/site/index']) ?>" class="text-decoration-none">Home</a></li>
                            
                            <?php if (!$isGuest && $role === 'SUPER_ADMIN'): ?>
                                <li><a href="<?= \yii\helpers\Url::toRoute(['/site/super-admin']) ?>" class="text-warning text-decoration-none">SaaS Master Registry</a></li>
                            <?php endif; ?>

                            <?php if (!$isGuest && $role === 'BURSAR'): ?>
                                <li><a href="<?= \yii\helpers\Url::toRoute(['/site/bursar']) ?>" class="text-info text-decoration-none">Bursar Dashboard</a></li>
                            <?php endif; ?>

                            <?php if (!$isGuest && $role === 'CANTEEN'): ?>
                                <li><a href="<?= \yii\helpers\Url::toRoute(['/site/canteen-terminal']) ?>" class="text-success text-decoration-none">Canteen Counter POS</a></li>
                            <?php endif; ?>

                            <?php if ($isGuest): ?>
                                <li class="position-relative style-dropdown-parent">
                                    <a href="#" class="text-decoration-none custom-dropdown-trigger" onclick="const m = this.nextElementSibling; m.classList.toggle('d-none'); event.stopPropagation();">
                                        Staff Access <i class="bi bi-chevron-down small ms-1"></i>
                                    </a>
                                    <ul class="position-absolute bg-white rounded-3 shadow border-0 p-2 text-start d-none custom-menu-box list-unstyled" style="z-index: 999; top: 100%; min-width: 180px; left: 0;">
                                        <li class="mb-1"><a href="<?= \yii\helpers\Url::toRoute(['/site/login']) ?>" class="dropdown-item px-3 py-2 text-dark fw-semibold rounded-2"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i> Log In</a></li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li><a href="<?= \yii\helpers\Url::toRoute(['/site/signup']) ?>" class="dropdown-item px-3 py-2 text-dark fw-semibold rounded-2"><i class="bi bi-person-plus-fill me-2 text-success"></i> Register Staff</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="col-3 col-lg-2 text-end d-flex align-items-center justify-content-end gap-3">
                        
                        <?php if (!$isGuest): ?>
                            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline m-0']) ?>
                            <?= Html::submitButton(
                                'Logout (' . Html::encode($username) . ')',
                                ['class' => 'btn btn-sm btn-danger px-3 py-1.5 fw-bold rounded-pill text-xs shadow-sm']
                            ) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <a href="<?= \yii\helpers\Url::toRoute(['/site/index']) ?>" class="call-us d-flex align-items-center text-decoration-none">
                                <span class="icon-phone me-1"></span>
                                <span>Parent Portal</span>
                            </a>
                        <?php endif; ?>

                        <!-- Standard Burger Menu for Mobile Devices -->
                        <a href="#" class="burger ms-auto float-end site-menu-toggle js-menu-toggle d-inline-block d-lg-none light text-decoration-none">
                            <span></span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.custom-menu-box a:hover {
    background-color: #f8f9fa !important;
    color: #0d6efd !important;
}
.style-dropdown-parent a {
    cursor: pointer;
}
</style>

<script>
// Window close interceptor to keep dashboard dropdown blocks handling cleanly
window.addEventListener('click', function(){
    document.querySelectorAll('.custom-menu-box').forEach(el => el.classList.add('d-none'));
});
</script>
