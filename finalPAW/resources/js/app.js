import './bootstrap';

// ── Sidebar Toggle ──────────────────────────────────────────────────────────
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const sidebarToggle  = document.getElementById('sidebar-toggle');
const sidebarClose   = document.getElementById('sidebar-close');

function openSidebar()  { sidebar.classList.remove('-translate-x-full'); sidebarOverlay.classList.remove('hidden'); }
function closeSidebar() { sidebar.classList.add('-translate-x-full');    sidebarOverlay.classList.add('hidden'); }

sidebarToggle?.addEventListener('click', openSidebar);
sidebarClose?.addEventListener('click', closeSidebar);
sidebarOverlay?.addEventListener('click', closeSidebar);

// ── User Menu Dropdown ──────────────────────────────────────────────────────
const userMenuBtn = document.getElementById('user-menu-btn');
const userMenu    = document.getElementById('user-menu');
userMenuBtn?.addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
document.addEventListener('click', () => userMenu?.classList.add('hidden'));

// ── Toast Notification System ───────────────────────────────────────────────
function showToast(type, message, duration = 5000) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    toast.innerHTML = `
        <div class="flex-1">
            <p class="text-sm text-gray-800 leading-snug">${message}</p>
        </div>
        <button onclick="closeToast(this)" class="text-gray-400 hover:text-gray-600 text-lg leading-none ml-1">&times;</button>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.remove(), duration);
}

function closeToast(el) {
    el.parentElement.remove();
}

// Export ke global agar bisa dipanggil dari @push('scripts') di view anak
window.showToast  = showToast;
window.closeToast = closeToast;

// ── Flash Messages on Page Load ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const flashSuccess = document.querySelector('meta[name="flash-success"]');
    if (flashSuccess) showToast('success', flashSuccess.content);

    const flashReportCode = document.querySelector('meta[name="flash-report-code"]');
    if (flashReportCode) {
        setTimeout(() => showToast('info', 'Simpan kode laporan Anda: <strong>' + flashReportCode.content + '</strong>'), 1000);
    }

    const flashError = document.querySelector('meta[name="flash-error"]');
    if (flashError) showToast('danger', flashError.content);
});

// ── Ajax Notification Polling ───────────────────────────────────────────────
const isAuth = document.querySelector('meta[name="auth-check"]')?.content === '1';

if (isAuth) {
    const POLL_INTERVAL = 15000;
    let lastNotifCount  = 0;

    const notifBtn         = document.getElementById('notif-btn');
    const notifBadge       = document.getElementById('notif-badge');
    const notifDropdown    = document.getElementById('notif-dropdown');
    const notifList        = document.getElementById('notif-list');
    const bellIcon         = document.getElementById('bell-icon');
    const markAllBtn       = document.getElementById('mark-all-read');

    const notifPollUrl     = document.querySelector('meta[name="route-notifications-poll"]').content;
    const notifMarkReadUrl = document.querySelector('meta[name="route-notifications-mark-read"]').content;

    notifBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('notif-wrapper')?.contains(e.target)) {
            notifDropdown?.classList.add('hidden');
        }
    });

    async function pollNotifications() {
        try {
            const res  = await fetch(notifPollUrl, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            updateNotifUI(data);

            if (data.count > lastNotifCount && lastNotifCount !== 0) {
                const newest = data.notifications[0];
                if (newest) {
                    showToast(
                        newest.color === 'success' ? 'success' : newest.color === 'danger' ? 'danger' : 'info',
                        `<strong>${newest.title}</strong><br>${newest.message}`
                    );
                    bellIcon.classList.add('bell-ring');
                    setTimeout(() => bellIcon.classList.remove('bell-ring'), 600);
                }
            }
            lastNotifCount = data.count;
        } catch(err) {
            console.error('Notification poll failed:', err);
        }
    }

    function updateNotifUI(data) {
        if (data.count > 0) {
            notifBadge.textContent = data.count > 9 ? '9+' : data.count;
            notifBadge.classList.remove('hidden');
        } else {
            notifBadge.classList.add('hidden');
        }

        if (data.notifications.length === 0) {
            notifList.innerHTML = '<p class="text-center text-gray-400 text-sm py-8">Tidak ada notifikasi baru</p>';
            return;
        }

        notifList.innerHTML = data.notifications.map(n => `
            <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer notif-item" data-id="${n.id}">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold leading-tight ${n.color === 'success' ? 'text-green-600' : n.color === 'danger' ? 'text-red-600' : 'text-gray-800'}">${n.title}</p>
                    <p class="text-xs text-gray-500 mt-0.5 leading-snug">${n.message}</p>
                    <p class="text-xs text-gray-400 mt-1">${n.created_at}</p>
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.notif-item').forEach(el => {
            el.addEventListener('click', async () => {
                const id = el.dataset.id;
                await fetch(`/api/notifications/${id}/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                el.style.opacity = '0.5';
                await pollNotifications();
            });
        });
    }

    markAllBtn?.addEventListener('click', async () => {
        await fetch(notifMarkReadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        await pollNotifications();
        notifDropdown.classList.add('hidden');
        showToast('success', 'Semua notifikasi ditandai sudah dibaca.');
    });

    pollNotifications();
    setInterval(pollNotifications, POLL_INTERVAL);
}
