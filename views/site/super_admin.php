<?php $this->title = 'Master SaaS Control Panel'; ?>
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h1 class="h3 fw-bold text-dark">SaaS Master School Registry</h1>
        <a href="<?= \yii\helpers\Url::toRoute(['site/create-school']) ?>" class="btn btn-primary fw-bold rounded-3">+ Onboard New School</a>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-dark >
                <tr>
                    <th>ID</th><th>School Name</th><th>Settlement Account</th><th>Target Base Fees</th><th>Contact Email</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schools as $school): ?>
                <tr>
                    <td class="><?= $school->id ?></td>
                    <td class="fw-bold"><?= $school->name ?></td>
                    <td class="font-monospace text-muted"><?= $school->bank_account ?></td>
                    <td class="font-monospace fw-bold text-success">UGX <?= number_format((float)$school->base_tuition_fees, 0) ?></td>
                    <td><?= $school->contact_email ?></td>
                    <td><a href="<?= \yii\helpers\Url::toRoute(['site/edit-school', 'id' => $school->id]) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded">Edit Specifications</a></td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
