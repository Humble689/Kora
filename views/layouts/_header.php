<?php

declare(strict_types=1);

use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

// Capture session metrics directly for conditional routing
$isGuest = Yii::$app->user->isGuest;
$identity = !$isGuest ? Yii::$app->user->identity : null;
$role = !$isGuest ? $identity->role : null;
$currentRoute = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

NavBar::begin([
    'brandLabel' => '<span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span> KORA',
    'brandOptions' => ['class' => 'navbar-brand fw-bold d-flex align-items-center gap-2'],
    'brandUrl' => Yii::$app->homeUrl,
    'options' => ['class' => 'navbar navbar-expand-md navbar-dark site-navbar fixed-top shadow-sm'],
]);

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ms-auto fw-bold gap-2 align-items-center'],
    'items' => [
        !$isGuest && $role === 'BURSAR' && $currentRoute === 'site/index' ? (
            ['label' => '<i class="bi bi-arrow-left-short"></i> Back to Workspace Desk', 'url' => ['/site/bursar'], 'encode' => false, 'linkOptions' => ['class' => 'btn btn-sm nav-pill-btn px-3']]
        ) : '',

        !$isGuest && $role === 'CANTEEN' && $currentRoute === 'site/index' ? (
            ['label' => '<i class="bi bi-arrow-left-short"></i> Back to Register Till', 'url' => ['/site/canteen-terminal'], 'encode' => false, 'linkOptions' => ['class' => 'btn btn-sm nav-pill-btn px-3']]
        ) : '',

        !$isGuest && $role === 'SUPER_ADMIN' && $currentRoute === 'site/index' ? (
            ['label' => '<i class="bi bi-arrow-left-short"></i> Back to SaaS Control Panel', 'url' => ['/site/super-admin'], 'encode' => false, 'linkOptions' => ['class' => 'btn btn-sm nav-pill-btn px-3']]
        ) : '',

        !$isGuest && $role === 'TEACHER' && $currentRoute !== 'site/index' ? (
            ['label' => 'Bursar Dashboard', 'url' => ['/site/teacher-grading']]
        ) : '',

        !$isGuest && $role === 'CANTEEN' && $currentRoute !== 'site/index' ? (
            ['label' => 'Canteen Counter POS', 'url' => ['/site/canteen-terminal']]
        ) : '',

        !$isGuest && $role === 'SCHOOL_ADMIN' && $currentRoute !== 'site/index' ? (
            ['label' => 'SchoolAdmin', 'url' => ['/site/school-admin']]
        ) : '',

        // Settings - visible to every logged-in role
        !$isGuest ? (
            [
                'label' => '<i class="bi bi-gear-fill"></i> <span class="d-none d-md-inline">Settings</span>',
                'url' => ['/site/settings'],
                'encode' => false,
                'linkOptions' => [
                    'class' => 'nav-pill-btn btn btn-sm px-3' . ($currentRoute === 'site/settings' ? ' active' : ''),
                ],
            ]
        ) : '',

        // Authentication Switch Dropdown Block
        $isGuest ? (
            [
                'label' => '<i class="bi bi-person-badge"></i> Staff Access',
                'encode' => false,
                'linkOptions' => [
                    'onclick' => "const el = this.nextElementSibling; if (el) { el.classList.toggle('show'); this.parentElement.classList.toggle('show'); event.stopPropagation(); }"
                ],
                'items' => [
                    ['label' => 'Log In', 'url' => ['/site/login']],
                    ['label' => 'Register New Staff', 'url' => ['/site/signup']],
                ],
            ]
        ) : (
            '<li class="nav-item d-flex align-items-center">'
            . '<a href="' . Yii::$app->urlManager->createUrl(['/site/settings']) . '" class="nav-user-chip me-2 d-none d-md-inline-flex align-items-center gap-2 text-decoration-none">'
            . (!empty($identity->profile_photo)
                ? '<img src="' . Html::encode($identity->profile_photo) . '" alt="" class="rounded-circle" style="width:22px;height:22px;object-fit:cover;">'
                : '<i class="bi bi-person-circle"></i>')
            . Html::encode($identity->username)
            . '</a>'
            . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline m-0'])
            . Html::submitButton(
                '<i class="bi bi-box-arrow-right"></i> Logout',
                ['class' => 'btn btn-sm logout-btn fw-bold border-0 px-3', 'type' => 'submit']
            )
            . Html::endForm()
            . '</li>'
        )
    ],
]);

NavBar::end();

?>

<style>
    :root {
        --kora-blue-900: #0b2a52;
        --kora-blue-800: #0f3a70;
        --kora-blue-700: #14488a;
        --kora-blue-accent: #3b82f6;
    }

    .site-navbar {
        background: linear-gradient(90deg, var(--kora-blue-800) 0%, var(--kora-blue-700) 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
    }

    .site-navbar .navbar-brand {
        color: #fff !important;
        letter-spacing: 0.05em;
        font-size: 1.15rem;
    }

    .site-navbar .brand-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--kora-blue-accent);
        font-size: 0.95rem;
    }

    .site-navbar .nav-link {
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 0.9rem;
        padding: 0.45rem 0.75rem;
        border-radius: 8px;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .site-navbar .nav-link:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff !important;
    }

    .site-navbar .nav-pill-btn {
        background: rgba(255, 255, 255, 0.1);
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        border-radius: 50px !important;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none !important;
    }

    .site-navbar .nav-pill-btn:hover,
    .site-navbar .nav-pill-btn.active {
        background: #fff;
        color: var(--kora-blue-800) !important;
    }

    .nav-user-chip {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.85rem;
        font-weight: 600;
        transition: color 0.15s ease;
    }

    .nav-user-chip:hover {
        color: #fff;
    }

    .logout-btn {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 50px;
        font-size: 0.85rem;
    }

    .logout-btn:hover {
        background: #fff;
        color: #c0392b;
    }

    .site-navbar .dropdown-menu {
        border: none;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(11, 42, 82, 0.18);
        overflow: hidden;
        padding: 0.35rem;
    }

    .site-navbar .dropdown-item {
        border-radius: 6px;
        font-size: 0.88rem;
        padding: 0.5rem 0.85rem;
    }

    .site-navbar .dropdown-item:hover {
        background: var(--kora-blue-soft, rgba(59, 130, 246, 0.12));
        color: var(--kora-blue-800);
    }
</style>
<!-- <style>
    .site-navbar {
        background: linear-gradient(90deg, #1f6feb 0%, #1657c1 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }

    .site-navbar .navbar-brand,
    .site-navbar .nav-link {
        color: rgba(255, 255, 255, 0.95) !important;
    }

    .site-navbar .navbar-brand {
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 1rem !important;
        margin-right: 1rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        box-shadow: 0 10px 24px rgba(16, 33, 58, 0.12);
        transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .site-navbar .navbar-brand:hover,
    .site-navbar .navbar-brand:focus {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
    }

    .site-navbar .nav-link:hover,
    .site-navbar .nav-link:focus,
    .site-navbar .nav-link.active {
        color: #ffffff !important;
    }

    .site-navbar .dropdown-menu {
        border: 1px solid #d8e4f7;
        border-radius: 0.85rem;
        box-shadow: 0 18px 40px rgba(16, 33, 58, 0.12);
    }

    .site-navbar .dropdown-item {
        color: #10213a;
    }

    .site-navbar .dropdown-item:hover,
    .site-navbar .dropdown-item:focus {
        background-color: #edf4ff;
        color: #1657c1;
    }

    .site-navbar .btn-outline-primary,
    .site-navbar .btn-outline-success,
    .site-navbar .btn-outline-warning {
        border-color: rgba(255, 255, 255, 0.7) !important;
        color: #ffffff !important;
    }

    .site-navbar .btn-outline-primary:hover,
    .site-navbar .btn-outline-success:hover,
    .site-navbar .btn-outline-warning:hover {
        background-color: rgba(255, 255, 255, 0.16) !important;
        border-color: #ffffff !important;
    }
</style> -->