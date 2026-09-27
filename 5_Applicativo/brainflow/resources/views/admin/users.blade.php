@extends('layouts.app')

@section('title', 'Gestione Utenti')

@section('content')

    <script>
        window.adminUsersData = {
            users: {!! json_encode($users ?? []) !!},
        };
    </script>

    <div x-data="adminUsers(adminUsersData.users)" class="col" style="flex:1">

        {{-- ── Topbar ───────────────────────────────────────── --}}
        <div class="topbar">
            <div class="topbar__crumbs">
                <span class="muted">Admin</span>
                <span class="topbar__sep">/</span>
                <strong>Gestione Utenti</strong>
            </div>
        </div>

        {{-- ── Content ──────────────────────────────────────── --}}
        <div class="content" style="flex:1;overflow-y:auto">

            @if(isset($warnBanner))
                <div class="warn-banner" style="margin-bottom:16px">
                    <x-icon name="alert" size="sm" />
                    {{ $warnBanner }}
                </div>
            @endif

            {{-- Header sezione con azione, come nella tab Membri --}}
            <div class="row" style="margin-bottom:16px">
                <div class="section-title" style="margin-bottom:0">
                    Tutti gli utenti
                    <span class="mono" x-text="users.length"></span>
                </div>
                <div style="flex:1"></div>
                <button class="btn btn--accent" @click="openCreate()">
                    <x-icon name="plus" /> Nuovo utente
                </button>
            </div>

            {{-- Lista utenti --}}
            <div class="col" style="gap:8px">
                <template x-for="u in users" :key="u.id">
                    <div class="card">
                        <div class="card__bd row">

                            {{-- Avatar --}}
                            <span class="avatar avatar--lg"
                                  :style="`background:${userColor(u.name)}`"
                                  x-text="initials(u.name)"></span>

                            {{-- Info --}}
                            <div class="col" style="gap:2px;flex:1;min-width:0">
                                <div class="row" style="gap:8px">
                                    <strong x-text="u.name" style="font-size:14px"></strong>
                                    <span class="badge"
                                          :class="u.sys_role === 'admin' ? 'badge--prio-high' : 'badge--neutral'"
                                          x-text="u.sys_role === 'admin' ? 'Admin' : 'Utente'"></span>
                                </div>
                                <span class="muted" x-text="u.email" style="font-size:12px"></span>
                            </div>

                            {{-- Ruolo di sistema (cambio rapido) --}}
                            <select class="select" style="width:auto;font-size:12px"
                                    :disabled="savingId === u.id"
                                    x-model="u.sys_role"
                                    @change="changeSysRole(u)">
                                <option value="user">Utente</option>
                                <option value="admin">Admin</option>
                            </select>

                            {{-- Azioni --}}
                            <button class="btn btn--ghost btn--icon btn--sm"
                                    @click="openEdit(u)"
                                    :disabled="savingId === u.id"
                                    title="Modifica utente">
                                <x-icon name="pencil" size="sm" />
                            </button>
                            <button class="btn btn--ghost btn--icon btn--sm"
                                    @click="askDelete(u)"
                                    :disabled="savingId === u.id"
                                    title="Elimina utente">
                                <x-icon name="trash" size="sm" />
                            </button>

                        </div>
                    </div>
                </template>

                {{-- Empty state --}}
                <template x-if="users.length === 0">
                    <div class="empty" style="padding:20px">
                        Nessun utente presente.
                    </div>
                </template>
            </div>

        </div>

        {{-- ── Modale crea / modifica ───────────────────────── --}}
        <template x-if="showForm">
            <div class="modal" @click.self="closeForm()">
                <div class="modal__box" @click.stop>
                    <div class="modal__hd">
                        <div class="modal__title" x-text="formMode === 'create' ? 'Nuovo utente' : 'Modifica utente'"></div>
                        <div class="modal__sub"
                             x-text="formMode === 'create' ? 'Crea un nuovo account utente.' : 'Aggiorna i dati dell\'utente.'"></div>
                    </div>

                    <div class="modal__bd">

                        {{-- Nome --}}
                        <div class="field">
                            <label class="field__label">Nome <span class="req">*</span></label>
                            <input type="text" class="input"
                                   :class="formErrors.name ? 'is-error' : ''"
                                   x-model="form.name"
                                   @input="delete formErrors.name"
                                   placeholder="es. Mario Rossi">
                            <template x-if="formErrors.name">
                                <span class="field__error" x-text="formErrors.name"></span>
                            </template>
                        </div>

                        {{-- Email --}}
                        <div class="field">
                            <label class="field__label">Email <span class="req">*</span></label>
                            <input type="email" class="input"
                                   :class="formErrors.email ? 'is-error' : ''"
                                   x-model="form.email"
                                   @input="delete formErrors.email"
                                   placeholder="es. mario.rossi@example.com">
                            <template x-if="formErrors.email">
                                <span class="field__error" x-text="formErrors.email"></span>
                            </template>
                        </div>

                        {{-- Ruolo di sistema --}}
                        <div class="field">
                            <label class="field__label">Ruolo di sistema</label>
                            <select class="select"
                                    :class="formErrors.sys_role ? 'is-error' : ''"
                                    x-model="form.sys_role"
                                    @change="delete formErrors.sys_role">
                                <option value="user">Utente</option>
                                <option value="admin">Admin</option>
                            </select>
                            <template x-if="formErrors.sys_role">
                                <span class="field__error" x-text="formErrors.sys_role"></span>
                            </template>
                        </div>

                    </div>

                    <div class="modal__ft">
                        <button type="button" class="btn" @click="closeForm()" :disabled="submitting">
                            Annulla
                        </button>
                        <button type="button" class="btn btn--accent" @click="submitForm()" :disabled="submitting">
                        <span x-text="submitting
                            ? 'Salvataggio...'
                            : (formMode === 'create' ? 'Crea utente' : 'Salva modifiche')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- ── Modale conferma eliminazione ─────────────────── --}}
        <template x-if="showDeleteConfirm">
            <div class="modal" @click.self="showDeleteConfirm=false">
                <div class="modal__box modal__box--sm" @click.stop>
                    <div class="modal__hd">
                        <div class="modal__title">Elimina utente</div>
                        <div class="modal__sub">
                            Eliminare definitivamente <b x-text="deleteTarget?.name"></b>?
                            <br>
                            <span style="color:var(--danger)">Questa azione è irreversibile.</span>
                        </div>
                    </div>

                    <div class="modal__bd">
                        {{-- Errore mostrato come messaggio di validazione (scritta rossa) --}}
                        <template x-if="deleteError">
                            <span class="field__error" x-text="deleteError"></span>
                        </template>
                    </div>

                    <div class="modal__ft">
                        <button type="button" class="btn"
                                @click="showDeleteConfirm=false; deleteError=''"
                                :disabled="deleting">
                            Annulla
                        </button>
                        <button type="button" class="btn btn--danger"
                                @click="confirmDelete()"
                                :disabled="deleting">
                            <x-icon name="trash" size="sm" />
                            <span x-text="deleting ? 'Eliminazione...' : 'Elimina'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('adminUsers', (users) => ({

                    // ── state ──────────────────────────────────────────
                    users,
                    savingId:  null,
                    submitting: false,

                    showForm:  false,
                    formMode:  'create',   // 'create' | 'edit'
                    formId:    null,
                    form:      { name: '', email: '', sys_role: 'user' },
                    formErrors: {},

                    showDeleteConfirm: false,
                    deleteTarget:      null,
                    deleteError:       '',
                    deleting:          false,

                    // ── helpers UI ─────────────────────────────────────
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

                    toast(type, message) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } }));
                    },

                    csrf() {
                        return document.querySelector('meta[name="csrf-token"]')?.content;
                    },

                    async request(url, method, body) {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: body ? JSON.stringify(body) : undefined,
                        });

                        let data = {};
                        try { data = await res.json(); } catch (_) {}

                        if (res.status === 422) {
                            const e = {};
                            for (const [k, msgs] of Object.entries(data.errors || {})) {
                                e[k] = Array.isArray(msgs) ? msgs[0] : msgs;
                            }
                            throw { validation: e, message: data.message };
                        }
                        if (!res.ok) {
                            throw { message: data.message || 'Si è verificato un errore.' };
                        }
                        return data;
                    },

                    // ── crea / modifica ────────────────────────────────
                    openCreate() {
                        this.formMode   = 'create';
                        this.formId     = null;
                        this.form       = { name: '', email: '', sys_role: 'user' };
                        this.formErrors = {};
                        this.showForm   = true;
                    },

                    openEdit(u) {
                        this.formMode   = 'edit';
                        this.formId     = u.id;
                        this.form       = { name: u.name, email: u.email, sys_role: u.sys_role };
                        this.formErrors = {};
                        this.showForm   = true;
                    },

                    closeForm() {
                        if (this.submitting) return;
                        this.showForm   = false;
                        this.formErrors = {};
                    },

                    async submitForm() {
                        this.submitting = true;
                        this.formErrors = {};
                        try {
                            if (this.formMode === 'create') {
                                const data = await this.request(
                                    '{{ route('admin.users.store') }}',
                                    'POST',
                                    this.form
                                );
                                if (data.user) this.users.push(data.user);

                                // L'utente è stato creato. Se l'email di registrazione
                                // non è partita, il backend lo segnala con email_sent=false:
                                // l'admin va avvisato che deve comunicare le credenziali in altro modo.
                                if (data.email_sent === false) {
                                    this.toast('warn', 'Utente creato, ma l\'email di registrazione non è stata inviata.');
                                } else {
                                    this.toast('success', 'Utente creato.');
                                }
                            } else {
                                const data = await this.request(
                                    `/admin/users/${this.formId}`,
                                    'PATCH',
                                    this.form
                                );
                                const idx = this.users.findIndex(x => x.id === this.formId);
                                if (idx !== -1) {
                                    this.users[idx] = data.user ?? { ...this.users[idx], ...this.form };
                                }
                                this.toast('success', 'Utente aggiornato.');
                            }
                            this.showForm = false;
                        } catch (err) {
                            // Update / create => errori mostrati come validazione inline
                            if (err.validation) {
                                this.formErrors = err.validation;
                            } else {
                                this.toast('danger', err.message || 'Errore durante il salvataggio.');
                            }
                        } finally {
                            this.submitting = false;
                        }
                    },

                    // ── cambio rapido ruolo (toast) ─────────────────────
                    async changeSysRole(u) {
                        this.savingId = u.id;
                        const idx = this.users.findIndex(x => x.id === u.id);
                        const previous = idx !== -1 ? this.users[idx].sys_role : null;
                        try {
                            await this.request(`/admin/users/${u.id}`, 'PATCH', { sys_role: u.sys_role });
                            this.toast('success', 'Ruolo aggiornato.');
                        } catch (err) {
                            if (idx !== -1 && previous) this.users[idx].sys_role = previous;
                            const msg = err.validation?.sys_role || err.message || 'Impossibile aggiornare il ruolo.';
                            this.toast('danger', msg);
                        } finally {
                            this.savingId = null;
                        }
                    },

                    // ── eliminazione (messaggio di validazione) ─────────
                    askDelete(u) {
                        this.deleteTarget      = u;
                        this.deleteError       = '';
                        this.showDeleteConfirm = true;
                    },

                    async confirmDelete() {
                        if (!this.deleteTarget) return;
                        const userId = this.deleteTarget.id;
                        this.deleting    = true;
                        this.deleteError = '';
                        try {
                            await this.request(`/admin/users/${userId}`, 'DELETE');
                            this.users = this.users.filter(x => x.id !== userId);
                            this.toast('success', 'Utente eliminato.');
                            this.showDeleteConfirm = false;
                            this.deleteTarget      = null;
                        } catch (err) {
                            this.deleteError = err.validation?.delete
                                || err.message
                                || 'Impossibile eliminare l\'utente. Riprova.';
                        } finally {
                            this.deleting = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
@endsection
