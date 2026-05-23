@extends('layouts.guest')

@section('title', 'Reimposta password')

@section('content')
    <div class="auth-shell" x-data="resetForm()">
        <div class="auth-shell__art">
            <div class="auth-shell__brand">
                <div class="auth-shell__brand-mark">BF</div>
                <span>BrainFlow</span>
            </div>
            <p class="auth-shell__quote">
                Quasi fatto,<br>
                <span>scegli una nuova password.</span>
            </p>
        </div>

        <div class="auth-shell__form-wrap">
            <div class="auth-form">
                <h1 class="auth-form__title">Nuova password</h1>
                <p class="auth-form__sub">Imposta la tua nuova password per accedere.</p>

                <form action="{{ route('password.store') }}" method="POST" class="col" style="gap:14px;margin-top:8px"
                      @submit.prevent="validateAndSubmit($el)">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="field">
                        <label class="field__label" for="email">Email</label>
                        <input
                            id="email"
                            class="input {{ $errors->has('email') ? 'is-error' : '' }}"
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            required
                            autofocus
                            autocomplete="email"
                        >
                        @error('email')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="password">Nuova password <span class="req">*</span></label>
                        <input
                            id="password"
                            class="input"
                            :class="pwError ? 'is-error' : ''"
                            type="password"
                            name="password"
                            x-model="password"
                            placeholder="min. 8 caratteri"
                            required
                            autocomplete="new-password"
                        >
                        <span x-show="pwError" class="field__error" x-text="pwError"></span>
                        @error('password')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                        <span class="field__hint">Almeno 8 caratteri, una maiuscola e un numero.</span>
                    </div>

                    <div class="field">
                        <label class="field__label" for="password_confirmation">Conferma password <span class="req">*</span></label>
                        <input
                            id="password_confirmation"
                            class="input"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <button class="btn btn--accent btn--lg btn--block" type="submit">
                        Reimposta password
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('resetForm', () => ({
                    password: '',
                    pwError: '',
                    validateAndSubmit(form) {
                        this.pwError = '';
                        const pw = this.password;
                        if (pw.length < 8) { this.pwError = 'La password deve avere almeno 8 caratteri.'; return; }
                        if (!/[A-Z]/.test(pw)) { this.pwError = 'Inserisci almeno una lettera maiuscola.'; return; }
                        if (!/[0-9]/.test(pw)) { this.pwError = 'Inserisci almeno un numero.'; return; }
                        form.submit();
                    },
                }));
            });
        </script>
    @endpush
@endsection
