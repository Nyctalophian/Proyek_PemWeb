{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="route-notifications-poll" content="{{ route('notifications.poll') }}">
    <meta name="route-notifications-mark-read" content="{{ route('notifications.mark-read') }}">
    <title>@yield('title', 'Lost & Found') — FILKOM UB</title>

    {{-- Tailwind CSS via CDN (untuk development; production pakai npm) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy:   { DEFAULT: '#1D3970', light: '#CCE5FF' },
                        orange: { DEFAULT: '#E3771C', light: '#FEF9C2', hover: '#c9631a' },
                    }
                }
            }
        }
    </script>
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-body min-h-screen flex flex-col">

{{-- Toast container (PHP state: flash messages + JS notifications) --}}
<div id="toast-container"></div>

{{-- ────── NAVBAR ────── --}}
<nav class="bg-navy shadow-md">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">

        {{-- Logo + Hamburger --}}
        <div class="flex items-center gap-4">
            <button id="sidebar-toggle" class="flex md:hidden flex-col gap-[5px] p-1 cursor-pointer" aria-label="Toggle menu">
                <span class="w-6 h-2 bg-orange rounded-sm block"></span>
                <span class="w-6 h-2 bg-orange rounded-sm block"></span>
                <span class="w-6 h-2 bg-orange rounded-sm block"></span>
            </button>
            <a href="{{ route('home') }}" class="font-manrope text-white text-xl font-semibold">
                Lost &amp; Found
            </a>
        </div>

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Right side --}}
        <div class="flex items-center gap-3">
            @auth
                {{-- FITUR UNGGULAN: Notification Bell --}}
                <div class="relative" id="notif-wrapper">
                    <button id="notif-btn"
                        class="relative p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition"
                        aria-label="Notifikasi">
                        <svg id="bell-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        {{-- Badge counter --}}
                        <span id="notif-badge"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold hidden">
                            0
                        </span>
                    </button>

                    {{-- Dropdown notifikasi --}}
                    <div id="notif-dropdown"
                        class="notif-dropdown hidden absolute right-0 top-12 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                        <div class="flex items-center justify-between px-4 py-3 bg-navy text-white">
                            <span class="font-manrope font-semibold text-sm">Notifikasi</span>
                            <button id="mark-all-read" class="text-xs text-blue-200 hover:text-white transition">
                                Tandai semua dibaca
                            </button>
                        </div>
                        <div id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                            <p class="text-center text-gray-400 text-sm py-8">Tidak ada notifikasi baru</p>
                        </div>
                    </div>
                </div>

                {{-- User avatar --}}
                <div class="relative" id="user-menu-wrapper">
                    <button id="user-menu-btn" class="flex items-center gap-2 p-1 rounded-full hover:bg-white/10 transition">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                class="w-8 h-8 rounded-full object-cover border-2 border-white/30" alt="avatar">
                        @else
                            <div class="w-8 h-8 rounded-full bg-orange flex items-center justify-center text-white text-sm font-bold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </button>
                    <div id="user-menu" class="hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profil Saya</a>
                        <a href="{{ route('my-reports') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ Auth::user()->isAdmin() ? 'Daftar Laporan' : 'Laporan Saya' }}</a>
                        @unless(Auth::user()->isAdmin())
                        <a href="{{ route('claims.my-claims') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Klaim Saya</a>
                        @endunless
                        @if(Auth::user()->isAdmin())
                            <hr class="my-1">
                            <a href="{{ route('admin.claims.index') }}" class="block px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 font-medium">Admin Panel</a>
                        @endif
                        <hr class="my-1">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-white/80 hover:text-white text-sm px-3 py-2 rounded-lg hover:bg-white/10 transition">Masuk</a>
                <a href="{{ route('register') }}" class="bg-orange hover:bg-orange-hover text-white text-sm px-4 py-2 rounded-lg transition font-medium">Daftar</a>
            @endguest
        </div>
    </div>
</nav>

{{-- ────── OVERLAY (mobile only) ────── --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 md:hidden hidden" aria-hidden="true"></div>

{{-- ────── FLASH MESSAGES ────── --}}
@if(session('success'))
    <meta name="flash-success" content="{{ session('success') }}">
@endif
@if(session('report_code'))
    <meta name="flash-report-code" content="{{ session('report_code') }}">
@endif
@if(session('error'))
    <meta name="flash-error" content="{{ session('error') }}">
@endif

{{-- ────── BODY LAYOUT ────── --}}
<div class="flex flex-1 max-w-6xl mx-auto w-full px-4 pt-4 pb-6 gap-6">
    {{-- Sidebar (mobile: slide, desktop: tetap) --}}
    <aside id="sidebar"
        class="fixed md:sticky left-0 top-0 md:top-20 h-full md:h-auto w-64 bg-white shadow-2xl md:shadow-sm z-50
               -translate-x-full md:translate-x-0 transition-transform duration-300
               pt-6 md:pt-0 flex flex-col flex-shrink-0 md:rounded-2xl md:overflow-hidden">
        <div class="px-4 pb-4 border-b border-gray-300 md:hidden flex items-center justify-between">
            <span class="font-manrope font-bold text-navy text-lg">Lost &amp; Found</span>
            <button id="sidebar-close" class="text-gray-500 hover:text-gray-700 p-1">✕</button>
        </div>
        <div class="flex-1 bg-white overflow-y-auto">
            <div class="p-3">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider px-2 mb-2">Menu</p>
                <nav class="space-y-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('home') ? 'bg-orange text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Beranda
                    </a>
                    <a href="{{ route('items.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('items.index') ? 'bg-orange text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                        Barang Temuan
                    </a>
                    @auth
                    @unless(Auth::user()->isAdmin())
                    <a href="{{ route('items.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('items.create') ? 'bg-orange text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Lapor Penemuan
                    </a>
                    @endunless
                    <a href="{{ route('my-reports') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('my-reports') ? 'bg-orange text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ Auth::user()->isAdmin() ? 'Daftar Laporan' : 'Laporan Saya' }}
                    </a>
                    @unless(Auth::user()->isAdmin())
                    <a href="{{ route('claims.my-claims') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('claims.my-claims') ? 'bg-orange text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Klaim Saya
                    </a>
                    @endunless
                    @endauth
                </nav>
                <hr class="my-3 border-gray-100">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider px-2 mb-2">Lain-Lain</p>
                <nav class="space-y-1">
                    @auth
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('profile') ? 'bg-orange text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil
                    </a>
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.claims.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-orange-600 hover:bg-orange-50 font-medium">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Admin Panel
                    </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-red-500 hover:bg-red-50">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
                        Masuk / Daftar
                    </a>
                    @endauth
                </nav>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="flex-1 min-w-0">
        @yield('content')
    </main>
</div>

{{-- ────── FOOTER ────── --}}
<footer class="bg-navy text-white/70 text-center text-xs py-4 mt-8">
    <p>© {{ date('Y') }} Lost &amp; Found — FILKOM UB</p>
</footer>

{{-- ────── JAVASCRIPT ────── --}}
<script>
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

    const icons = {
        success: '✅', danger: '❌', warning: '⚠️', info: 'ℹ️'
    };

    toast.innerHTML = `
        <span class="text-lg mt-0.5">${icons[type] || '🔔'}</span>
        <div class="flex-1">
            <p class="text-sm text-gray-800 leading-snug">${message}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 text-lg leading-none ml-1">×</button>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.remove(), duration);
}

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
</script>

{{-- ────── AUTH-SPECIFIC JAVASCRIPT ────── --}}
@auth
<script>
// ── FITUR UNGGULAN: Ajax Notification Polling ───────────────────────────────
const POLL_INTERVAL = 15000;
let lastNotifCount  = 0;

const notifBtn      = document.getElementById('notif-btn');
const notifBadge    = document.getElementById('notif-badge');
const notifDropdown = document.getElementById('notif-dropdown');
const notifList     = document.getElementById('notif-list');
const bellIcon      = document.getElementById('bell-icon');
const markAllBtn    = document.getElementById('mark-all-read');

const notifPollUrl  = document.querySelector('meta[name="route-notifications-poll"]').content;
const notifMarkReadUrl = document.querySelector('meta[name="route-notifications-mark-read"]').content;

notifBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    notifDropdown.classList.toggle('hidden');
    notifWrapper.classList.remove('hidden');
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
            <div class="flex gap-3 items-start">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                    ${n.color === 'success' ? 'bg-green-100' : n.color === 'danger' ? 'bg-red-100' : 'bg-blue-100'}">
                    <span class="text-sm">${n.color === 'success' ? '✅' : n.color === 'danger' ? '❌' : '🔔'}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-800 leading-tight">${n.title}</p>
                    <p class="text-xs text-gray-500 mt-0.5 leading-snug">${n.message}</p>
                    <p class="text-xs text-gray-400 mt-1">${n.created_at}</p>
                </div>
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
</script>
@endauth

@stack('scripts')
</body>
</html>