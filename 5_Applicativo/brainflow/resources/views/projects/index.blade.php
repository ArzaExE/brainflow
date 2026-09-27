@extends('layouts.app')

@section('title', 'Progetti')

@section('content')

<div x-data="projectsIndex()" class="col" style="flex:1">

    {{-- Top bar --}}
    <div class="topbar">
        <div class="topbar__crumbs">
            <strong>Tutti i Progetti</strong>
        </div>
        <div class="topbar__right">
            @if($canCreateProject)
                <button class="btn btn--accent" @click="showCreate = true">
                    <x-icon name="plus" />
                    Nuovo progetto
                </button>
            @endif
        </div>
    </div>

    <div class="content">

        {{-- Active projects --}}
        @if($projects->count() > 0)
        <div class="section-title">
            Attivi
            <span class="mono">{{ $projects->count() }}</span>
        </div>

        <div class="proj-grid">
            @foreach($projects as $project)
            @php
            $id       = $project->id;
            $name     = $project->name;
            $desc     = $project->description ?? null;
            $priority = $project->priority ?? 'medium';
            $total    = $project->tasks()->count() ?? 0;
            $members  = $project->members;
            $pct      = $project->completionPercent();
            $canCreateProject ?? false;
            @endphp
            <a href="{{ route('projects.show', $id) }}" class="proj-card">
                <div class="proj-card__hd">
                    <div style="flex:1">
                        <div class="proj-card__title">{{ $name }}</div>
                        @if($desc)
                        <div class="proj-card__desc">{{ $desc }}</div>
                        @endif
                    </div>
                    <x-priority-badge :priority="$priority" />
                </div>

                <div>
                    <div class="progress">
                        <div class="progress__fill" style="width:{{ $pct }}%"></div>
                    </div>
                </div>

                <div class="proj-card__foot">
                    <div class="proj-card__progress">
                        <span>{{ $pct }}%</span>
                        <span>·</span>
                        <span>{{ $total }} task</span>
                    </div>
                    <div class="avatar-stack">
                        @foreach(collect($members)->take(4) as $member)
                        <x-avatar :user="$member" size="sm" />
                        @endforeach
                        @if(collect($members)->count() > 4)
                        <span class="avatar avatar--sm avatar--more">+{{ collect($members)->count() - 4 }}</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
            <div class="empty" style="margin-top:40px">
                <x-icon name="folder" size="lg" />
                <strong>Nessun progetto</strong>
                @if($canCreateProject)
                    Crea il tuo primo progetto per iniziare.
                @else
                    Non sei ancora stato aggiunto a nessun progetto. Contatta un Project Manager per essere invitato.
                @endif
            </div>
        @endif

        {{-- Archived projects --}}
        @if(isset($archivedProjects) && $archivedProjects->count() > 0)
        <div class="divider" style="margin:32px 0 16px"></div>
        <div class="section-title">
            Archiviati
            <span class="badge badge--archived">{{ $archivedProjects->count() }}</span>
        </div>

        <div class="proj-grid">
            @foreach($archivedProjects as $project)
            @php
            $id       = $project->id;
            $name     = $project->name;
            $total    = $project->tasks()->count() ?? 0;
            $members  = $project->members;
            $pct      = $project->completionPercent();
            @endphp
            <a href="{{ route('projects.show', $id) }}" class="proj-card is-archived">
                <div class="proj-card__hd">
                    <div style="flex:1">
                        <div class="row" style="gap:8px">
                            <div class="proj-card__title">{{ $name }}</div>
                            <span class="badge badge--archived">Archiviato</span>
                        </div>
                    </div>
                </div>
                <div class="proj-card__foot">
                    <span class="muted" style="font-size:12px">{{ $pct }}% completato · {{ $total }} task</span>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Create project modal --}}
    @if($canCreateProject)
    <div class="modal" x-show="showCreate" @click.self="showCreate=false" x-cloak>
        <div class="modal__box" @click.stop>
            <div class="modal__hd">
                <div class="modal__title">Nuovo progetto</div>
                <div class="modal__sub">Crea un nuovo progetto per il tuo team.</div>
            </div>
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="modal__bd">
                    {{-- Campo Nome --}}
                    <div class="field">
                        <label class="field__label" for="proj_name">
                            Nome progetto <span class="req">*</span>
                        </label>
                        <input
                            id="proj_name"
                            class="input {{ $errors->has('name') ? 'is-error' : '' }}"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="es. Sito aziendale"
                            required
                        >
                        @error('name')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Campo Descrizione --}}
                    <div class="field">
                        <label class="field__label" for="proj_desc">Descrizione</label>
                        <textarea
                            id="proj_desc"
                            class="textarea {{ $errors->has('description') ? 'is-error' : '' }}"
                            name="description"
                            placeholder="Descrizione opzionale..."
                            rows="2"
                        >{{ old('description') }}</textarea>
                        @error('description')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Campo Priorità --}}
                    <div class="field">
                        <label class="field__label" for="proj_priority">Priorità</label>
                        <select
                            id="proj_priority"
                            class="select {{ $errors->has('priority') ? 'is-error' : '' }}"
                            name="priority"
                        >
                            <option value="low"    {{ old('priority') === 'low' ? 'selected' : '' }}>Bassa</option>
                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high"   {{ old('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                        </select>
                        @error('priority')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                <div class="modal__ft">
                    <button type="button" class="btn" @click="showCreate=false">Annulla</button>
                    <button type="submit" class="btn btn--accent">Crea progetto</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('projectsIndex', () => ({
                showCreate: {{ $canCreateProject && (session('show_create') || $errors->any()) ? 'true' : 'false' }},
            }));
        });
    </script>
@endpush
@endsection
