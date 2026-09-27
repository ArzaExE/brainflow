<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BrainFlow · @yield('title', 'Kanban')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app">
        @include('components.sidebar', [
            'sidebarProjects'  => $sidebarProjects  ?? collect(),
            'archivedProjects' => $archivedProjects ?? collect(),
            'currentProject'   => $currentProject   ?? null,
            'authUser'         => $authUser          ?? null,
        ])

        <main class="main">
            @yield('content')
        </main>
    </div>

    {{-- Toast container --}}
    @include('components.toast')

    @stack('modals')

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
