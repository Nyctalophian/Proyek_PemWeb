{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-check" content="{{ Auth::check() ? '1' : '0' }}">
    <meta name="route-notifications-poll" content="{{ route('notifications.poll') }}">
    <meta name="route-notifications-mark-read" content="{{ route('notifications.mark-read') }}">
    <title>@yield('title', 'Lost & Found') — FILKOM UB</title>

    {{-- Tailwind CSS via CDN --}}
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
    @vite('resources/js/app.js')
    @stack('styles')
</head>
<body class="bg-body min-h-screen flex flex-col">

{{-- Toast container --}}
<div id="toast-container"></div>

{{-- Navbar --}}
@include('layouts._navbar')

{{-- Mobile overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 md:hidden hidden" aria-hidden="true"></div>

{{-- Flash messages --}}
@if(session('success'))
    <meta name="flash-success" content="{{ session('success') }}">
@endif
@if(session('report_code'))
    <meta name="flash-report-code" content="{{ session('report_code') }}">
@endif
@if(session('error'))
    <meta name="flash-error" content="{{ session('error') }}">
@endif

{{-- Body layout --}}
<div class="flex flex-1 w-full px-6 pt-4 pb-6 gap-6">
    @include('layouts._sidebar')
    <main class="flex-1 min-w-0">
        @yield('content')
    </main>
</div>

{{-- Footer --}}
<footer class="bg-navy text-white/70 text-center text-xs py-4 mt-8">
    <p>&copy; {{ date('Y') }} Lost &amp; Found — FILKOM UB</p>
</footer>

{{-- Scripts --}}
@stack('scripts')
</body>
</html>
