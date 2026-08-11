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
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" data-bs-theme="light">
<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title) ?></title>
    
    <?php 
    if ($currentRoute !== 'site/index'): 
    ?>
    <style>
        body, html, #main {
            background-color: #f4f6f9 !important;
            color: #212529 !important;
        }

        .card, .site-signup, .site-login, .site-bursar {
            background-color: #ffffff !important;
            color: #212529 !important;
        }

        #main h1, #main h2, #main h3, #main h4, #main h5, #main h6,
        #main label, #main .form-label, #main p, #main span {
            /* color: #212529 !important; */
        }
        #main .text-muted {
            color: #6c757d !important;
        }

        /* Form buttons and links look professional */
        #main .btn-primary, #main button[type="submit"] {
            background-color: #007bff !important;
            border-color: #007bff !important;
            color: #ffffff !important;
        }
        #main .btn-primary:hover {
            background-color: #0056b3 !important;
            border-color: #0056b3 !important;
        }

        /* text link actions to match the theme blue */
        #main a:not(.btn):not(.sidebar-link) {
            color: #007bff !important;
            text-decoration: none !important;
            font-weight: 600 !important;
        }
        #main a:not(.btn):not(.sidebar-link):hover {
            text-decoration: underline !important;
            color: #0056b3 !important;
        }

        .form-control, .form-select, select, input {
            background-color: #ffffff !important;
            color: #212529 !important;
            border: 1px solid #ced4da !important;
        }

        .table {
            background-color: #ffffff !important;
            color: #212529 !important;
        }
        .table th {
            background-color: #f8f9fa !important;
            color: #212529 !important;
            font-weight: bold !important;
        }
        .table td {
            background-color: #ffffff !important;
            color: #212529 !important;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #f2f4f7 !important; 
            color: #212529 !important;
        }
        .table-hover > tbody > tr:hover > * {
            background-color: #e2e8f0 !important;
            color: #212529 !important;
        }

        .pagination .page-link {
            background-color: #ffffff !important;
            /* color: #007bff !important; */
            border: 1px solid #dee2e6 !important;
        }
        .pagination .page-item.active .page-link {
            /* background-color: #007bff !important; */
            border-color: #007bff !important;
            color: #ffffff !important;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d !important;
            background-color: #fff !important;
        }

        input[type="checkbox"] {
            accent-color: #02254b !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        #main input::placeholder,
        #main select::placeholder,
        #main textarea::placeholder,
        .form-control::placeholder {
            color: #495057 !important; 
            opacity: 1 !important; 
            font-weight: 500 !important;
        }

        #main input::-ms-input-placeholder { color: #495057 !important; opacity: 1 !important; }

        .sidebar-panel {
            width: 260px;
            min-height: calc(100vh - 56px);
            position: fixed;
            top: 56px;
            left: 0;
            z-index: 100;
            background-color: #1e293b !important;
            border-right: 1px solid #334155;
            padding: 1.5rem 1rem;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            color: #94a3b8 !important;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none !important;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            color: #ffffff !important;
            background-color: #334155 !important;
        }
        .workspace-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding-top: 1.5rem;
        }
        .sidebar-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            padding-left: 1rem;
        }
    </style>
    <?php endif; ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<?php 
if ($currentRoute !== 'site/index'): 
?>
    <?= $this->render('_header') ?>
<?php endif; ?>

<main id="main" class="flex-grow-1" role="main" style="<?= $currentRoute !== 'site/index' ? 'padding-top: 56px;' : '' ?>">
    
    <?php if (!$isGuest && $currentRoute !== 'site/index'): ?>
        <div class="d-flex w-100">
            
            <!-- Left Fixed Navigation Panel -->
            <div class="sidebar-panel">
                <div class="text-white small px-3 mb-3 pb-2 border-bottom border-secondary opacity-70">
                    <i class="bi bi-shield-lock-fill me-1"></i> Role: <span class="fw-bold text-info"><?= Html::encode($role) ?></span>
                </div>
                
                <div class="sidebar-heading">Navigation Desk</div>
                <a href="<?= Url::toRoute(['site/index']) ?>" class="sidebar-link"><i class="bi bi-house-door"></i> Parent Portal</a>


                                <!--  SCHOOL MASTER ADMINISTRATOR SIDEBAR DESK -->
                <?php if ($role === 'SCHOOL_ADMIN'): ?>
                    <div class="sidebar-heading">School Management</div>
                    <a href="<?= Url::toRoute(['site/school-admin']) ?>" class="sidebar-link <?= $currentRoute === 'site/school-admin' ? 'active' : '' ?>"><i class="bi bi-shield-shaded"></i> Admin Dashboard</a>
                    <a href="<?= Url::toRoute(['site/signup']) ?>" class="sidebar-link <?= $currentRoute === 'site/signup' ? 'active' : '' ?>"><i class="bi bi-person-plus-fill"></i> Provision Staff Account</a>
                    
                   <div class="sidebar-heading">Financial Audits</div>
                    <a href="<?= Url::toRoute(['site/bursar']) ?>" class="sidebar-link <?= $currentRoute === 'site/bursar' ? 'active' : '' ?>"><i class="bi bi-receipt-cutoff"></i> Collections Ledger</a>
                    <a href="<?= Url::toRoute(['site/students-directory']) ?>" class="sidebar-link <?= $currentRoute === 'site/students-directory' ? 'active' : '' ?>"><i class="bi bi-people-fill"></i> Student Directory</a>
                    
                    
                    <div class="sidebar-heading">Academic Moderation</div>
                    <a href="<?= Url::toRoute(['site/dos-review']) ?>" class="sidebar-link"><i class="bi bi-clipboard-check"></i> Review & Seal Marks</a>
                    <a href="<?= Url::toRoute(['site/manage-assignments']) ?>" class="sidebar-link"><i class="bi bi-person-gear"></i> Allocate Teachers</a>
                    <a href="<?= Url::toRoute(['site/print-reports']) ?>" class="sidebar-link"><i class="bi bi-printer"></i> Print Report Cards</a>
                <?php endif; ?>


                <!-- BURSAR SIDEBAR CONTROLS -->
                <?php if ($role === 'BURSAR'): ?>
                    <div class="sidebar-heading">Bursar Operations</div>
                    <a href="<?= Url::toRoute(['site/bursar']) ?>" class="sidebar-link <?= $currentRoute === 'site/bursar' ? 'active' : '' ?>"><i class="bi bi-receipt-cutoff"></i> Collections Ledger</a>
                    <a href="<?= Url::toRoute(['site/students-directory']) ?>" class="sidebar-link <?= $currentRoute === 'site/students-directory' ? 'active' : '' ?>"><i class="bi bi-people-fill"></i> Student Directory</a>
                    <a href="<?= Url::toRoute(['site/register-student']) ?>" class="sidebar-link <?= $currentRoute === 'site/register-student' ? 'active' : '' ?>"><i class="bi bi-person-plus"></i> Enroll Student</a>
                <?php endif; ?>



                <!-- TEACHER SIDEBAR CONTROLS -->
                <?php if ($role === 'TEACHER'): ?>
                    <div class="sidebar-heading">Classroom Grading</div>
                    <a href="<?= Url::toRoute(['site/teacher-grading']) ?>" class="sidebar-link <?= $currentRoute === 'site/teacher-grading' ? 'active' : '' ?>"><i class="bi bi-journal-check"></i> Enter Term Marks</a>
                <?php endif; ?>

                <!-- D.O.S. SIDEBAR CONTROLS -->
                <?php if ($role === 'DOS' || $role === 'SUPER_ADMIN'): ?>
                    <div class="sidebar-heading">Academic Moderation</div>
                    <a href="<?= Url::toRoute(['site/dos-review']) ?>" class="sidebar-link <?= $currentRoute === 'site/dos-review' ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i> Review & Seal Marks</a>
                     <a href="<?= Url::toRoute(['site/manage-assignments']) ?>" class="sidebar-link <?= $currentRoute === 'site/manage-assignments' ? 'active' : '' ?>"><i class="bi bi-person-gear"></i> Allocate Teachers</a>
                    <a href="<?= Url::toRoute(['site/print-reports']) ?>" class="sidebar-link <?= $currentRoute === 'site/print-reports' ? 'active' : '' ?>"><i class="bi bi-printer"></i> Print Report Cards</a>
                <?php endif; ?>
            </div>

            <div class="workspace-content container-fluid px-4">
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
                <?php endif ?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>
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
