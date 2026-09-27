@extends('layouts.app')

@section('title', $project->name)

@section('content')

@php
    $canEdit          = $canEdit ?? false;
    $isPm             = $isPm ?? false;
    $isArchived       = $isArchived ?? false;
    $canManageProject = $canManageProject ?? false;
@endphp

<script>
    window.brainflowData = {
        project:     {!! json_encode($project) !!},
        columns:     {!! json_encode($columns) !!},
        tasks:       {!! json_encode($tasks) !!},
        members:     {!! json_encode($members) !!},
        labels:      {!! json_encode($labels) !!},
        activity:    {!! json_encode($activity) !!},
        isPm:        {{ $isPm ? 'true' : 'false' }},
        canEdit:     {{ $canEdit ? 'true' : 'false' }},
    };
</script>

<div
    x-data="projectBoard(
        brainflowData.project,
        brainflowData.columns,
        brainflowData.columnTypes,
        brainflowData.tasks,
        brainflowData.members,
        brainflowData.labels,
        brainflowData.activity,
        brainflowData.isPm,
        brainflowData.canEdit
    )"
    style="display:flex;flex-direction:column;height:100vh;overflow:hidden"
>

    {{-- ── Project toolbar ─────────────────────────────── --}}
    <div class="board-toolbar">
        <div class="board-toolbar__title">
            <span x-text="project.name"></span>
            <span
                class="badge"
                :class="{
            'badge--prio-high': project.priority === 'high',
            'badge--prio-med':  project.priority === 'medium',
            'badge--prio-low':  project.priority === 'low',
        }"
                x-text="priorityLabel(project.priority)"
            ></span>
            <span x-show="project.archived_at || project.is_archived" class="badge badge--archived">Archiviato</span>
        </div>

        <div class="board-toolbar__meta">
            <div class="avatar-stack">
                <template x-for="m in members.slice(0,5)" :key="m.id">
                    <span class="avatar avatar--sm"
                          :style="`background:${userColor(m.name)}`"
                          :title="m.name"
                          x-text="initials(m.name)">
                    </span>
                </template>
                <span x-show="members.length > 5" class="avatar avatar--sm avatar--more"
                      x-text="`+${members.length - 5}`"></span>
            </div>
            <span class="muted" style="font-size:12px">
                <span x-text="tasks.length"></span> task ·
                <span x-text="completionPct()"></span>% completato
            </span>
        </div>

        <div class="board-toolbar__right">
            @if($isArchived)
                @if($canManageProject)
                    <form action="{{ route('projects.unarchive', $project->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn">
                            <x-icon name="archive" /> Ripristina
                        </button>
                    </form>
                @endif
            @else
                <div style="position:relative" x-data="{ open: false }" x-show="isPm">
                    <button class="btn" @click="open = !open" @click.outside="open = false">
                        <x-icon name="moreV" />
                    </button>
                    <div class="popover" x-show="open" x-transition style="right:0;top:36px">
                        <button class="popover__item" @click="showEdit=true; open=false">
                            <x-icon name="pencil" size="sm" /> Modifica progetto
                        </button>
                        <div class="popover__sep"></div>
                        <button class="popover__item" @click="showArchiveConfirm=true; open=false">
                            <x-icon name="archive" size="sm" /> Archivia
                        </button>
                        <button class="popover__item is-danger" @click="showDeleteConfirm=true; open=false">
                            <x-icon name="trash" size="sm" /> Elimina
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Tabs ─────────────────────────────────────────── --}}
    <div class="tabs">
        <button class="tab" :class="activeTab==='board' ? 'is-active' : ''" @click="activeTab='board'">
            <x-icon name="layout" size="sm" /> Board
        </button>
        <button class="tab" :class="activeTab==='dashboard' ? 'is-active' : ''" @click="activeTab='dashboard'">
            <x-icon name="bar-chart" size="sm" /> Dashboard
        </button>
        <button class="tab" :class="activeTab==='members' ? 'is-active' : ''" @click="activeTab='members'">
            <x-icon name="users" size="sm" /> Membri
            <span class="tab__count" x-text="members.length"></span>
        </button>
        <button class="tab" :class="activeTab==='labels' ? 'is-active' : ''" @click="activeTab='labels'">
            <x-icon name="tag" size="sm" /> Etichette
        </button>
        <button class="tab" :class="activeTab==='activity' ? 'is-active' : ''" @click="activeTab='activity'">
            <x-icon name="activity" size="sm" /> Attività
        </button>
    </div>

    {{-- ── Board tab ────────────────────────────────────── --}}
    <div x-show="activeTab==='board'" class="board" style="flex:1;overflow-x:auto;overflow-y:hidden">
        <template x-for="col in orderedColumns()" :key="col.id">
            <div class="column"
                 :data-column-id="col.id"
                 :class="{'is-dragging': colDragId === col.id, 'column--drop-active': colDragOverId === col.id && colDragId !== col.id}"
                 @dragover="if (isPm && colDragId && colDragId !== col.id) { $event.preventDefault(); colDragOverId = col.id; }"
                 @dragleave="colDragOverId = null"
                 @drop="if (isPm && colDragId && colDragId !== col.id) { $event.preventDefault(); moveColumn(colDragId, orderedColumns().findIndex(c => c.id === col.id)); colDragId = null; colDragOverId = null; }">
                {{-- Column header --}}
                <div class="column__hd"
                     :draggable="isPm"
                     @dragstart="if (!isPm) return; colDragId = col.id; $event.dataTransfer.effectAllowed = 'move'"
                     @dragend="colDragId = null; colDragOverId = null"
                     :style="isPm ? 'cursor:grab' : 'cursor:default'">
                    <svg style="color:var(--muted);flex-shrink:0;margin-right:2px" viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><circle cx="9" cy="5" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="9" cy="19" r="1.4"/><circle cx="15" cy="5" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="15" cy="19" r="1.4"/></svg>
                    <span class="column__title" x-text="col.name"></span>
                    <span class="column__count" x-text="getColumnTasks(col.id).length"></span>
                    <div class="column__menu"
                         x-show="isPm"
                         style="position:relative"
                         x-data="{ open: false }" @dragstart.stop>
                        <button class="menubtn" draggable="false" @click="open=!open" @click.outside="open=false">
                            <x-icon name="more" size="sm" />
                        </button>
                        <div class="popover" x-show="open" x-transition style="right:0;top:32px;min-width:160px">
                            <button class="popover__item is-danger"
                                    @click="askDeleteColumn(col); open=false">
                                <x-icon name="trash" size="sm" /> Elimina colonna
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tasks --}}
                <div class="column__body"
                     :data-column-id="col.id"
                     x-ref="col_{{ '__id__' }}"
                     :id="`col-${col.id}`">
                    <template x-for="task in getColumnTasks(col.id)" :key="task.id">
                        <button class="task-card" @click="openDrawer(task.id)" :data-task-id="task.id">

                            {{-- Labels --}}
                            <div class="task-card__labels" x-show="task.labels && task.labels.length > 0">
                                <template x-for="lbl in (task.labels || [])" :key="lbl.id">
                                    <span class="chip">
                                        <span class="chip__dot" :style="`background:${lbl.color}`"></span>
                                        <span x-text="lbl.name"></span>
                                    </span>
                                </template>
                            </div>

                            {{-- Title --}}
                            <div class="task-card__title" x-text="task.title"></div>

                            {{-- Meta: priority, due date --}}
                            <div class="task-card__meta">
                                <span class="task-card__meta-item"
                                      :class="task.priority === 'high' ? 'badge--prio-high' : (task.priority === 'medium' ? 'badge--prio-med' : 'badge--prio-low')"
                                      style="padding:1px 5px;border-radius:4px;font-size:10px"
                                      x-text="priorityLabel(task.priority)"
                                ></span>
                                <span x-show="task.due_date"
                                      class="task-card__meta-item"
                                      :class="isOverdue(task) ? 'is-overdue' : ''">
                                    <svg class="icon icon--sm" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <span x-text="fmtDate(task.due_date)"></span>
                                </span>
                            </div>

                            {{-- Bottom: subtasks + assignees --}}
                            <div class="task-card__bottom">
                                <span x-show="task.subtasks && task.subtasks.length > 0"
                                      class="muted" style="font-size:11px">
                                    <span x-text="task.subtasks.filter(s=>s.done).length"></span>/<span x-text="task.subtasks.length"></span>
                                </span>
                                <div class="task-card__assignees avatar-stack" style="margin-left:auto">
                                    <template x-for="u in (task.assignees||[]).slice(0,3)" :key="u.id">
                                        <span class="avatar avatar--sm"
                                              :style="`background:${userColor(u.name)}`"
                                              :title="u.name"
                                              x-text="initials(u.name)">
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Add task --}}
                <div class="column__add" x-show="canEdit">
                    <button class="btn btn--ghost btn--sm btn--block"
                            @click="openNewTask(col.id)">
                        <x-icon name="plus" size="sm" /> Aggiungi task
                    </button>
                </div>
            </div>
        </template>

        {{-- Add column button --}}
        <button class="column-add-btn" @click="openNewColModal()" x-show="isPm">
            <x-icon name="plus" /> Nuova colonna
        </button>
    </div>

    {{-- ── Dashboard tab ────────────────────────────────── --}}
    <div x-show="activeTab==='dashboard'" class="content" style="flex:1;overflow-y:auto">
        {{-- KPI grid --}}
        <div class="kpi-grid">
            <div class="kpi">
                <div class="kpi__label">Completamento</div>
                <div class="kpi__value" x-text="`${completionPct()}%`"></div>
                <div class="progress progress--accent" style="margin-top:4px">
                    <div class="progress__fill" :style="`width:${completionPct()}%`"></div>
                </div>
            </div>
            <div class="kpi">
                <div class="kpi__label">Task totali</div>
                <div class="kpi__value" x-text="tasks.length"></div>
                <div class="kpi__sub" x-text="`${doneTasks()} completati`"></div>
            </div>
            <div class="kpi">
                <div class="kpi__label">In scadenza</div>
                <div class="kpi__value" x-text="overdueTasks().length" :style="overdueTasks().length > 0 ? 'color:var(--danger)' : ''"></div>
                <div class="kpi__sub">task in ritardo</div>
            </div>
            <div class="kpi">
                <div class="kpi__label">Membri attivi</div>
                <div class="kpi__value" x-text="members.length"></div>
                <div class="kpi__sub">nel progetto</div>
            </div>
        </div>

        {{-- Tasks by column --}}
        <div class="card" style="margin-top:24px">
            <div class="card__hd"><div class="card__title">Task per colonna</div></div>
            <div class="card__bd col" style="gap:4px">
                <template x-for="col in columns" :key="col.id">
                    <div class="bar-row">
                        <div class="bar-row__label" x-text="col.name"></div>
                        <div class="bar-row__bar">
                            <div :style="`width:${tasks.length ? (getColumnTasks(col.id).length/tasks.length*100) : 0}%`"></div>
                        </div>
                        <div class="bar-row__val" x-text="getColumnTasks(col.id).length"></div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Tasks by member --}}
        <div class="card" style="margin-top:14px">
            <div class="card__hd"><div class="card__title">Task per membro</div></div>
            <div class="card__bd col" style="gap:4px">
                <template x-for="m in members" :key="m.id">
                    <div class="bar-row">
                        <div class="bar-row__label">
                            <span class="avatar avatar--sm" :style="`background:${userColor(m.name)}`" x-text="initials(m.name)"></span>
                            <span x-text="m.name"></span>
                        </div>
                        <div class="bar-row__bar">
                            <div :style="`width:${tasks.length ? (getMemberTasks(m.id).length/tasks.length*100) : 0}%`"></div>
                        </div>
                        <div class="bar-row__val" x-text="getMemberTasks(m.id).length"></div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Overdue tasks --}}
        <div x-show="overdueTasks().length > 0" class="card" style="margin-top:14px">
            <div class="card__hd">
                <div class="card__title" style="color:var(--danger)">
                    <x-icon name="alert" size="sm" /> Task in ritardo
                </div>
            </div>
            <div class="card__bd col" style="gap:6px">
                <template x-for="t in overdueTasks()" :key="t.id">
                    <button class="row" style="text-align:left;padding:6px 8px;border-radius:6px"
                            @click="openDrawer(t.id); activeTab='board'">
                        <span class="badge badge--overdue" style="font-size:10px">SCADUTO</span>
                        <span x-text="t.title" style="font-size:13px;font-weight:500"></span>
                        <span class="muted" x-text="fmtDate(t.due_date)" style="margin-left:auto;font-size:11px"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ── Members tab ──────────────────────────────────── --}}
    <div x-show="activeTab==='members'" class="content" style="flex:1;overflow-y:auto">
        <div class="row" style="margin-bottom:16px">
            <div class="section-title" style="margin-bottom:0">Membri del progetto</div>
            <div style="flex:1"></div>
            <button class="btn btn--accent" @click="openInviteModal()" x-show="isPm">
                <x-icon name="plus" /> Invita
            </button>
        </div>

        <div class="col" style="gap:8px">
            <template x-for="m in members" :key="m.id">
                <div class="card">
                    <div class="card__bd row">
                <span class="avatar avatar--lg"
                      :style="`background:${userColor(m.name)}`"
                      x-text="initials(m.name)"></span>
                        <div class="col" style="gap:2px;flex:1">
                            <strong x-text="m.name" style="font-size:14px"></strong>
                            <span class="muted" x-text="m.email" style="font-size:12px"></span>
                        </div>
                        <select class="select" style="width:auto;font-size:12px"
                                :disabled="!isPm"
                                x-model="m.pivot_role"
                                @change="changeMemberRole(m.id, $event.target.value)">
                            <option value="pm">Project Manager</option>
                            <option value="developer">Developer</option>
                            <option value="viewer">Viewer</option>
                        </select>
                        <button class="btn btn--ghost btn--icon btn--sm"
                                @click="askDeleteMember(m)"
                                title="Rimuovi dal progetto"
                                x-show="isPm">
                            <x-icon name="close" size="sm" />
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Labels tab ───────────────────────────────────── --}}
    <div x-show="activeTab==='labels'" class="content" style="flex:1;overflow-y:auto">
        <div class="row" style="margin-bottom:16px">
            <div class="section-title" style="margin-bottom:0">Etichette</div>
            <div style="flex:1"></div>
            <button class="btn btn--accent" @click="showNewLabel=true" x-show="canEdit">
                <x-icon name="plus" /> Nuova etichetta
            </button>
        </div>

        <div class="section-title" style="font-size:11px;color:var(--muted)">DI SISTEMA</div>
        <div class="col" style="gap:6px;margin-bottom:20px">
            <template x-for="lbl in labels.filter(l=>l.is_system)" :key="lbl.id">
                <div class="card">
                    <div class="card__bd row">
                        <span class="chip">
                            <span class="chip__dot" :style="`background:${lbl.color}`"></span>
                            <span x-text="lbl.name"></span>
                        </span>
                        <span class="muted" style="font-size:12px">Sistema — non modificabile</span>
                        <div style="flex:1"></div>
                    </div>
                </div>
            </template>
        </div>

        <div class="section-title" style="font-size:11px;color:var(--muted)">PERSONALIZZATE</div>
        <div class="col" style="gap:6px">
            <template x-for="lbl in labels.filter(l=>!l.is_system)" :key="lbl.id">
                <div class="card">
                    <div class="card__bd row">
                        <span class="chip">
                            <span class="chip__dot" :style="`background:${lbl.color}`"></span>
                            <span x-text="lbl.name"></span>
                        </span>
                        <input type="color" :value="lbl.color"
                               @change="updateLabelColor(lbl.id, $event.target.value)"
                               :disabled="!canEdit"
                               style="width:28px;height:28px;cursor:pointer;border:1px solid var(--border);border-radius:4px">
                        <div style="flex:1"></div>
                        <button class="btn btn--ghost btn--icon btn--sm"
                                @click="askDeleteLabel(lbl.id)"
                                x-show="canEdit">
                            <x-icon name="trash" size="sm" />
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="labels.filter(l=>!l.is_system).length === 0">
                <div class="empty" style="padding:20px">
                    Nessuna etichetta personalizzata.
                </div>
            </template>
        </div>
    </div>

    {{-- ── Activity tab ─────────────────────────────────── --}}
    <div x-show="activeTab==='activity'" class="content" style="flex:1;overflow-y:auto">
        <div class="section-title">Attività recente</div>
        <div class="activity-feed">
            <template x-for="entry in activity" :key="entry.id">
                <div class="activity-row">
                    <div class="activity-row__icon"
                         :style="{ background: userColor(entry.user_name||entry.user?.name||'?'), color: '#fff', fontSize: '9px', fontWeight: '600', letterSpacing: '-0.02em', fontFamily: 'var(--font-mono)' }"
                         x-text="initials(entry.user_name||entry.user?.name||'?')">
                    </div>
                    <div class="activity-row__text">
                        <b x-text="entry.user_name || entry.user?.name"></b>
                        <span x-text="' ' + activityText(entry)"></span>
                        <span x-show="entry.meta?.body" style="display:block;color:var(--muted);margin-top:3px"
                              x-text="entry.meta?.body"></span>
                    </div>
                    <div class="activity-row__time" x-text="fmtRelative(entry.created_at)"></div>
                </div>
            </template>
            <template x-if="activity.length === 0">
                <div class="empty" style="padding:20px">Nessuna attività registrata.</div>
            </template>
        </div>
    </div>

    {{-- ── Task detail drawer ───────────────────────────── --}}
    <template x-if="openTaskId !== null && taskDraft !== null">
        <div>
            <div class="scrim" @click="closeDrawer()"></div>
            <div class="drawer">
                <div class="drawer__hd">
                    <div class="drawer__crumb" x-text="columnName(openTask?.column_id)"></div>
                    <div style="flex:1"></div>
                    <button class="btn btn--ghost btn--icon" @click="closeDrawer()">
                        <x-icon name="close" />
                    </button>
                </div>

                <div class="drawer__bd">

                    {{-- Title --}}
                    <div class="field">
                        <input class="drawer__title-input"
                               :class="taskDraftErrors.title ? 'is-error' : ''"
                               x-model="taskDraft.title"
                               :disabled="!canEdit"
                               placeholder="Titolo task">
                        <template x-if="taskDraftErrors.title">
                            <span class="field__error" x-text="taskDraftErrors.title"></span>
                        </template>
                    </div>

                    <div class="detail-grid">
                        {{-- Priorità --}}
                        <span class="detail-grid__label">Priorità</span>
                        <select class="select"
                                :class="taskDraftErrors.priority ? 'is-error' : ''"
                                x-model="taskDraft.priority"
                                :disabled="!canEdit">
                            <option value="high">Alta</option>
                            <option value="medium">Media</option>
                            <option value="low">Bassa</option>
                        </select>

                        {{-- Assegnatari --}}
                        <span class="detail-grid__label">Assegnatari <span class="req">*</span></span>
                        <div class="col" style="gap:6px">
                            <div class="row" style="flex-wrap:wrap;gap:6px"
                                 x-show="taskDraft.assignee_ids.length > 0">
                                <template x-for="userId in taskDraft.assignee_ids" :key="userId">
                                <span class="chip">
                                    <span class="avatar avatar--sm"
                                          :style="`background:${userColor(memberFullName(userId))}`"
                                          x-text="initials(memberFullName(userId))"></span>
                                    <span x-text="memberFullName(userId)"></span>
                                    <button type="button" x-show="canEdit"
                                            @click="draftRemoveAssignee(userId)"
                                            style="opacity:0.6;margin-left:2px">
                                        <svg class="icon icon--sm" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </span>
                                </template>
                            </div>
                            <select class="select"
                                    :class="taskDraftErrors.assignees ? 'is-error' : ''"
                                    x-show="canEdit"
                                    @change="draftAddAssignee($event.target.value); $event.target.value=''">
                                <option value="">+ Aggiungi assegnatario</option>
                                <template x-for="m in draftAvailableMembers()" :key="m.id">
                                    <option :value="m.id" x-text="memberFullName(m.id)"></option>
                                </template>
                            </select>
                            <template x-if="taskDraftErrors.assignees">
                                <span class="field__error" x-text="taskDraftErrors.assignees"></span>
                            </template>
                        </div>

                        {{-- Scadenza --}}
                        <span class="detail-grid__label">Scadenza</span>
                        <input type="date" class="input"
                               :class="taskDraftErrors.due_date ? 'is-error' : ''"
                               x-model="taskDraft.due_date"
                               :disabled="!canEdit">

                        {{-- Etichette --}}
                        <span class="detail-grid__label">Etichette</span>
                        <div class="col" style="gap:6px">
                            <div class="row" style="flex-wrap:wrap;gap:4px"
                                 x-show="taskDraft.label_ids.length > 0">
                                <template x-for="labelId in taskDraft.label_ids" :key="labelId">
                                <span class="chip">
                                    <span class="chip__dot"
                                          :style="`background:${labelById(labelId)?.color}`"></span>
                                    <span x-text="labelById(labelId)?.name"></span>
                                    <button type="button" x-show="canEdit"
                                            @click="draftRemoveLabel(labelId)"
                                            style="opacity:0.6;margin-left:2px">
                                        <svg class="icon icon--sm" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </span>
                                </template>
                            </div>
                            <select class="select" x-show="canEdit"
                                    @change="draftAddLabel($event.target.value); $event.target.value=''">
                                <option value="">+ Aggiungi etichetta</option>
                                <template x-for="lbl in draftAvailableLabels()" :key="lbl.id">
                                    <option :value="lbl.id" x-text="lbl.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Descrizione --}}
                    <div class="field">
                        <label class="field__label">Descrizione</label>
                        <textarea class="textarea"
                                  :class="taskDraftErrors.description ? 'is-error' : ''"
                                  rows="3"
                                  x-model="taskDraft.description"
                                  :disabled="!canEdit"
                                  placeholder="Aggiungi una descrizione..."></textarea>
                    </div>

                    {{-- Sottoattività --}}
                    <div class="field">
                        <div class="section-title">
                            Sottoattività
                            <span class="mono" x-text="draftSubtaskStats()"></span>
                        </div>

                        <div class="subtasks" x-show="taskDraft.subtasks.length > 0">
                            <template x-for="(sub, idx) in taskDraft.subtasks" :key="idx">
                                <div class="subtask">
                                    <input type="checkbox" class="checkbox"
                                           x-model="sub.done"
                                           :disabled="!canEdit">
                                    <input type="text" class="input"
                                           x-model="sub.title"
                                           style="flex:1;font-size:13px"
                                           :disabled="!canEdit"
                                           placeholder="Titolo sottoattività">
                                    <button type="button" x-show="canEdit"
                                            class="btn btn--ghost btn--icon btn--sm"
                                            @click="draftRemoveSubtask(idx)">
                                        <x-icon name="close" size="sm" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="row" style="margin-top:8px;gap:6px" x-show="canEdit">
                            <input class="input" type="text"
                                   x-model="newDraftSubtaskTitle"
                                   placeholder="Aggiungi una sottoattività..."
                                   @keydown.enter.prevent="draftAddSubtask()">
                            <button type="button" class="btn btn--sm" @click="draftAddSubtask()">
                                Aggiungi
                            </button>
                        </div>

                        <template x-if="taskDraftErrors.subtasks">
                            <span class="field__error" x-text="taskDraftErrors.subtasks"></span>
                        </template>
                    </div>

                    {{-- Footer azioni --}}
                    <div class="divider"></div>
                    <div class="row" style="gap:8px;justify-content:flex-end">
                        <button x-show="canEdit"
                                class="btn btn--danger btn--sm"
                                style="margin-right:auto"
                                @click="askDeleteTask(openTask)">
                            <x-icon name="trash" size="sm" /> Elimina
                        </button>
                        <button class="btn" @click="closeDrawer()">
                            Annulla
                        </button>
                        <button x-show="canEdit"
                                class="btn btn--accent"
                                @click="saveTask()"
                                :disabled="savingTask">
                            <span x-text="savingTask ? 'Salvataggio...' : 'Salva modifiche'"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </template>

    {{-- ── Edit project modal ──────────────────────────── --}}
    <div class="modal" x-show="showEdit" @click.self="showEdit=false" x-cloak>
        <div class="modal__box" @click.stop>
            <div class="modal__hd">
                <div class="modal__title">Modifica progetto</div>
                <div class="modal__sub">Aggiorna i dettagli del progetto.</div>
            </div>
            <form action="{{ route('projects.update', $project->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal__bd">

                    {{-- Campo Nome --}}
                    <div class="field">
                        <label class="field__label" for="edit_name">
                            Nome progetto <span class="req">*</span>
                        </label>
                        <input
                            id="edit_name"
                            class="input {{ $errors->has('name') ? 'is-error' : '' }}"
                            type="text"
                            name="name"
                            value="{{ old('name', $project->name) }}"
                            placeholder="es. Sito aziendale"
                            required
                        >
                        @error('name')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Campo Descrizione --}}
                    <div class="field">
                        <label class="field__label" for="edit_desc">Descrizione</label>
                        <textarea
                            id="edit_desc"
                            class="textarea {{ $errors->has('description') ? 'is-error' : '' }}"
                            name="description"
                            placeholder="Descrizione opzionale..."
                            rows="3"
                        >{{ old('description', $project->description) }}</textarea>
                        @error('description')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Campo Priorità --}}
                    <div class="field">
                        <label class="field__label" for="edit_priority">Priorità</label>
                        <select
                            id="edit_priority"
                            class="select {{ $errors->has('priority') ? 'is-error' : '' }}"
                            name="priority"
                        >
                            <option value="low"    {{ old('priority', $project->priority) === 'low'    ? 'selected' : '' }}>Bassa</option>
                            <option value="medium" {{ old('priority', $project->priority) === 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high"   {{ old('priority', $project->priority) === 'high'   ? 'selected' : '' }}>Alta</option>
                        </select>
                        @error('priority')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                <div class="modal__ft">
                    <button type="button" class="btn" @click="showEdit=false">Annulla</button>
                    <button type="submit" class="btn btn--accent">Salva modifiche</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── New task modal ──────────────────────────────── --}}
    <template x-if="showNewTask">
        <div class="modal" @click.self="showNewTask=false">
            <div class="modal__box" @click.stop style="max-width:560px">
                <div class="modal__hd">
                    <div class="modal__title">Nuova task</div>
                    <div class="modal__sub" x-text="`Colonna: ${columnName(newTaskColId)}`"></div>
                </div>

                <div class="modal__bd">
                    <template x-if="newTaskErrors.column_id">
                        <div class="warn-banner" style="margin-bottom:12px" x-text="newTaskErrors.column_id"></div>
                    </template>

                    {{-- Titolo --}}
                    <div class="field">
                        <label class="field__label">Titolo <span class="req">*</span></label>
                        <input class="input"
                               :class="newTaskErrors.title ? 'is-error' : ''"
                               type="text"
                               x-model="newTaskTitle"
                               placeholder="es. Implementare login"
                               x-ref="newTaskInput">
                        <template x-if="newTaskErrors.title">
                            <span class="field__error" x-text="newTaskErrors.title"></span>
                        </template>
                    </div>

                    {{-- Priorità --}}
                    <div class="field">
                        <label class="field__label">Priorità</label>
                        <select class="select"
                                :class="newTaskErrors.priority ? 'is-error' : ''"
                                x-model="newTaskPriority">
                            <option value="high">Alta</option>
                            <option value="medium">Media</option>
                            <option value="low">Bassa</option>
                        </select>
                        <template x-if="newTaskErrors.priority">
                            <span class="field__error" x-text="newTaskErrors.priority"></span>
                        </template>
                    </div>

                    {{-- Scadenza --}}
                    <div class="field">
                        <label class="field__label">Scadenza</label>
                        <input type="date" class="input"
                               :class="newTaskErrors.due_date ? 'is-error' : ''"
                               x-model="newTaskDueDate">
                        <template x-if="newTaskErrors.due_date">
                            <span class="field__error" x-text="newTaskErrors.due_date"></span>
                        </template>
                    </div>

                    {{-- Assegnatari --}}
                    <div class="field">
                        <label class="field__label">
                            Assegnatari <span class="req">*</span>
                        </label>

                        {{-- Chip degli assegnatari già aggiunti --}}
                        <div class="row" style="flex-wrap:wrap;gap:6px;margin-bottom:8px"
                             x-show="newTaskAssignees.length > 0">
                            <template x-for="userId in newTaskAssignees" :key="userId">
                            <span class="chip">
                                <span class="avatar avatar--sm"
                                      :style="`background:${userColor(memberName(userId))}`"
                                      x-text="initials(memberName(userId))"></span>
                                <span x-text="memberName(userId)"></span>
                                <button type="button"
                                        @click="removeNewTaskAssignee(userId)"
                                        style="opacity:0.6;margin-left:2px">
                                    <svg class="icon icon--sm" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </span>
                            </template>
                        </div>

                        {{-- Select per aggiungere un assegnatario --}}
                        <select class="select"
                                :class="newTaskErrors.assignees ? 'is-error' : ''"
                                @change="addNewTaskAssignee($event.target.value); $event.target.value=''">
                            <option value="">+ Aggiungi assegnatario</option>
                            <template x-for="m in availableMembersForNewTask()" :key="m.id">
                                <option :value="m.id" x-text="m.name"></option>
                            </template>
                        </select>

                        <template x-if="newTaskErrors.assignees">
                            <span class="field__error" x-text="newTaskErrors.assignees"></span>
                        </template>
                    </div>

                    {{-- Etichette --}}
                    <div class="field">
                        <label class="field__label">Etichette</label>

                        <div class="row" style="flex-wrap:wrap;gap:4px;margin-bottom:8px"
                             x-show="newTaskLabels.length > 0">
                            <template x-for="labelId in newTaskLabels" :key="labelId">
                            <span class="chip">
                                <span class="chip__dot"
                                      :style="`background:${labelById(labelId)?.color}`"></span>
                                <span x-text="labelById(labelId)?.name"></span>
                                <button type="button"
                                        @click="removeNewTaskLabel(labelId)"
                                        style="opacity:0.6;margin-left:2px">
                                    <svg class="icon icon--sm" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </span>
                            </template>
                        </div>

                        <select class="select"
                                :class="newTaskErrors.labels ? 'is-error' : ''"
                                @change="addNewTaskLabel($event.target.value); $event.target.value=''">
                            <option value="">+ Aggiungi etichetta</option>
                            <template x-for="lbl in availableLabelsForNewTask()" :key="lbl.id">
                                <option :value="lbl.id" x-text="lbl.name"></option>
                            </template>
                        </select>
                        <template x-if="newTaskErrors.labels">
                            <span class="field__error" x-text="newTaskErrors.labels"></span>
                        </template>
                    </div>

                    {{-- Descrizione --}}
                    <div class="field">
                        <label class="field__label">Descrizione</label>
                        <textarea class="textarea"
                                  :class="newTaskErrors.description ? 'is-error' : ''"
                                  rows="3"
                                  x-model="newTaskDescription"
                                  placeholder="Descrizione opzionale..."></textarea>
                        <template x-if="newTaskErrors.description">
                            <span class="field__error" x-text="newTaskErrors.description"></span>
                        </template>
                    </div>

                    {{-- Sottoattività --}}
                    <div class="field">
                        <label class="field__label">Sottoattività</label>

                        <div class="subtasks" x-show="newTaskSubtasks.length > 0">
                            <template x-for="(sub, idx) in newTaskSubtasks" :key="idx">
                                <div class="subtask">
                                    <input type="checkbox" class="checkbox"
                                           x-model="sub.done">
                                    <input type="text" class="input"
                                           x-model="sub.title"
                                           style="flex:1;font-size:13px"
                                           placeholder="Titolo sottoattività">
                                    <button type="button"
                                            class="btn btn--ghost btn--icon btn--sm"
                                            @click="removeNewTaskSubtask(idx)">
                                        <x-icon name="close" size="sm" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="row" style="margin-top:8px;gap:6px">
                            <input class="input" type="text"
                                   x-model="newSubtaskDraft"
                                   placeholder="Aggiungi una sottoattività..."
                                   @keydown.enter.prevent="addNewTaskSubtask()">
                            <button type="button" class="btn btn--sm"
                                    @click="addNewTaskSubtask()">
                                Aggiungi
                            </button>
                        </div>

                        <template x-if="newTaskErrors.subtasks">
                            <span class="field__error" x-text="newTaskErrors.subtasks"></span>
                        </template>
                    </div>

                </div>

                <div class="modal__ft">
                    <button type="button" class="btn" @click="closeNewTaskModal()">Annulla</button>
                    <button type="button" class="btn btn--accent" @click="submitNewTask()">
                        Crea task
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ── New column modal ────────────────────────────── --}}
    <template x-if="showNewCol">
        <div class="modal" @click.self="showNewCol=false">
            <div class="modal__box" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Nuova colonna</div>
                </div>
                <div class="modal__bd">
                    <div class="field">
                        <label class="field__label">Tipo colonna <span class="req">*</span></label>

                        <template x-if="loadingColTypes">
                            <p class="muted" style="font-size:12px">Caricamento...</p>
                        </template>

                        <template x-if="!loadingColTypes">
                            <select class="select" x-model="newColTypeId" x-ref="newColInput">
                                <option value="">— Seleziona tipo —</option>
                                <template x-for="type in columnTypes" :key="type.id">
                                    <option :value="type.id" x-text="type.name"></option>
                                </template>
                            </select>
                        </template>

                        <template x-if="newColErrors.column_type_id">
                            <span class="field__error" x-text="newColErrors.column_type_id[0]"></span>
                        </template>

                        <template x-if="!loadingColTypes && columnTypes.length === 0">
                            <p class="muted" style="font-size:12px;margin-top:6px">
                                Tutti i tipi disponibili sono già stati aggiunti al progetto.
                            </p>
                        </template>
                    </div>
                </div>
                <div class="modal__ft">
                    <button class="btn" @click="showNewCol=false; newColErrors={}">Annulla</button>
                    <button class="btn btn--accent" @click="submitNewCol()"
                            :disabled="!newColTypeId">Crea colonna</button>
                </div>
            </div>
        </div>
    </template>

    {{-- ── Invite member modal ─────────────────────────── --}}
    <template x-if="showInvite">
        <div class="modal" @click.self="showInvite=false">
            <div class="modal__box" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Invita membro</div>
                    <div class="modal__sub">Aggiungi un utente esistente al progetto.</div>
                </div>

                <div class="modal__bd">
                    <template x-if="loadingAvailableUsers">
                        <p class="muted" style="font-size:12px">Caricamento utenti...</p>
                    </template>

                    <template x-if="!loadingAvailableUsers && availableUsers.length === 0">
                        <p class="muted" style="font-size:13px">
                            Tutti gli utenti registrati sono già membri di questo progetto.
                        </p>
                    </template>

                    <template x-if="!loadingAvailableUsers && availableUsers.length > 0">
                        <div>
                            <div class="field">
                                <label class="field__label">Utente <span class="req">*</span></label>
                                <select class="select" x-model="inviteUserId" required>
                                    <option value="">— Seleziona utente —</option>
                                    <template x-for="u in availableUsers" :key="u.id">
                                        <option :value="u.id" x-text="`${u.name} (${u.email})`"></option>
                                    </template>
                                </select>
                                <template x-if="inviteErrors.user_id">
                                    <span class="field__error" x-text="inviteErrors.user_id"></span>
                                </template>
                            </div>

                            <div class="field">
                                <label class="field__label">Ruolo</label>
                                <select class="select" x-model="inviteRole">
                                    <option value="pm">Project Manager</option>
                                    <option value="developer">Developer</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>

                            <template x-if="inviteErrors.general">
                                <div class="warn-banner" x-text="inviteErrors.general"></div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="modal__ft">
                    <button type="button" class="btn" @click="showInvite=false">Annulla</button>
                    <button type="button" class="btn btn--accent"
                            :disabled="!inviteUserId || loadingAvailableUsers || inviting"
                            @click="submitInvite()">
                        <span x-text="inviting ? 'Invio...' : 'Invita'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ── Archive confirm modal ──────────────────────────── --}}
    <template x-if="showArchiveConfirm">
        <div class="modal" @click.self="showArchiveConfirm=false">
            <div class="modal__box modal__box--sm" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Archivia progetto</div>
                    <div class="modal__sub">Il progetto sarà archiviato e non apparirà più tra quelli attivi.</div>
                </div>
                <form action="{{ route('projects.archive', $project->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal__ft">
                        <button type="button" class="btn" @click="showArchiveConfirm=false">Annulla</button>
                        <button type="submit" class="btn btn--accent">Archivia</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── Delete confirm modal ────────────────────────────── --}}
    <template x-if="showDeleteConfirm">
        <div class="modal" @click.self="showDeleteConfirm=false">
            <div class="modal__box modal__box--sm" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Elimina progetto</div>
                    <div class="modal__sub" style="color:var(--danger)">
                        Questa azione è irreversibile. Tutte le colonne, task e dati del progetto verranno eliminati definitivamente.
                    </div>
                </div>
                <form action="{{ route('projects.destroy', $project->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal__ft">
                        <button type="button" class="btn" @click="showDeleteConfirm=false">Annulla</button>
                        <button type="submit" class="btn btn--danger">Elimina definitivamente</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── New label modal ─────────────────────────────── --}}
    <template x-if="showNewLabel">
        <div class="modal" @click.self="showNewLabel=false">
            <div class="modal__box" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Nuova etichetta</div>
                </div>
                <div class="modal__bd">
                    <div class="field">
                        <label class="field__label">Nome <span class="req">*</span></label>
                        <input class="input" type="text" x-model="newLabelName" placeholder="es. Bug">
                    </div>
                    <div class="field">
                        <label class="field__label">Colore</label>
                        <input type="color" x-model="newLabelColor"
                               style="width:48px;height:36px;cursor:pointer;border:1px solid var(--border);border-radius:6px">
                    </div>
                </div>
                <div class="modal__ft">
                    <button class="btn" @click="showNewLabel=false">Annulla</button>
                    <button class="btn btn--accent" @click="submitNewLabel()">Crea</button>
                </div>
            </div>
        </div>
    </template>

    {{-- ── Delete column confirm modal ─────────────────────── --}}
    <template x-if="showDeleteColConfirm">
        <div class="modal" @click.self="showDeleteColConfirm=false">
            <div class="modal__box modal__box--sm" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Elimina colonna</div>
                    <div class="modal__sub">
                        <template x-if="deleteColTarget && getColumnTasks(deleteColTarget.id).length > 0">
                        <span>
                            La colonna <b x-text="deleteColTarget.name"></b> contiene
                            <b x-text="getColumnTasks(deleteColTarget.id).length"></b> task che verranno eliminate.
                            <br>
                            <span style="color:var(--danger)">Questa azione è irreversibile.</span>
                        </span>
                        </template>
                        <template x-if="deleteColTarget && getColumnTasks(deleteColTarget.id).length === 0">
                        <span>
                            Eliminare la colonna <b x-text="deleteColTarget.name"></b>?
                        </span>
                        </template>
                    </div>
                </div>

                <div class="modal__bd">
                    {{-- Banner errore (stile coerente con quello del modal di creazione) --}}
                    <template x-if="deleteColError">
                        <div class="warn-banner" x-text="deleteColError"></div>
                    </template>
                </div>

                <div class="modal__ft">
                    <button type="button" class="btn"
                            @click="showDeleteColConfirm=false; deleteColError=''">Annulla</button>
                    <button type="button" class="btn btn--danger"
                            @click="confirmDeleteColumn()"
                            :disabled="deletingCol">
                        <x-icon name="trash" size="sm" />
                        <span x-text="deletingCol ? 'Eliminazione...' : 'Elimina colonna'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ── Delete task confirm modal ─────────────────────── --}}
    <template x-if="showDeleteTaskConfirm">
        <div class="modal" @click.self="showDeleteTaskConfirm=false">
            <div class="modal__box modal__box--sm" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Elimina task</div>
                    <div class="modal__sub">
                        Eliminare la task <b x-text="deleteTaskTarget?.title"></b>?
                        <br>
                        <span style="color:var(--danger)">Questa azione è irreversibile.</span>
                    </div>
                </div>

                <div class="modal__bd">
                    <template x-if="deleteTaskError">
                        <div class="warn-banner" x-text="deleteTaskError"></div>
                    </template>
                </div>

                <div class="modal__ft">
                    <button type="button" class="btn"
                            @click="showDeleteTaskConfirm=false; deleteTaskError=''"
                            :disabled="deletingTask">
                        Annulla
                    </button>
                    <button type="button" class="btn btn--danger"
                            @click="confirmDeleteTask()"
                            :disabled="deletingTask">
                        <x-icon name="trash" size="sm" />
                        <span x-text="deletingTask ? 'Eliminazione...' : 'Elimina task'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ── Delete member confirm modal ─────────────────────── --}}
    <template x-if="showDeleteMemberConfirm">
        <div class="modal" @click.self="showDeleteMemberConfirm=false">
            <div class="modal__box modal__box--sm" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Rimuovi membro</div>
                    <div class="modal__sub">
                        Rimuovere <b x-text="deleteMemberTarget?.name"></b> dal progetto?
                    </div>
                </div>

                <div class="modal__bd">
                    <template x-if="deleteMemberError">
                        <div class="warn-banner" x-text="deleteMemberError"></div>
                    </template>
                </div>

                <div class="modal__ft">
                    <button type="button" class="btn"
                            @click="showDeleteMemberConfirm=false; deleteMemberError=''"
                            :disabled="deletingMember">
                        Annulla
                    </button>
                    <button type="button" class="btn btn--danger"
                            @click="confirmDeleteMember()"
                            :disabled="deletingMember">
                        <x-icon name="trash" size="sm" />
                        <span x-text="deletingMember ? 'Rimozione...' : 'Rimuovi'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
    {{-- ── Delete label confirm modal ─────────────────────── --}}
    <template x-if="showDeleteLabelConfirm">
        <div class="modal" @click.self="showDeleteLabelConfirm=false">
            <div class="modal__box modal__box--sm" @click.stop>
                <div class="modal__hd">
                    <div class="modal__title">Elimina etichetta</div>
                    <div class="modal__sub">
                        Eliminare l'etichetta <b x-text="deleteLabelTarget?.name"></b>?
                        <br>
                        <span style="color:var(--danger)">
                        Verrà rimossa da tutti i task che la utilizzano.
                    </span>
                    </div>
                </div>

                <div class="modal__bd">
                    <template x-if="deleteLabelError">
                        <div class="warn-banner" x-text="deleteLabelError"></div>
                    </template>
                </div>

                <div class="modal__ft">
                    <button type="button" class="btn"
                            @click="showDeleteLabelConfirm=false; deleteLabelError=''"
                            :disabled="deletingLabel">
                        Annulla
                    </button>
                    <button type="button" class="btn btn--danger"
                            @click="confirmDeleteLabel()"
                            :disabled="deletingLabel">
                        <x-icon name="trash" size="sm" />
                        <span x-text="deletingLabel ? 'Eliminazione...' : 'Elimina etichetta'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('projectBoard', (project, columns, columnTypes, tasks, members, labels, activity, isPm, canEdit) => ({

        // ── state ──────────────────────────────────────────
        project,
        columns,
        columnTypes,
        tasks,
        members,
        labels,
        activity,
        isPm,
        canEdit,

        activeTab:      'board',
        openTaskId:     null,

        // ── new task state ──────────────────────────────────
        newTaskTitle:       '',
        newTaskPriority:    'medium',
        newTaskColId:       null,
        newTaskDueDate:     '',
        newTaskDescription: '',
        newTaskAssignees:   [],   // array di user id
        newTaskLabels:      [],   // array di label id
        newTaskSubtasks:    [],   // array di { title, done }
        newSubtaskDraft:    '',
        newTaskErrors:      {},

        taskDraft:       null,   // bozza editabile, indipendente da this.tasks
        taskDraftErrors: {},
        savingTask:      false,

        showNewTask:        false,
        showNewCol:         false,
        showInvite:         false,
        showNewLabel:       false,
        showEdit: {{ $errors->any() ? 'true' : 'false' }},
        showArchiveConfirm: false,
        showDeleteConfirm:  false,

        colDragId:      null,
        colDragOverId:  null,

        newColTypeId:   '',
        newDraftSubtaskTitle: '',
        newComment:     '',
        newLabelName:   '',
        newLabelColor:  '#6366f1',

        newColErrors: {},
        loadingColTypes: false,
        showDeleteColConfirm: false,
        deleteColTarget:      null,
        deleteColError:       '',
        deletingCol:          false,

        showDeleteTaskConfirm: false,
        deleteTaskTarget:      null,
        deleteTaskError:       '',
        deletingTask:          false,

        availableUsers:        [],
        loadingAvailableUsers: false,
        inviteUserId:          '',
        inviteRole:            'developer',

        showDeleteMemberConfirm: false,
        deleteMemberTarget:      null,
        deleteMemberError:       '',
        deletingMember:          false,
        savingMemberRole:        false,

        showDeleteLabelConfirm: false,
        deleteLabelTarget:      null,
        deleteLabelError:       '',
        deletingLabel:          false,

        inviteErrors: {},
        inviting:     false,

        // ── computed ───────────────────────────────────────
        get openTask() {
            if (this.openTaskId === null) return null;
            return this.tasks.find(t => t.id === this.openTaskId) || null;
        },

        getColumnTasks(colId) {
            return this.tasks.filter(t => t && t.column_id == colId);
        },

        getMemberTasks(userId) {
            return this.tasks.filter(t => t && (t.assignees || []).some(a => a.id == userId));
        },

        overdueTasks() {
            const now = new Date();
            const doneId = this.doneColumnId();
            return this.tasks.filter(t =>
                t && t.due_date &&
                new Date(t.due_date) < now &&
                t.column_id !== doneId   // ← se doneId è null, nessuna è esclusa
            );
        },

        columnName(colId) {
            const col = this.columns.find(c => c.id == colId);
            return col ? col.name : '';
        },

        doneColumnId() {
            const done = this.columns.find(c => c.is_done);
            return done ? done.id : null;
        },

        doneTasks() {
            const id = this.doneColumnId();
            if (id === null) return 0;
            return this.getColumnTasks(id).length;
        },

        completionPct() {
            if (!this.tasks.length || this.doneColumnId() === null) return 0;
            return Math.round(this.doneTasks() / this.tasks.length * 100);
        },

        isOverdue(task) {
            if (!task || !task.due_date) return false;
            const doneId = this.doneColumnId();
            return new Date(task.due_date) < new Date() && task.column_id !== doneId;
        },

        membersNotAssigned() {
            const ids = (this.openTask?.assignees || []).map(a => a.id);
            return this.members.filter(m => !ids.includes(m.id));
        },

        labelsNotOnTask() {
            const ids = (this.openTask?.labels || []).map(l => l.id);
            return this.labels.filter(l => !ids.includes(l.id));
        },

        taskActivity() {
            return this.activity.filter(e => e.task_id == this.openTaskId);
        },

        // ── helpers ────────────────────────────────────────
        toDateInput(d) {
            if (!d) return '';
            // Funziona sia per "2026-06-30", "2026-06-30T...", "2026-06-30 00:00:00"
            return String(d).slice(0, 10);
        },

        memberName(userId) {
            const m = this.members.find(m => m.id == userId);
            return m ? m.name : '?';
        },

        labelById(labelId) {
            return this.labels.find(l => l.id == labelId);
        },

        availableMembersForNewTask() {
            return this.members.filter(m => !this.newTaskAssignees.includes(m.id));
        },

        availableLabelsForNewTask() {
            return this.labels.filter(l => !this.newTaskLabels.includes(l.id));
        },

        addNewTaskAssignee(userId) {
            if (!userId) return;
            const id = parseInt(userId);
            if (!this.newTaskAssignees.includes(id)) {
                this.newTaskAssignees.push(id);
            }
            // Pulisce l'errore se l'utente ha appena risolto la condizione
            if (this.newTaskAssignees.length > 0) {
                delete this.newTaskErrors.assignees;
            }
        },

        removeNewTaskAssignee(userId) {
            this.newTaskAssignees = this.newTaskAssignees.filter(id => id !== userId);
        },

        addNewTaskLabel(labelId) {
            if (!labelId) return;
            const id = parseInt(labelId);
            if (!this.newTaskLabels.includes(id)) {
                this.newTaskLabels.push(id);
            }
        },

        removeNewTaskLabel(labelId) {
            this.newTaskLabels = this.newTaskLabels.filter(id => id !== labelId);
        },

        // Sottoattività della bozza
        draftAddSubtask() {
            const title = this.newDraftSubtaskTitle.trim();
            if (!title || !this.taskDraft) return;
            this.taskDraft.subtasks.push({
                id:    null,    // null = nuova, sarà creata dal backend
                title,
                done:  false,
            });
            this.newDraftSubtaskTitle = '';
        },

        draftRemoveSubtask(idx) {
            if (!this.taskDraft) return;
            this.taskDraft.subtasks.splice(idx, 1);
        },

        draftSubtaskStats() {
            if (!this.taskDraft || !this.taskDraft.subtasks) return '0/0';
            const total = this.taskDraft.subtasks.length;
            const done  = this.taskDraft.subtasks.filter(s => s.done).length;
            return `${done}/${total}`;
        },

        addNewTaskSubtask() {
            const title = this.newSubtaskDraft.trim();
            if (!title) return;
            this.newTaskSubtasks.push({ title, done: false });
            this.newSubtaskDraft = '';
        },

        removeNewTaskSubtask(idx) {
            this.newTaskSubtasks.splice(idx, 1);
        },

        priorityLabel(p) {
            return { high: 'Alta', medium: 'Media', low: 'Bassa' }[p] || p || '';
        },

        initials(name) {
            if (!name) return '?';
            return name.split(' ').map(p => p[0].toUpperCase()).join('').slice(0, 2);
        },

        userColor(name) {
            const palette = [
                '#6366f1','#8b5cf6','#ec4899','#f43f5e',
                '#f97316','#22c55e','#14b8a6','#3b82f6',
                '#06b6d4','#a855f7','#d946ef','#84cc16',
            ];
            let h = 0;
            for (const c of (name || '')) h = c.charCodeAt(0) + ((h << 5) - h);
            return palette[Math.abs(h) % palette.length];
        },

        fmtDate(d) {
            if (!d) return '';
            return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short' });
        },

        fmtRelative(d) {
            if (!d) return '';
            const diff = (Date.now() - new Date(d)) / 1000;
            if (diff < 60)   return 'adesso';
            if (diff < 3600) return `${Math.floor(diff/60)} min fa`;
            if (diff < 86400)return `${Math.floor(diff/3600)} h fa`;
            return `${Math.floor(diff/86400)} g fa`;
        },

        activityVerb(type) {
            return {
                'task.created':  'ha creato',
                'task.moved':    'ha spostato',
                'task.assigned': 'ha assegnato',
                'task.deleted':  'ha eliminato',
                'task.updated':  'ha modificato',
            }[type] || type;
        },

        activityText(entry) {
            const verb  = this.activityVerb(entry.type);
            const title = entry.meta?.task_title || '';
            let text = `${verb} ${title}`;

            if (entry.meta?.old_title) {
                text += ` (rinominata da ${entry.meta.old_title})`;
            }

            if (entry.meta?.assigned_to) {
                // Se il verbo è già "ha assegnato" (task.assigned) non ripetere "assegnata":
                // basta " a Marta". Per gli altri tipi usa " e assegnata a ".
                const connector = entry.type === 'task.assigned'
                    ? ' a '
                    : ' e l\'ha assegnato a ';
                text += `${connector}${entry.meta.assigned_to}`;
            }

            if (entry.meta?.from_column && entry.meta?.to_column) {
                text += ` da ${entry.meta.from_column} a ${entry.meta.to_column}`;
            }

            return text;
        },

        // Restituisce gli oggetti completi degli assegnatari della bozza
        draftAssignees() {
            if (!this.taskDraft) return [];
            return this.taskDraft.assignee_ids
                .map(id => this.members.find(m => m.id == id))
                .filter(Boolean);
        },

        // Restituisce gli oggetti completi delle etichette della bozza
        draftLabels() {
            if (!this.taskDraft) return [];
            return this.taskDraft.label_ids
                .map(id => this.labels.find(l => l.id == id))
                .filter(Boolean);
        },

        draftAvailableMembers() {
            if (!this.taskDraft) return [];
            return this.members.filter(m => !this.taskDraft.assignee_ids.includes(m.id));
        },

        draftAvailableLabels() {
            if (!this.taskDraft) return [];
            return this.labels.filter(l => !this.taskDraft.label_ids.includes(l.id));
        },

        draftAddAssignee(userId) {
            if (!userId || !this.taskDraft) return;
            const id = parseInt(userId);
            if (!this.taskDraft.assignee_ids.includes(id)) {
                this.taskDraft.assignee_ids.push(id);
            }
        },

        draftRemoveAssignee(userId) {
            if (!this.taskDraft) return;
            this.taskDraft.assignee_ids = this.taskDraft.assignee_ids.filter(id => id !== userId);
        },

        draftAddLabel(labelId) {
            if (!labelId || !this.taskDraft) return;
            const id = parseInt(labelId);
            if (!this.taskDraft.label_ids.includes(id)) {
                this.taskDraft.label_ids.push(id);
            }
        },

        draftRemoveLabel(labelId) {
            if (!this.taskDraft) return;
            this.taskDraft.label_ids = this.taskDraft.label_ids.filter(id => id !== labelId);
        },

        // Nome+cognome (full name) — distinto da memberName che era solo "name"
        memberFullName(userId) {
            const m = this.members.find(m => m.id == userId);
            if (!m) return '?';
            // Se hai already first_name/last_name separati usali, altrimenti m.name è sufficiente
            return m.full_name || `${m.first_name || ''} ${m.last_name || ''}`.trim() || m.name || '?';
        },

        // ── drawer ─────────────────────────────────────────
        openDrawer(taskId) {
            const t = this.tasks.find(t => t.id === taskId);
            if (!t) return;

            this.openTaskId = taskId;

            this.taskDraft = {
                title:        t.title,
                description:  t.description || '',
                priority:     t.priority,
                due_date:     this.toDateInput(t.due_date),   // ← qui
                assignee_ids: (t.assignees || []).map(a => a.id),
                label_ids:    (t.labels    || []).map(l => l.id),
                subtasks:     (t.subtasks || []).map(s => ({
                    id:    s.id,
                    title: s.title,
                    done:  !!s.done,
                })),
            };
            this.taskDraftErrors = {};
            this.newDraftSubtaskTitle = '';
        },

        closeDrawer() {
            this.openTaskId      = null;
            this.taskDraft       = null;
            this.taskDraftErrors = {};
        },

        // ── task CRUD ──────────────────────────────────────
        openNewTask(colId) {
            if (!this.canEdit) return;
            this.newTaskColId       = colId;
            this.newTaskTitle       = '';
            this.newTaskPriority    = 'medium';
            this.newTaskDueDate     = '';
            this.newTaskDescription = '';
            this.newTaskAssignees   = [];
            this.newTaskLabels      = [];
            this.newTaskSubtasks    = [];
            this.newSubtaskDraft    = '';
            this.newTaskErrors      = {};
            this.showNewTask        = true;
            this.$nextTick(() => this.$refs.newTaskInput?.focus());
        },

        closeNewTaskModal() {
            this.showNewTask   = false;
            this.newTaskErrors = {};
        },

        async submitNewTask() {
            if (!this.canEdit) return;

            const errors = {};
            if (!this.newTaskTitle.trim()) {
                errors.title = 'Il titolo è obbligatorio.';
            }
            if (this.newTaskAssignees.length === 0) {
                errors.assignees = 'Seleziona almeno un assegnatario.';
            }
            this.newTaskErrors = errors;
            if (Object.keys(errors).length > 0) return;

            // Costruisci il payload
            const payload = {
                title:        this.newTaskTitle.trim(),
                description:  this.newTaskDescription.trim() || null,
                priority:     this.newTaskPriority,
                column_id:    this.newTaskColId,
                due_date:     this.newTaskDueDate || null,
                assignee_ids: this.newTaskAssignees,
                label_ids:    this.newTaskLabels,
                subtasks:     this.newTaskSubtasks
                    .filter(s => s.title.trim())
                    .map(s => ({ title: s.title.trim(), done: !!s.done })),
            };

            // Chiamata al backend
            const response = await fetch(`/projects/{{ $project->id }}/tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));

                if (data.errors) {
                    // Mappa i nomi backend → frontend in modo esplicito
                    const fieldMap = {
                        'title':        'title',
                        'description':  'description',
                        'priority':     'priority',
                        'column_id':    'column_id',
                        'due_date':     'due_date',
                        'assignee_ids': 'assignees',
                        'label_ids':    'labels',
                    };

                    const mapped = {};

                    for (const [backendKey, msgs] of Object.entries(data.errors)) {
                        const msg = Array.isArray(msgs) ? msgs[0] : msgs;

                        // Sottoattività: chiavi come 'subtasks.0.title' → raggruppiamo in 'subtasks'
                        if (backendKey.startsWith('subtasks')) {
                            mapped.subtasks = mapped.subtasks || msg;
                            continue;
                        }

                        // Campo conosciuto → usa il mapping
                        const frontendKey = fieldMap[backendKey] || backendKey;
                        mapped[frontendKey] = msg;
                    }

                    this.newTaskErrors = mapped;
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'error', message: 'Errore nella creazione della task.' }
                    }));
                }
                return;
            }

            // Successo: il backend ritorna la task creata con assignees/labels/subtasks già popolati
            const result = await response.json();
            this.tasks.push(result.task);

            const newActs = result.activities || (result.activity ? [result.activity] : []);
            newActs.forEach(a => this.activity.unshift(a));

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Task creata con successo.' }
            }));

            this.closeNewTaskModal();
        },
        async saveTask() {
            if (!this.canEdit || !this.taskDraft || this.savingTask) return;

            // ── 1. Validazione client-side ──
            const errors = {};
            if (!this.taskDraft.title.trim()) {
                errors.title = 'Il titolo è obbligatorio.';
            }
            if (this.taskDraft.assignee_ids.length === 0) {
                errors.assignees = 'Seleziona almeno un assegnatario.';
            }
            this.taskDraftErrors = errors;
            if (Object.keys(errors).length > 0) return;

            // ── 2. Costruzione payload ──
            this.savingTask = true;

            const payload = {
                title:        this.taskDraft.title.trim(),
                description:  this.taskDraft.description.trim() || null,
                priority:     this.taskDraft.priority,
                due_date:     this.taskDraft.due_date || null,
                assignee_ids: this.taskDraft.assignee_ids,
                label_ids:    this.taskDraft.label_ids,
                subtasks: this.taskDraft.subtasks
                    .filter(s => s.title.trim())
                    .map(s => ({
                        id:    s.id,
                        title: s.title.trim(),
                        done:  !!s.done,
                    })),
            };

            // ── 3. Chiamata al backend ──
            const response = await fetch(`/projects/{{ $project->id }}/tasks/${this.openTaskId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });

            this.savingTask = false;

            // ── 4. Gestione errori dal backend ──
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));

                if (errorData.errors) {
                    const fieldMap = {
                        'title':        'title',
                        'description':  'description',
                        'priority':     'priority',
                        'due_date':     'due_date',
                        'assignee_ids': 'assignees',
                        'label_ids':    'labels',
                    };

                    const mapped = {};
                    for (const [backendKey, msgs] of Object.entries(errorData.errors)) {
                        const msg = Array.isArray(msgs) ? msgs[0] : msgs;

                        if (backendKey.startsWith('subtasks')) {
                            mapped.subtasks = mapped.subtasks || msg;
                            continue;
                        }

                        const frontendKey = fieldMap[backendKey] || backendKey;
                        mapped[frontendKey] = msg;
                    }
                    this.taskDraftErrors = mapped;
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'error', message: 'Errore nel salvataggio.' }
                    }));
                }
                return;
            }

            // ── 5. Successo: aggiorna lo stato ──
            const data = await response.json();
            const task = data.task || data;

            if (!task || !task.id) {
                console.error('Backend ha restituito una task invalida:', data);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: 'Risposta del server non valida.' }
                }));
                return;
            }

            const idx = this.tasks.findIndex(t => t.id === this.openTaskId);
            if (idx !== -1) this.tasks[idx] = task;

            const newActs = data.activities || (data.activity ? [data.activity] : []);
            newActs.forEach(a => this.activity.unshift(a));

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Task aggiornata.' }
            }));

            this.closeDrawer();
        },

        async moveTask(taskId, newColId) {
            if (!this.canEdit) return;

            const t = this.tasks.find(t => t.id === taskId);
            if (!t || t.column_id === newColId) return;

            // ── Optimistic update: aggiorna subito la UI ──
            const previousColId = t.column_id;
            t.column_id = newColId;

            // ── Chiamata al backend ──
            const response = await fetch(`/projects/{{ $project->id }}/tasks/${taskId}/move`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ column_id: newColId }),
            });

            if (!response.ok) {
                // Rollback: ripristina la colonna precedente
                t.column_id = previousColId;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: 'Impossibile spostare la task.' }
                }));
                return;
            }

            const data = await response.json();

            const idx = this.tasks.findIndex(t => t.id === taskId);
            if (idx !== -1) this.tasks[idx] = data.task;

            const newActs = data.activities || (data.activity ? [data.activity] : []);
            newActs.forEach(a => this.activity.unshift(a));
        },

        askDeleteTask(task) {
            if (!this.canEdit) return;
            this.deleteTaskTarget       = task;
            this.deleteTaskError        = '';
            this.showDeleteTaskConfirm  = true;
        },

        // Esegue l'eliminazione effettiva
        async confirmDeleteTask() {
            if (!this.deleteTaskTarget) return;

            const taskId = this.deleteTaskTarget.id;
            this.deletingTask     = true;
            this.deleteTaskError  = '';

            const response = await fetch(`/projects/{{ $project->id }}/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) {
                this.deleteTaskError = 'Impossibile eliminare la task. Riprova.';
                this.deletingTask = false;
                return;
            }

            // Aggiorna stato locale: rimuovi la task
            this.tasks = this.tasks.filter(t => t.id !== taskId);

            // Se il backend restituisce activity, aggiungila al feed
            const data = await response.json().catch(() => ({}));
            const newActs = data.activities || (data.activity ? [data.activity] : []);
            newActs.forEach(a => this.activity.unshift(a));

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Task eliminata con successo.' }
            }));

            // Chiudi modal e drawer
            this.showDeleteTaskConfirm = false;
            this.deleteTaskTarget      = null;
            this.deletingTask          = false;
            this.closeDrawer();
        },

        // ── columns ────────────────────────────────────────
        async openNewColModal() {
            if (!this.isPm) return;
            this.loadingColTypes = true;
            this.showNewCol = true;

            const response = await fetch(`/projects/{{ $project->id }}/columns/available-types`, {
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json();
            this.columnTypes = data;
            this.loadingColTypes = false;
        },

        orderedColumns() {
            return [...this.columns].sort((a, b) => (a.position ?? 0) - (b.position ?? 0));
        },

        async submitNewCol() {
            if (!this.isPm) return;
            if (!this.newColTypeId) return;

            const response = await fetch(`/projects/{{ $project->id }}/columns`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ column_type_id: this.newColTypeId }),
            });

            if (!response.ok) {
                const data = await response.json();
                if (data.errors) this.newColErrors = data.errors;
                return;
            }

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Colonna creata con successo.' }
            }));

            const column = await response.json();
            this.columns.push(column);
            this.newColTypeId = '';
            this.showNewCol   = false;
            this.$nextTick(() => this.initTaskSortable());
        },

        // Apre il modal di conferma
        askDeleteColumn(col) {
            if (!this.isPm) return;
            this.deleteColTarget   = col;
            this.deleteColError    = '';
            this.showDeleteColConfirm = true;
        },

        // Esegue l'eliminazione effettiva
        async confirmDeleteColumn() {
            if (!this.deleteColTarget) return;

            const colId   = this.deleteColTarget.id;
            this.deletingCol  = true;
            this.deleteColError = '';
            console.log('colId:', colId, typeof colId);
            const response = await fetch(`/projects/{{ $project->id }}/columns/${colId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                console.log(data.message);
                this.deleteColError = 'Impossibile eliminare la colonna. Riprova.';
                this.deletingCol = false;
                return;
            }

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Colonna eliminata con successo.' }
            }));

            // Aggiorna stato locale
            this.tasks   = this.tasks.filter(t => t.column_id !== colId);
            this.columns = this.columns.filter(c => c.id !== colId);

            // Chiudi il modal
            this.showDeleteColConfirm = false;
            this.deleteColTarget      = null;
            this.deletingCol          = false;

        },

        moveColumn(colId, newPosition) {
            if (!this.isPm) return;
            const ordered = this.orderedColumns();
            const fromIdx = ordered.findIndex(c => c.id === colId);
            if (fromIdx === newPosition) return;

            const cols = [...ordered];
            const [moved] = cols.splice(fromIdx, 1);
            cols.splice(newPosition, 0, moved);
            cols.forEach((c, i) => {
                const col = this.columns.find(col => col.id === c.id);
                if (col) col.position = i;
            });

            // Invia al backend l'ordine aggiornato
            fetch(`/projects/{{ $project->id }}/columns/reorder`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    columns: this.orderedColumns().map(c => c.id),
                }),
            });
        },

        // ── members ────────────────────────────────────────

        async changeMemberRole(userId, role) {
            if (!this.isPm) return;

            const m = this.members.find(m => m.id == userId);
            const previousRole = m ? m.pivot_role : null;

            const response = await fetch(`/projects/{{ $project->id }}/members/${userId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ role }),
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));

                // Rollback: ripristina il ruolo precedente nello stato Alpine
                if (m && previousRole) m.pivot_role = previousRole;

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'danger', message: data.message || 'Impossibile aggiornare il ruolo.' }
                }));
                return;
            }

            if (m) m.pivot_role = role;

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Ruolo aggiornato.' }
            }));
        },

        askDeleteMember(member) {
            if (!this.isPm) return;
            this.deleteMemberTarget      = member;
            this.deleteMemberError       = '';
            this.showDeleteMemberConfirm = true;
        },

        async confirmDeleteMember() {
            if (!this.deleteMemberTarget) return;

            const userId = this.deleteMemberTarget.id;
            this.deletingMember   = true;
            this.deleteMemberError = '';

            const response = await fetch(`/projects/{{ $project->id }}/members/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                this.deleteMemberError = data.message || 'Impossibile rimuovere il membro. Riprova.';
                this.deletingMember = false;
                return;
            }

            this.members = this.members.filter(m => m.id !== userId);

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Membro rimosso dal progetto.' }
            }));

            this.showDeleteMemberConfirm = false;
            this.deleteMemberTarget      = null;
            this.deletingMember          = false;
        },

        async openInviteModal() {
            if (!this.isPm) return;
            this.showInvite            = true;
            this.loadingAvailableUsers = true;
            this.inviteUserId          = '';
            this.inviteRole            = 'developer';
            this.inviteErrors          = {};

            const response = await fetch(`/projects/{{ $project->id }}/members/available-types`, {
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json();
            this.availableUsers        = data;
            this.loadingAvailableUsers = false;
        },

        async submitInvite() {
            if (!this.isPm || !this.inviteUserId) return;

            this.inviting      = true;
            this.inviteErrors  = {};

            const response = await fetch(`/projects/{{ $project->id }}/members`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    user_id: this.inviteUserId,
                    role:    this.inviteRole,
                }),
            });

            this.inviting = false;

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                if (data.errors) {
                    this.inviteErrors = {
                        user_id: data.errors.user_id?.[0] || null,
                        general: null,
                    };
                } else {
                    this.inviteErrors = { general: data.message || 'Impossibile aggiungere il membro.' };
                }
                return;
            }

            const data = await response.json();
            this.members.push(data.member);

            if (data.email_sent === false) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'warn', message: 'Membro aggiunto, ma l\'email d\'invito non è stata inviata.' }
                }));
            } else {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'success', message: 'Membro aggiunto al progetto.' }
                }));
            }

            this.showInvite = false;
            this.inviteUserId = '';

            this.showInvite = false;
            this.inviteUserId = '';
        },

        // ── labels management ──────────────────────────────
        async submitNewLabel() {
            if (!this.canEdit) return;
            if (!this.newLabelName.trim()) return;

            const response = await fetch(`/projects/{{ $project->id }}/labels`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    name:  this.newLabelName.trim(),
                    color: this.newLabelColor,
                }),
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: data.message || 'Errore nella creazione dell\'etichetta.' }
                }));
                return;
            }

            const label = await response.json();
            this.labels.push(label);

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Etichetta creata.' }
            }));

            this.newLabelName  = '';
            this.newLabelColor = '#6366f1';
            this.showNewLabel  = false;
        },

        async updateLabelColor(labelId, color) {
            if (!this.canEdit) return;

            const l = this.labels.find(l => l.id == labelId);
            const previousColor = l ? l.color : null;

            // Optimistic update
            if (l) l.color = color;

            const response = await fetch(`/projects/{{ $project->id }}/labels/${labelId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ color }),
            });

            if (!response.ok) {
                // Rollback
                if (l && previousColor) l.color = previousColor;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: 'Impossibile aggiornare il colore.' }
                }));
            }
        },

        askDeleteLabel(labelId) {
            if (!this.canEdit) return;
            this.deleteLabelTarget      = this.labels.find(l => l.id == labelId) || null;
            this.deleteLabelError       = '';
            this.showDeleteLabelConfirm = true;
        },

        async confirmDeleteLabel() {
            if (!this.deleteLabelTarget) return;

            const labelId = this.deleteLabelTarget.id;
            this.deletingLabel    = true;
            this.deleteLabelError = '';

            const response = await fetch(`/projects/{{ $project->id }}/labels/${labelId}`, {
                method: 'DELETE',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                this.deleteLabelError = data.message || 'Impossibile eliminare l\'etichetta. Riprova.';
                this.deletingLabel = false;
                return;
            }

            this.labels = this.labels.filter(l => l.id !== labelId);
            this.tasks.forEach(t => {
                if (t.labels) t.labels = t.labels.filter(l => l.id !== labelId);
            });

            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'Etichetta eliminata.' }
            }));

            this.showDeleteLabelConfirm = false;
            this.deleteLabelTarget      = null;
            this.deletingLabel          = false;
        },

        // ── activity helper ────────────────────────────────
        addActivity(type, taskId, taskTitle) {
            this.activity.push({
                id:         Date.now(),
                task_id:    taskId,
                type,
                user_name:  'Tu',
                created_at: new Date().toISOString(),
                meta:       { task_title: taskTitle },
            });
        },

        // ── init ───────────────────────────────────────────
        init() {
            this.$nextTick(() => this.initSortable());
        },

        initSortable() {
            this.initTaskSortable();
        },

        initTaskSortable() {
            if (typeof Sortable === 'undefined') return;
            document.querySelectorAll('.column__body:not([data-task-sortable])').forEach(el => {
                el.dataset.taskSortable = '1';
                Sortable.create(el, {
                    group:      'tasks',
                    animation:  150,
                    ghostClass: 'is-dragging',
                    disabled:   !this.canEdit,
                    onEnd:      (evt) => {
                        if (!this.canEdit) return;
                        const taskId   = parseInt(evt.item.dataset.taskId);
                        const newColId = parseInt(evt.to.dataset.columnId);
                        const task = this.tasks.find(t => t.id === taskId);

                        if (task && task.column_id !== newColId) {
                            // ⬇ ora chiama moveTask che gestisce backend + attività
                            this.moveTask(taskId, newColId);
                        }
                    },
                });
            });
        },
    }));
});
</script>
@endpush
@endsection
