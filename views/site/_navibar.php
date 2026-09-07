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

                            <?php if (!$isGuest && $role === 'SCHOOL_ADMIN'): ?>
                                <li><a href="<?= \yii\helpers\Url::toRoute(['/site/school-admin']) ?>" class="text-white text-decoration-none fw-bold">School Admin Panel</a></li>
                            <?php endif; ?>

                            <?php if (!$isGuest && $role === 'DOS'): ?>
                                <li><a href="<?= \yii\helpers\Url::toRoute(['/site/dos-review']) ?>" class="text-white text-decoration-none">D.O.S. Moderation Desk</a></li>
                            <?php endif; ?>

                            <?php if (!$isGuest && $role === 'TEACHER'): ?>
                                <li><a href="<?= \yii\helpers\Url::toRoute(['/site/teacher-grading']) ?>" class="text-white text-decoration-none">Teacher Terminal</a></li>
                            <?php endif; ?>

                            <?php if (!$isGuest && $role === 'BURSAR'): ?>
                                <li><a href="<?= \yii\helpers\Url::toRoute(['/site/bursar']) ?>" class="text-white text-decoration-none">Bursar Dashboard</a></li>
                            <?php endif; ?>

                            <?php if (!$isGuest && $role === 'CANTEEN'): ?>
                                <li><a href="<?= \yii\helpers\Url::toRoute(['/site/canteen-terminal']) ?>" class="text-white text-decoration-none">Canteen Counter POS</a></li>
                            <?php endif; ?>

                            <?php if ($isGuest): ?>
                                <li class="position-relative style-dropdown-parent">
                                    <a href="#" class="text-decoration-none custom-dropdown-trigger" onclick="const m = this.nextElementSibling; m.classList.toggle('d-none'); event.stopPropagation();">
                                        Staff Access <i class="bi bi-chevron-down small ms-1"></i>
                                    </a>
                                    <ul class="position-absolute bg-white rounded-3 shadow border-0 p-2 text-start d-none custom-menu-box list-unstyled" style="z-index: 999; top: 100%; min-width: 180px; left: 0;">
                                        <li class="mb-1"><a href="<?= \yii\helpers\Url::toRoute(['/site/login']) ?>" class="dropdown-item px-3 py-2 text-dark fw-semibold rounded-2"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i> Log In</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>

                        </ul>
                    </div>
                    
                    <div class="col-3 col-lg-2 text-end d-flex align-items-center justify-content-end gap-2 gap-lg-3 nav-actions">
                        
                        <?php if (!$isGuest): ?>
                            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline m-0 logout-form']) ?>
                            <?= Html::submitButton(
                                '<i class="bi bi-box-arrow-right"></i>'
                                . '<span class="d-none d-sm-inline ms-1">Logout (' . Html::encode($username) . ')</span>'
                                . '<span class="d-inline d-sm-none ms-1">Logout</span>',
                                ['class' => 'btn btn-sm btn-danger px-3 py-1 fw-bold rounded-pill shadow-sm logout-btn']
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

.nav-actions {
    flex-wrap: nowrap !important;
}

.nav-actions .logout-form {
    min-width: 0;      
    flex-shrink: 1;
}

.nav-actions .logout-btn {
    white-space: nowrap;   
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.nav-actions .burger {
    flex-shrink: 0;      
    position: relative;
    z-index: 20 !important ;           
    min-width: 34px;
    min-height: 34px;
}

.nav-actions .logout-form,
.nav-actions .call-us {
    position: relative;
    z-index: 5;             
}

@media (max-width: 575.98px) {
    .nav-actions .logout-btn {
        padding-left: 0.6rem !important;
        padding-right: 0.6rem !important;
        font-size: 0.8rem;
    }
}
</style>

<script>
window.addEventListener('click', function(){
    document.querySelectorAll('.custom-menu-box').forEach(el => el.classList.add('d-none'));
});
</script>