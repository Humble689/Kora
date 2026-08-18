<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Users $user */
/** @var array $assignments */

use yii\bootstrap5\Html;

$this->title = 'Account Settings';
$isTeacher = ($user->role ?? null) === 'TEACHER';
?>

<div class="site-settings bg-light py-4 min-vh-100">
    <div class="container-fluid" style="max-width: 60rem;">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <h1 class="h3 fw-bold text-dark mb-0"><i class="bi bi-gear-fill me-2"></i>Account Settings</h1>
            <p class="text-muted small mb-0">Manage your profile picture and username.</p>
        </div>

        <div class="row g-3">
            <!-- Profile card -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title h6 fw-bold mb-0"><i class="bi bi-person-badge-fill me-1"></i> Profile</h5>
                    </div>
                    <div class="card-body p-4">
                        <?= Html::beginForm(['site/settings'], 'post', [
                            'enctype' => 'multipart/form-data',
                            'id' => 'settings-form',
                        ]) ?>
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                            <div class="d-flex align-items-center gap-4 mb-4">
                                <div class="position-relative">
                                    <img id="photo-preview"
                                         src="<?= !empty($user->profile_photo) ? $user->profile_photo : '' ?>"
                                         alt="Profile photo"
                                         class="rounded-circle border <?= empty($user->profile_photo) ? 'd-none' : '' ?>"
                                         style="width:96px;height:96px;object-fit:cover;">
                                    <span id="photo-placeholder"
                                          class="rounded-circle bg-secondary-subtle text-secondary d-inline-flex align-items-center justify-content-center <?= !empty($user->profile_photo) ? 'd-none' : '' ?>"
                                          style="width:96px;height:96px;">
                                        <i class="bi bi-person-fill fs-1"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <label for="profile_photo" class="form-label fw-semibold small text-uppercase text-muted mb-1">Profile Picture</label>
                                    <input type="file" name="profile_photo" id="profile_photo" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
                                    <div class="form-text text-dark text-muted">JPG, PNG, or WEBP. Max 2MB.</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="username" class="form-label fw-semibold small text-uppercase text-muted mb-1">Username</label>
                                <input type="text" name="username" id="username" class="form-control" value="<?= Html::encode($user->username) ?>" minlength="3" maxlength="32" pattern="[a-zA-Z0-9_.]+" required>
                                <div class="form-text text-dark text-muted">3-32 characters. Letters, numbers, dots, and underscores only.</div>
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4 d-flex align-items-center gap-2">
                                <i class="bi bi-check-lg"></i> Save Changes
                            </button>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            </div>

            <!-- Teacher assignments (read-only) -->
            <?php if ($isTeacher): ?>
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white h-100">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><i class="bi bi-mortarboard-fill me-1"></i> My Class &amp; Subject Assignments</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-secondary border-0 bg-light small mb-3">
                                <i class="bi bi-lock-fill me-1"></i> Assignments are view-only here. Contact your School Admin to request a change.
                            </div>

                            <?php if (empty($assignments)): ?>
                                <p class="text-muted small mb-0">No classes or subjects have been assigned to you yet.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($assignments as $a): ?>
                                        <li class="list-fpx-0 d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold text-dark"><?= Html::encode($a->class_level) ?></span>
                                            <span class="text-primary px-2 py-1"><?= Html::encode($a->subject_name) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('profile_photo').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('photo-preview');
    const placeholder = document.getElementById('photo-placeholder');
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (ev) {
        preview.src = ev.target.result;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
    };
    reader.readAsDataURL(file);
});
</script>