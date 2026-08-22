<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

$this->render('_head');

$isGuest = Yii::$app->user->isGuest;
$role = !$isGuest ? Yii::$app->user->identity->role : null;
$currentRoute = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" data-bs-theme="light">
<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title) ?></title>
    
    <?php 
    if (!in_array($currentRoute, ['site/index', 'site/login'], true)): 
    ?>
    <style>
        :root {
            --theme-primary: #1f6feb;
            --theme-primary-hover: #1657c1;
            --theme-surface: #ffffff;
            --theme-bg: #f4f8ff;
            --theme-text: #10213a;
            --theme-muted: #5b6f8f;
            --theme-border: #d8e4f7;
            --theme-soft: #edf4ff;
        }

        body, html, #main {
            background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%) !important;
            background-attachment: fixed !important;
            color: var(--theme-text) !important;
        }

        #main {
            position: relative;
        }

        #main::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at top right, rgba(31, 111, 235, 0.12), transparent 38%),
                        radial-gradient(circle at bottom left, rgba(31, 111, 235, 0.08), transparent 28%);
        }

        .card, .site-signup, .site-login, .site-bursar {
            background-color: var(--theme-surface) !important;
            color: var(--theme-text) !important;
            border-color: var(--theme-border) !important;
            box-shadow: 0 18px 50px rgba(16, 33, 58, 0.08) !important;
            border-radius: 1rem !important;
        }

        #main h1, #main h2, #main h3, #main h4, #main h5, #main h6,
        #main label, #main .form-label, #main p, #main span {
            color: var(--theme-text);
        }
        #main .text-muted {
            color: var(--theme-muted) !important;
        }

        /* Form buttons and links look professional */
        #main .btn-primary, #main button[type="submit"] {
            background-color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
            color: #ffffff !important;
        }
        #main .btn-primary:hover {
            background-color: var(--theme-primary-hover) !important;
            border-color: var(--theme-primary-hover) !important;
        }

        /* text link actions to match the theme blue */
        #main a:not(.btn):not(.sidebar-link) {
            color: var(--theme-primary) !important;
            text-decoration: none !important;
            font-weight: 600 !important;
        }
        #main a:not(.btn):not(.sidebar-link):hover {
            text-decoration: underline !important;
            color: var(--theme-primary-hover) !important;
        }

        .form-control, .form-select, select, input {
            background-color: #ffffff !important;
            color: var(--theme-text) !important;
            border: 1px solid var(--theme-border) !important;
        }

        .table {
            background-color: #ffffff !important;
            color: var(--theme-text) !important;
        }
        .table th {
            background-color: var(--theme-soft) !important;
            color: var(--theme-text) !important;
            font-weight: bold !important;
        }
        .table td {
            background-color: #ffffff !important;
            color: var(--theme-text) !important;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #f9fbff !important; 
            color: var(--theme-text) !important;
        }
        .table-hover > tbody > tr:hover > * {
            background-color: #eaf2ff !important;
            color: var(--theme-text) !important;
        }

        .pagination .page-link {
            background-color: #ffffff !important;
            color: var(--theme-primary) !important;
            border: 1px solid var(--theme-border) !important;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
            color: #ffffff !important;
        }
        .pagination .page-item.disabled .page-link {
            color: var(--theme-muted) !important;
            background-color: #fff !important;
        }

        input[type="checkbox"] {
            accent-color: var(--theme-primary) !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        #main input::placeholder,
        #main select::placeholder,
        #main textarea::placeholder,
        .form-control::placeholder {
            color: #6b5a86 !important; 
            opacity: 1 !important; 
            font-weight: 500 !important;
        }

        #main input::-ms-input-placeholder { color: #6b5a86 !important; opacity: 1 !important; }

        .sidebar-panel {
            width: 260px;
            min-height: calc(100vh - 56px);
            position: fixed;
            top: 56px;
            left: 0;
            z-index: 100;
            background: linear-gradient(180deg, #1f6feb 0%, #1657c1 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.18);
            padding: 1.5rem 1rem;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            color: rgba(255, 255, 255, 0.92) !important;
            font-weight: 600;
            border-radius: 0.85rem;
            text-decoration: none !important;
            margin-bottom: 0.25rem;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .sidebar-link:hover, .sidebar-link.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.18) !important;
            transform: translateX(2px);
        }
        .workspace-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding-top: 1.5rem;
            position: relative;
            z-index: 1;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        .sidebar-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.75);
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            padding-left: 1rem;
        }

        .sidebar-panel .border-bottom {
            border-color: rgba(255, 255, 255, 0.16) !important;
        }

        .sidebar-panel .badge,
        .sidebar-panel .role-badge {
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.22) !important;
        }

        .workspace-content > .breadcrumbs,
        .workspace-content > .alert {
            border-radius: 0.85rem;
        }

        /* ===== Responsive sidebar (tablet & phone) ===== */
        .sidebar-toggle-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 0.6rem;
            background: var(--theme-primary) !important;
            color: #fff !important;
            font-size: 1.1rem;
            position: fixed;
            top: 66px;
            left: 14px;
            z-index: 200;
            box-shadow: 0 6px 16px rgba(16, 33, 58, 0.18);
        }
        .sidebar-toggle-btn:hover {
            background: var(--theme-primary-hover) !important;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            top: 56px;
            background: rgba(11, 20, 38, 0.45);
            z-index: 90;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        @media (max-width: 991.98px) {
            .sidebar-toggle-btn {
                display: inline-flex;
            }

            .sidebar-panel.side {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.25);
            }
            .sidebar-panel.side.show {
                transform: translateX(0);
            }

            .workspace-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding-top: 4rem;
            }
        }

        @media (max-width: 575.98px) {
            .sidebar-panel.side {
                width: 82vw;
                max-width: 300px;
            }
        }
    </style>
    <?php endif; ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<?php 
if (!in_array($currentRoute, ['site/index', 'site/login'], true)): 
?>
    <?= $this->render('_header') ?>
<?php endif; ?>

<main id="main" class="flex-grow-1" role="main" style="<?= $currentRoute !== 'site/index' ? 'padding-top: 56px;' : '' ?>">
    
    <?php if (!$isGuest && $currentRoute !== 'site/index'): ?>
        <div class="d-flex w-100">

            <!-- Mobile / tablet sidebar toggle -->
            <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle navigation" aria-controls="koraSidebar" aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>

            <!-- Backdrop shown behind the sidebar on small screens -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <!-- Left Fixed Navigation Panel -->
        <div class="sidebar-panel side" id="koraSidebar">
                <div class="sidebar-brand-block px-3 pt-2 pb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="sidebar-brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
                        <span class="sidebar-brand-text">KORA</span>
                    </div>
                </div>

                <div class="text-white-50 small px-3 mb-3 pb-3 border-bottom border-light border-opacity-10">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="d-inline-flex align-items-center gap-2 fw-semibold text-white">
                            <i class="bi bi-shield-lock-fill"></i>
                            Account Role
                        </span>
                        <span class="badge rounded-pill px-3 py-2 role-badge"><?= Html::encode($role) ?></span>
                    </div>
                </div>

                <div class="sidebar-heading">Navigation Desk</div>
                <a href="<?= Url::toRoute(['site/index']) ?>" class="sidebar-link <?= $currentRoute === 'site/index' ? 'active' : '' ?>"><i class="bi bi-house-door"></i> Parent Portal</a>


                <!-- SCHOOL MASTER ADMINISTRATOR SIDEBAR DESK -->
                <?php if ($role === 'SCHOOL_ADMIN'): ?>
                    <div class="sidebar-heading">School Management</div>
                    <a href="<?= Url::toRoute(['site/school-admin']) ?>" class="sidebar-link <?= $currentRoute === 'site/school-admin' ? 'active' : '' ?>"><i class="bi bi-shield-shaded"></i> Admin Dashboard</a>
                    <a href="<?= Url::toRoute(['site/signup']) ?>" class="sidebar-link <?= $currentRoute === 'site/signup' ? 'active' : '' ?>"><i class="bi bi-person-plus-fill"></i> Provision Staff Account</a>

                    <div class="sidebar-heading">Financial Audits</div>
                    <a href="<?= Url::toRoute(['site/bursar']) ?>" class="sidebar-link <?= $currentRoute === 'site/bursar' ? 'active' : '' ?>"><i class="bi bi-receipt-cutoff"></i> Collections Ledger</a>
                    <a href="<?= Url::toRoute(['site/students-directory']) ?>" class="sidebar-link <?= $currentRoute === 'site/students-directory' ? 'active' : '' ?>"><i class="bi bi-people-fill"></i> Student Directory</a>

                    <div class="sidebar-heading">Academic Moderation</div>
                    <a href="<?= Url::toRoute(['site/dos-review']) ?>" class="sidebar-link <?= $currentRoute === 'site/dos-review' ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i> Review &amp; Seal Marks</a>
                    <a href="<?= Url::toRoute(['site/manage-assignments']) ?>" class="sidebar-link <?= $currentRoute === 'site/manage-assignments' ? 'active' : '' ?>"><i class="bi bi-person-gear"></i> Allocate Teachers</a>
                    <a href="<?= Url::toRoute(['site/print-reports']) ?>" class="sidebar-link <?= $currentRoute === 'site/print-reports' ? 'active' : '' ?>"><i class="bi bi-printer"></i> Print Report Cards</a>
                <?php endif; ?>


              <!-- BURSAR SIDEBAR CONTROLS -->
                <?php if ($role === 'BURSAR'): ?>
                    <div class="sidebar-heading">Bursar Operations</div>
                    <a href="<?= Url::toRoute(['site/bursar']) ?>" class="sidebar-link <?= $currentRoute === 'site/bursar' ? 'active' : '' ?>"><i class="bi bi-receipt-cutoff"></i> Collections Ledger</a>
                    <a href="<?= Url::toRoute(['site/students-directory']) ?>" class="sidebar-link <?= $currentRoute === 'site/students-directory' ? 'active' : '' ?>"><i class="bi bi-people-fill"></i> Student Directory</a>
                    <a href="<?= Url::toRoute(['site/register-student']) ?>" class="sidebar-link <?= $currentRoute === 'site/register-student' ? 'active' : '' ?>"><i class="bi bi-person-plus"></i> Enroll Student</a>
                    <a href="<?= Url::toRoute(['site/expense-claims']) ?>" class="sidebar-link <?= $currentRoute === 'site/expense-claims' ? 'active' : '' ?>"><i class="bi bi-clipboard-check-fill"></i> Expense Claims</a>
                <?php endif; ?>


                <!-- TEACHER SIDEBAR CONTROLS -->
                <?php if ($role === 'TEACHER'): ?>
                    <div class="sidebar-heading">Classroom Grading</div>
                    <a href="<?= Url::toRoute(['site/teacher-grading']) ?>" class="sidebar-link <?= $currentRoute === 'site/teacher-grading' ? 'active' : '' ?>"><i class="bi bi-journal-check"></i> Enter Term Marks</a>
                <?php endif; ?>

                <!-- D.O.S. SIDEBAR CONTROLS -->
                <?php if ($role === 'DOS' || $role === 'SUPER_ADMIN'): ?>
                    <div class="sidebar-heading">Academic Moderation</div>
                    <a href="<?= Url::toRoute(['site/dos-review']) ?>" class="sidebar-link <?= $currentRoute === 'site/dos-review' ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i> Review &amp; Seal Marks</a>
                    <a href="<?= Url::toRoute(['site/manage-assignments']) ?>" class="sidebar-link <?= $currentRoute === 'site/manage-assignments' ? 'active' : '' ?>"><i class="bi bi-person-gear"></i> Allocate Teachers</a>
                    <a href="<?= Url::toRoute(['site/print-reports']) ?>" class="sidebar-link <?= $currentRoute === 'site/print-reports' ? 'active' : '' ?>"><i class="bi bi-printer"></i> Print Report Cards</a>
                <?php endif; ?>
            </div>

<style>
    :root {
        --kora-blue-900: #0b2a52;
        --kora-blue-800: #0f3a70;
        --kora-blue-700: #14488a;
        --kora-blue-accent: #3b82f6;
        --kora-blue-soft: rgba(59, 130, 246, 0.16);
    }

    /* ===== Sidebar ===== */
    .sidebar-panel.side {
        background: linear-gradient(180deg, var(--kora-blue-900) 0%, var(--kora-blue-800) 100%);
        min-height: 100vh;
        padding-top: 0.75rem;
        box-shadow: 2px 0 12px rgba(11, 42, 82, 0.15);
    }

    .sidebar-brand-block {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 0.75rem;
    }

    .sidebar-brand-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: var(--kora-blue-accent);
        color: #fff;
        font-size: 1rem;
    }

    .sidebar-brand-text {
        color: #fff;
        font-weight: 700;
        letter-spacing: 0.06em;
        font-size: 1.05rem;
    }

    .role-badge {
        background: var(--kora-blue-accent);
        color: #fff;
        font-weight: 600;
        font-size: 0.72rem;
        letter-spacing: 0.03em;
    }

    .sidebar-heading {
        color: rgba(255, 255, 255, 0.45);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.9rem 1.25rem 0.4rem;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.6rem 1.25rem;
        margin: 0.05rem 0.6rem;
        border-radius: 8px;
        color: rgba(255, 255, 255, 0.75);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }

    .sidebar-link i {
        font-size: 1rem;
        width: 20px;
        text-align: center;
        color: rgba(255, 255, 255, 0.55);
        transition: color 0.15s ease;
    }

    .sidebar-link:hover {
        background: rgba(255, 255, 255, 0.06);
        color: #fff;
        transform: translateX(2px);
    }

    .sidebar-link:hover i {
        color: #fff;
    }

    .sidebar-link.active {
        background: var(--kora-blue-accent);
        color: #fff;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.35);
    }

    .sidebar-link.active i {
        color: #fff;
    }
</style>

            <div class="workspace-content container-fluid px-4">
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
                <?php endif ?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>

        <script>
        (function () {
            var sidebar = document.getElementById('koraSidebar');
            var toggleBtn = document.getElementById('sidebarToggleBtn');
            var overlay = document.getElementById('sidebarOverlay');

            if (!sidebar || !toggleBtn || !overlay) {
                return;
            }

            function openSidebar() {
                sidebar.classList.add('show');
                overlay.classList.add('show');
                toggleBtn.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                toggleBtn.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            toggleBtn.addEventListener('click', function () {
                if (sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            overlay.addEventListener('click', closeSidebar);

            // Auto-close after tapping a nav link on small screens
            sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });

            // If the window is resized back up to desktop, make sure the
            // mobile-only classes are cleared out
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });
        })();
        </script>
    <?php else: ?>
        <!-- Standard Content Layout for Unauthenticated Guests or the Parent Portal Landing Page -->
        <div class="container">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    <?php endif; ?>
</main>

<?= $this->render('_footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>