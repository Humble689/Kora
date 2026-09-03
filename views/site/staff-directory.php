<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Staff Directory';

$statusMeta = [
    'ACTIVE'      => ['label' => 'Active',      'class' => 'status-active',      'dot' => '#16a34a'],
    'INACTIVE'    => ['label' => 'Inactive',    'class' => 'status-inactive',    'dot' => '#6b7280'],
    'ON_LEAVE'    => ['label' => 'On Leave',    'class' => 'status-leave',       'dot' => '#d97706'],
    'TERMINATED'  => ['label' => 'Terminated',  'class' => 'status-terminated',  'dot' => '#dc2626'],
];

$roleMeta = [
    'SCHOOL_ADMIN' => 'Administrator',
    'BURSAR'       => 'Bursar',
    'CANTEEN'      => 'Canteen Operator',
    'TEACHER'      => 'Teacher',
    'DOS'          => 'Director of Studies',
];

$allModels = $dataProvider->getModels();
$counts = ['ACTIVE' => 0, 'INACTIVE' => 0, 'ON_LEAVE' => 0, 'TERMINATED' => 0];
foreach ($allModels as $m) {
    if (isset($counts[$m->status])) $counts[$m->status]++;
}
?>
<style>

#assignDeviceModal {
    z-index: 2000 !important;
}
.modal-backdrop {
    z-index: 1990 !important;
}
.staff-page { background: #f7f8fa; min-height: 100vh; padding: 2.5rem 0; }
.staff-container { max-width: 1140px; margin: 0 auto; padding: 0 1.5rem; }

.staff-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--theme-primary, #2563eb); }
.staff-title { font-size: 1.65rem; font-weight: 800; color: #111827; margin: .25rem 0 .25rem; }
.staff-subtitle { color: #6b7280; font-size: .9rem; }

.stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin: 1.75rem 0; }
.stat-card { background: #fff; border: 1px solid #eceef1; border-radius: 14px; padding: 1.1rem 1.25rem; }
.stat-num { font-size: 1.6rem; font-weight: 800; color: #111827; line-height: 1; }
.stat-label { font-size: .75rem; color: #6b7280; margin-top: .4rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.stat-card.active .stat-num { color: #16a34a; }
.stat-card.leave .stat-num { color: #d97706; }
.stat-card.terminated .stat-num { color: #dc2626; }

.staff-card { background: #fff; border: 1px solid #eceef1; border-radius: 16px; overflow: hidden; }
.staff-toolbar { display: flex; gap: .75rem; align-items: center; padding: 1.1rem 1.25rem; border-bottom: 1px solid #f1f2f4; flex-wrap: wrap; }
.staff-search { flex: 1; min-width: 220px; position: relative; }
.staff-search input { width: 100%; padding: .55rem .9rem .55rem 2.25rem; border: 1px solid #e5e7eb; border-radius: 10px; font-size: .875rem; background: #fafafa; }
.staff-search i { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.filter-chip { border: 1px solid #e5e7eb; background: #fff; color: #4b5563; font-size: .8rem; font-weight: 600; padding: .4rem .85rem; border-radius: 999px; cursor: pointer; transition: all .15s; }
.filter-chip.active { background: #111827; color: #fff; border-color: #111827; }

table.staff-table { width: 100%; border-collapse: collapse; }
.staff-table thead th { text-align: left; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; font-weight: 700; padding: .8rem 1.25rem; border-bottom: 1px solid #f1f2f4; }
.staff-table tbody td { padding: .95rem 1.25rem; border-bottom: 1px solid #f5f6f7; vertical-align: middle; }
.staff-table tbody tr:last-child td { border-bottom: none; }
.staff-table tbody tr:hover { background: #fafbfc; }

.staff-identity { display: flex; align-items: center; gap: .7rem; }
.staff-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
.staff-avatar-img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid #eceef1; }
.staff-name { font-weight: 700; color: #111827; font-size: .875rem; }
.staff-email { font-size: .78rem; color: #9ca3af; }

.role-pill { font-size: .74rem; font-weight: 600; color: #374151; background: #f3f4f6; padding: .28rem .65rem; border-radius: 7px; display: inline-block; }

.status-pill { font-size: .74rem; font-weight: 700; padding: .3rem .7rem .3rem .55rem; border-radius: 999px; display: inline-flex; align-items: center; gap: .4rem; }
.status-pill .dot { width: 6px; height: 6px; border-radius: 50%; }
.status-active { background: #ecfdf3; color: #16a34a; }
.status-inactive { background: #f3f4f6; color: #6b7280; }
.status-leave { background: #fffbeb; color: #b45309; }
.status-terminated { background: #fef2f2; color: #dc2626; }

.actions-dropdown { position: relative; display: inline-block; }
.actions-trigger { border: 1px solid #e5e7eb; background: #fff; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; color: #6b7280; }
.actions-trigger:hover { background: #f9fafb; }
.actions-menu { display: none; position: absolute; right: 0; top: calc(100% + 6px); background: #fff; border: 1px solid #eceef1; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.08); min-width: 200px; z-index: 20; overflow: hidden; }
.actions-menu.show { display: block; }
.actions-menu button { width: 100%; text-align: left; padding: .6rem .9rem; border: none; background: none; font-size: .82rem; font-weight: 500; color: #374151; cursor: pointer; display: flex; align-items: center; gap: .55rem; }
.actions-menu button:hover { background: #f9fafb; }
.actions-menu button.danger { color: #dc2626; }
.actions-menu .divider { height: 1px; background: #f1f2f4; margin: .25rem 0; }

.empty-state { text-align: center; padding: 3.5rem 1rem; color: #9ca3af; }
.toast-container { position: fixed; top: 1.25rem; right: 1.25rem; z-index: 999; display: flex; flex-direction: column; gap: .5rem; }
.toast { padding: .75rem 1.1rem; border-radius: 10px; font-size: .85rem; font-weight: 600; box-shadow: 0 8px 20px rgba(0,0,0,.12); animation: slideIn .2s ease-out; }
.toast.success { background: #16a34a; color: #fff; }
.toast.error { background: #dc2626; color: #fff; }
@keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>

<div class="staff-page">
    <div class="staff-container">

        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2">
            <div>
                <span class="staff-eyebrow">Personnel Management</span>
                <h1 class="staff-title">Staff Directory</h1>
                <p class="staff-subtitle mb-0">Manage employment status for onboarded staff at this school.</p>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat-card active">
                <div class="stat-num"><?= $counts['ACTIVE'] ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $counts['INACTIVE'] ?></div>
                <div class="stat-label">Inactive</div>
            </div>
            <div class="stat-card leave">
                <div class="stat-num"><?= $counts['ON_LEAVE'] ?></div>
                <div class="stat-label">On Leave</div>
            </div>
            <div class="stat-card terminated">
                <div class="stat-num"><?= $counts['TERMINATED'] ?></div>
                <div class="stat-label">Terminated</div>
            </div>
        </div>

        <div class="staff-card">
            <div class="staff-toolbar">
                <div class="staff-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="staffSearchInput" placeholder="Search by name or email...">
                </div>
                <button type="button" class="filter-chip active" data-filter="ALL">All</button>
                <button type="button" class="filter-chip" data-filter="ACTIVE">Active</button>
                <button type="button" class="filter-chip" data-filter="INACTIVE">Inactive</button>
                <button type="button" class="filter-chip" data-filter="ON_LEAVE">On Leave</button>
                <button type="button" class="filter-chip" data-filter="TERMINATED">Terminated</button>
            </div>

            <?php if (empty($allModels)): ?>
                <div class="empty-state">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    No staff members found for this school yet.
                </div>
            <?php else: ?>
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody id="staffTableBody">
                        <?php foreach ($allModels as $m):
                            $meta = $statusMeta[$m->status] ?? $statusMeta['INACTIVE'];
                            $initials = strtoupper(substr($m->username, 0, 2));
                        ?>
                        <tr data-status="<?= Html::encode($m->status) ?>" data-role="<?= Html::encode($m->role) ?>" data-search="<?= Html::encode(strtolower($m->username . ' ' . $m->email)) ?>">
                           <td>
                                <div class="staff-identity">
                                    <?php if (!empty($m->profile_photo)): ?>
                                        <img src="<?= Html::encode($m->profile_photo) ?>" alt="<?= Html::encode($m->username) ?>" class="staff-avatar-img">
                                    <?php else: ?>
                                        <div class="staff-avatar"><?= Html::encode($initials) ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="staff-name"><?= Html::encode($m->username) ?></div>
                                        <div class="staff-email"><?= Html::encode($m->email) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-pill"><?= Html::encode($roleMeta[$m->role] ?? $m->role) ?></span></td>
                            <td>
                                <span class="status-pill <?= $meta['class'] ?>" id="statusPill-<?= $m->id ?>">
                                    <span class="dot" style="background: <?= $meta['dot'] ?>;"></span>
                                    <?= $meta['label'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php if ($m->status !== 'TERMINATED'): ?>
                                <div class="actions-dropdown">
                                    <button type="button" class="actions-trigger" onclick="toggleMenu(<?= $m->id ?>)">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="actions-menu" id="menu-<?= $m->id ?>">
                                        <?php if ($m->status !== 'ACTIVE'): ?>
                                        <button onclick="setStaffStatus(<?= $m->id ?>, 'ACTIVE')"><i class="bi bi-check-circle"></i> Reactivate</button>
                                        <?php endif; ?>
                                        <?php if ($m->status !== 'INACTIVE'): ?>
                                        <button onclick="setStaffStatus(<?= $m->id ?>, 'INACTIVE')"><i class="bi bi-pause-circle"></i> Set Inactive</button>
                                        <?php endif; ?>
                                        <?php if ($m->status !== 'ON_LEAVE'): ?>
                                        <button onclick="setStaffStatus(<?= $m->id ?>, 'ON_LEAVE')"><i class="bi bi-airplane"></i> Mark On Leave</button>
                                        <?php endif; ?>
                                        <?php if ($m->role === 'CANTEEN'): ?>
                                        <div class="divider"></div>
                                        <button onclick="openAssignDeviceModal(<?= $m->id ?>, '<?= Html::encode(addslashes($m->username)) ?>')"><i class="bi bi-hdd-stack"></i> Assign Terminal</button>
                                        <?php endif; ?>
                                        <div class="divider"></div>
                                        <button class="danger" onclick="removeStaff(<?= $m->id ?>, '<?= Html::encode(addslashes($m->username)) ?>')"><i class="bi bi-person-x"></i> Remove Staff</button>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="p-3">
                    <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Assign Terminal Modal -->
<div class="modal fade" id="assignDeviceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Assign Terminal - <span id="assignDeviceStaffName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 11px;">Select an existing terminal</label>
                <select id="assignDeviceSelect" class="form-select mb-3">
                    <option value="">- No terminal (unassign) -</option>
                    <?php foreach ($devices as $device): ?>
                        <option value="<?= $device->id ?>">
                            <?= Html::encode($device->label ?: $device->device_uid) ?>
                            <?= $device->assigned_staff_id ? ' (currently assigned)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 11px;">Rename terminal (optional)</label>
                <input type="text" id="assignDeviceLabelInput" class="form-control" placeholder="Leave blank to keep current label" maxlength="100">
                <div class="form-text">Reassigning a terminal already in use will release it from its current cashier.</div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success fw-bold" onclick="submitAssignDevice()">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
const CSRF_PARAM = '<?= Yii::$app->request->csrfParam ?>';
const CSRF_TOKEN = '<?= Yii::$app->request->getCsrfToken() ?>';

function toggleMenu(id) {
    document.querySelectorAll('.actions-menu.show').forEach(el => {
        if (el.id !== `menu-${id}`) el.classList.remove('show');
    });
    document.getElementById(`menu-${id}`).classList.toggle('show');
}
document.addEventListener('click', (e) => {
    if (!e.target.closest('.actions-dropdown')) {
        document.querySelectorAll('.actions-menu.show').forEach(el => el.classList.remove('show'));
    }
});

function showToast(message, isError) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${isError ? 'error' : 'success'}`;
    toast.innerText = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

function postAction(url, id) {
    const params = new URLSearchParams();
    params.append(CSRF_PARAM, CSRF_TOKEN);
    return fetch(`${url}?id=${id}`, {
        method: 'POST',
        body: params,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    }).then(res => res.json());
}

function setStaffStatus(id, status) {
    const params = new URLSearchParams();
    params.append('status', status);
    params.append(CSRF_PARAM, CSRF_TOKEN);

    fetch(`<?= Url::toRoute(['site/update-staff-status']) ?>?id=${id}`, {
        method: 'POST',
        body: params,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, !data.success);
        if (data.success) setTimeout(() => location.reload(), 600);
    });
}

function removeStaff(id, username) {
    if (!confirm(`Remove ${username} from active staff? Their account will be deactivated and any assigned POS terminal released. This does not delete their transaction history.`)) {
        return;
    }
    postAction('<?= Url::toRoute(['site/remove-staff']) ?>', id)
        .then(data => {
            showToast(data.message, !data.success);
            if (data.success) setTimeout(() => location.reload(), 600);
        });
}

// --- Assign Terminal modal ---
let assignDeviceModal;
let currentAssignStaffId = null;

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('assignDeviceModal');

    // Move the modal to <body> so it escapes any stacking context
    document.body.appendChild(modalEl);

    assignDeviceModal = new bootstrap.Modal(modalEl, {
        backdrop: true,
        keyboard: true,
        focus: true
    });
});

function openAssignDeviceModal(staffId, staffName) {
    document.querySelectorAll('.actions-menu.show').forEach(el => el.classList.remove('show'));

    currentAssignStaffId = staffId;
    document.getElementById('assignDeviceStaffName').innerText = staffName;
    document.getElementById('assignDeviceSelect').value = '';
    document.getElementById('assignDeviceLabelInput').value = '';

    assignDeviceModal.show();
}

function submitAssignDevice() {
    const deviceId = document.getElementById('assignDeviceSelect').value;
    const label = document.getElementById('assignDeviceLabelInput').value.trim();

    const params = new URLSearchParams();
    params.append('staff_id', currentAssignStaffId);
    params.append('device_id', deviceId);
    params.append('label', label);
    params.append(CSRF_PARAM, CSRF_TOKEN);

    fetch('<?= Url::toRoute(['site/assign-device']) ?>', {
        method: 'POST',
        body: params,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(res => res.json())
    .then(data => {
        assignDeviceModal.hide();
        showToast(data.message, !data.success);
        if (data.success) setTimeout(() => location.reload(), 700);
    });
}

// Filter chips
document.querySelectorAll('.filter-chip').forEach(chip => {
    chip.addEventListener('click', () => {
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        applyFilters();
    });
});

document.getElementById('staffSearchInput')?.addEventListener('input', applyFilters);

function applyFilters() {
    const activeFilter = document.querySelector('.filter-chip.active').dataset.filter;
    const query = document.getElementById('staffSearchInput').value.toLowerCase().trim();

    document.querySelectorAll('#staffTableBody tr').forEach(row => {
        const matchesStatus = activeFilter === 'ALL' || row.dataset.status === activeFilter;
        const matchesSearch = !query || row.dataset.search.includes(query);
        row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
    });
}
</script>