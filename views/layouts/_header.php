<?php

declare(strict_types=1);

use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

// Capture session metrics directly for conditional routing
$isGuest = Yii::$app->user->isGuest;
$role = !$isGuest ? Yii::$app->user->identity->role : null;
$currentRoute = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

NavBar::begin([
    'brandLabel' => 'KORA',
    'brandUrl' => Yii::$app->homeUrl,
    'options' => ['class' => 'navbar navbar-expand-md navbar-dark site-navbar fixed-top shadow-sm'],
]);

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ms-auto fw-bold gap-2 align-items-center'],
    'items' => [
        !$isGuest && $role === 'BURSAR' && $currentRoute === 'site/index' ? (
            ['label' => '← Back to Workspace Desk', 'url' => ['/site/bursar'], 'linkOptions' => ['class' => 'btn btn-sm btn-outline-primary px-3 rounded-pill text-white text-decoration-none border-2 fw-bold']]
        ) : '',

        !$isGuest && $role === 'CANTEEN' && $currentRoute === 'site/index' ? (
            ['label' => '← Back to Register Till', 'url' => ['/site/canteen-terminal'], 'linkOptions' => ['class' => 'btn btn-sm btn-outline-success px-3 rounded-pill text-white text-decoration-none border-2 fw-bold']]
        ) : '',

        !$isGuest && $role === 'SUPER_ADMIN' && $currentRoute === 'site/index' ? (
            ['label' => '← Back to SaaS Control Panel', 'url' => ['/site/super-admin'], 'linkOptions' => ['class' => 'btn btn-sm btn-outline-warning px-3 rounded-pill text-white text-decoration-none border-2 fw-bold']]
        ) : '',

        !$isGuest && $role === 'BURSAR' && $currentRoute !== 'site/index' ? (
            ['label' => 'Bursar Dashboard', 'url' => ['/site/bursar']]
        ) : '',

        !$isGuest && $role === 'CANTEEN' && $currentRoute !== 'site/index' ? (
            ['label' => 'Canteen Counter POS', 'url' => ['/site/canteen-terminal']]
        ) : '',

        !$isGuest && $role === 'SCHOOL_ADMIN' && $currentRoute !== 'site/index' ? (
            ['label' => 'SchoolAdmin', 'url' => ['/site/school-admin']]
        ) : '',

        // Authentication Switch Dropdown Block
        $isGuest ? (
            [
                'label' => 'Staff Access',
                'linkOptions' => [
                    'onclick' => "const el = this.nextElementSibling; if (el) { el.classList.toggle('show'); this.parentElement.classList.toggle('show'); event.stopPropagation(); }"
                ],
                'items' => [
                    ['label' => 'Log In', 'url' => ['/site/login']],
                    ['label' => 'Register New Staff', 'url' => ['/site/signup']],
                ],
            ]
        ) : (
            '<li class="nav-item">'
            . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline m-0'])
            . Html::submitButton(
                'Logout (' . Html::encode(Yii::$app->user->identity->username) . ')',
                ['class' => 'btn btn-link nav-link logout text-danger fw-bold border-0 px-2 d-inline-block']
            )
            . Html::endForm()
            . '</li>'
        )
    ],
]);

NavBar::end();

?>

<style>
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
</style>
