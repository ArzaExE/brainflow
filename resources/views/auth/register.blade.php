@extends('layouts.guest')

@section('title', 'Registrati')

@section('content')
    <div class="auth-shell" x-data="registerForm()">

        {{-- Left: decorative panel --}}
        <div class="auth-shell__art">
            <div class="auth-shell__brand">
                <div class="auth-shell__brand-mark">BF</div>
                <span>BrainFlow</span>
            </div>

            <div>
                <p class="auth-shell__quote">
                    Unisciti al team,<br>
                    <span>inizia a collaborare.</span>
                </p>
            </div>

            <div class="auth-shell__foot">
                <span>BrainFlow © {{ date('Y') }}</span>
                <span>Kanban · Task · Team</span>
            </div>

            <div class="auth-deco"></div>
        </div>

        {{-- Right: form panel --}}
        <div class="auth-shell__form-wrap">
            <div class="auth-form">

                <h1 class="auth-form__title">Crea account</h1>
                <p class="auth-form__sub">Inizia la tua esperienza su BrainFlow</p>

                <form action="{{ route('register') }}" method="POST" class="col" style="gap:14px;margin-top:8px"
                      @submit.prevent="validateAndSubmit($el)">
                    @csrf

                    <div class="field">
                        <label class="field__label" for="reg_name">Nome completo <span class="req">*</span></label>
                        <input
                            id="reg_name"
                            class="input {{ $errors->has('name') ? 'is-error' : '' }}"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Mario Rossi"
                            required
                            autofocus
                            autocomplete="name"
                        >
                        @error('name')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="reg_email">Email <span class="req">*</span></label>
                        <input
                            id="reg_email"
                            class="input {{ $errors->has('email') ? 'is-error' : '' }}"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nome@azienda.it"
                            required
                            autocomplete="email"
                        >
                        @error('email')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="reg_password">Password <span class="req">*</span></label>
                        <input
                            id="reg_password"
                            class="input"
                            :class="(pwError || '{{ $errors->has('password') ? '1' : '' }}') ? 'is-error' : ''"
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
                        <label class="field__label" for="reg_password_confirm">Conferma Password <span class="req">*</span></label>
                        <input
                            id="reg_password_confirm"
                            class="input"
                            type="password"
                            name="password_confirmation"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <button class="btn btn--accent btn--lg btn--block" type="submit">
                        Crea account
                    </button>

                    <p class="muted" style="text-align:center;font-size:13px">
                        Hai già un account?
                        <a href="{{ route('login') }}" style="text-decoration:underline">
                            Accedi
                        </a>
                    </p>
                </form>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('registerForm', () => ({
                    password: '',
                    pwError:  '',

                    validateAndSubmit(form) {
                        this.pwError = '';
                        const pw = this.password;
                        if (pw.length < 8) {
                            this.pwError = 'La password deve avere almeno 8 caratteri.';
                            return;
                        }
                        if (!/[A-Z]/.test(pw)) {
                            this.pwError = 'Inserisci almeno una lettera maiuscola.';
                            return;
                        }
                        if (!/[0-9]/.test(pw)) {
                            this.pwError = 'Inserisci almeno un numero.';
                            return;
                        }
                        form.submit();
                    },
                }));
            });
        </script>
    @endpush
@endsection
