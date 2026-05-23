@php
$sidebarProjects = $projects ?? collect();
$archivedProjects = $archivedProjects?? collect();
$currentProject  = $currentProject  ?? null;

$isAdmin = $isAdmin = auth()->user()->isAdmin();
$initials = auth()->user()->initials();
$displayName = auth()->user()->name;

$colors = ['#6366f1','#8b5cf6','#ec4899','#f43f5e','#f97316','#22c55e','#14b8a6','#3b82f6'];
$hash   = array_sum(array_map('ord', str_split($displayName)));
$avatarBg = $colors[$hash % count($colors)];
@endphp

<aside class="sidebar" x-data="{ archivesOpen: false }">

    {{-- Brand --}}
    <div class="sidebar__brand">
        <div class="sidebar__brand-mark">BF</div>
        <span>BrainFlow</span>
    </div>

    {{-- Primary nav --}}
    <div class="sidebar__section">Navigazione</div>

    <a href="{{ route('projects.index') }}"
       class="nav-item {{ request()->routeIs('projects.index') ? 'is-active' : '' }}">
        <x-icon name="grid" />
        <span>Tutti i Progetti</span>
    </a>

    @if($isAdmin)
    <a href="{{ route('admin.users') }}"
       class="nav-item {{ request()->routeIs('admin.users') ? 'is-active' : '' }}">
        <x-icon name="shield" />
        <span>Utenti</span>
    </a>
    @endif

    {{-- Active projects --}}
    @if($sidebarProjects->count() > 0)
    <div class="sidebar__section">Progetti attivi</div>

    @foreach($sidebarProjects as $proj)
        @php
        $projId   = $proj['id'] ?? $proj->id;
        $projName = $proj['name'] ?? $proj->name;
        $isActive = $currentProject && (($currentProject['id'] ?? $currentProject->id) === $projId);
        @endphp
        <a href="{{ route('projects.show', $projId) }}"
           class="nav-item {{ $isActive ? 'is-active' : '' }}">
            <span class="nav-item__dot"></span>
            <span>{{ $projName }}</span>
        </a>
    @endforeach
    @endif

    {{-- Archived projects --}}
    @if($archivedProjects->count() > 0)
    <button class="nav-item" @click="archivesOpen = !archivesOpen">
        <x-icon name="archive" />
        <span>Archivio</span>
        <span class="nav-item__count">{{ $archivedProjects->count() }}</span>
        <x-icon :name="'chevDown'" size="sm" />
    </button>

    <div x-show="archivesOpen" style="padding-left:18px">
        @foreach($archivedProjects as $proj)
        @php
        $projId   = $proj['id'] ?? $proj->id;
        $projName = $proj['name'] ?? $proj->name;
        @endphp
        <a href="{{ route('projects.show', $projId) }}"
           class="nav-item" style="opacity:0.65">
            <span class="nav-item__dot"></span>
            <span>{{ $projName }}</span>
        </a>
        @endforeach
    </div>
    @endif

    <div style="flex:1"></div>

    {{-- User profile + logout --}}
    <div class="divider"></div>
    <div class="row" style="padding:6px 8px;gap:10px">
        <span class="avatar avatar--sm" style="background:{{ $avatarBg }};flex-shrink:0">
            {{ $initials }}
        </span>
        <div class="col" style="gap:0;flex:1;min-width:0">
            <span style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $displayName }}
            </span>
            <span class="muted" style="font-size:11px">
                {{ $isAdmin ? 'Admin' : 'Utente' }}
            </span>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn--ghost btn--icon" title="Esci">
                <x-icon name="logout" />
            </button>
        </form>
    </div>

</aside>
