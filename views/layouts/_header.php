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
    'options' => ['class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top shadow-sm'],
]);

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ms-auto fw-bold gap-2 align-items-center'],
    'items' => [
        ['label' => 'Parent Portal', 'url' => ['/site/index']],
        
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
