{{-- resources/views/layouts/_navbar.blade.php --}}
<nav class="bg-navy shadow-md">
    <div class="px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <button id="sidebar-toggle" class="flex md:hidden flex-col gap-[5px] p-1 cursor-pointer" aria-label="Toggle menu">
                <span class="w-6 h-2 bg-orange rounded-sm block"></span>
                <span class="w-6 h-2 bg-orange rounded-sm block"></span>
                <span class="w-6 h-2 bg-orange rounded-sm block"></span>
            </button>
            <a href="{{ route('home') }}" class="font-manrope text-white text-xl font-semibold">Lost &amp; Found</a>
        </div>

        <div class="flex-1"></div>

        <div class="flex items-center gap-3">
            @auth
            <div class="relative" id="notif-wrapper">
                <button id="notif-btn"
                    class="relative p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition"
                    aria-label="Notifikasi">
                    <svg id="bell-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span id="notif-badge"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold hidden">0</span>
                </button>

                <div id="notif-dropdown"
                    class="notif-dropdown hidden absolute right-0 top-12 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                    <div class="flex items-center justify-between px-4 py-3 bg-navy text-white">
                        <span class="font-manrope font-semibold text-sm">Notifikasi</span>
                        <button id="mark-all-read" class="text-xs text-blue-200 hover:text-white transition">Tandai semua dibaca</button>
                    </div>
                    <div id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                        <p class="text-center text-gray-400 text-sm py-8">Tidak ada notifikasi baru</p>
                    </div>
                </div>
            </div>

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
            @endauth
        </div>
    </div>
</nav>