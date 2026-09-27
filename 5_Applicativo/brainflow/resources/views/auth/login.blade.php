@extends('layouts.guest')

@section('title', 'Accedi')

@section('content')
    <div class="auth-shell">

        {{-- Left: decorative panel --}}
        <div class="auth-shell__art">
            <div class="auth-shell__brand">
                <div class="auth-shell__brand-mark">BF</div>
                <span>BrainFlow</span>
            </div>

            <div>
                <p class="auth-shell__quote">
                    Organizza il tuo team,<br>
                    <span>amplifica la produttività.</span>
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

                <h1 class="auth-form__title">Bentornato</h1>
                <p class="auth-form__sub">Accedi al tuo workspace BrainFlow</p>

                <form action="{{ route('login') }}" method="POST" class="col" style="gap:14px;margin-top:8px">
                    @csrf

                    {{-- Messaggio di sessione (es. "Password reimpostata con successo") --}}
                    @if (session('status'))
                        <div class="info-banner">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Errore generico di autenticazione --}}
                    @if ($errors->any())
                        <div class="warn-banner">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="field">
                        <label class="field__label" for="login_email">Email</label>
                        <input
                            id="login_email"
                            class="input {{ $errors->has('email') ? 'is-error' : '' }}"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nome@azienda.it"
                            required
                            autofocus
                            autocomplete="email"
                        >
                    </div>

                    <div class="field">
                        <label class="field__label" for="login_password">Password</label>
                        <input
                            id="login_password"
                            class="input {{ $errors->has('password') ? 'is-error' : '' }}"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <div class="row" style="justify-content:space-between">
                        <label class="row" style="gap:8px;font-size:13px;cursor:pointer">
                            <input class="checkbox" type="checkbox" name="remember">
                            Ricordami
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="muted" style="font-size:13px;text-decoration:underline">
                                Password dimenticata?
                            </a>
                        @endif
                    </div>

                    <button class="btn btn--accent btn--lg btn--block" type="submit">
                        Accedi
                    </button>

                    <p class="muted" style="text-align:center;font-size:13px">
                        Non hai un account?
                        <a href="{{ route('register') }}" style="text-decoration:underline">
                            Registrati
                        </a>
                    </p>
                </form>

            </div>
        </div>
    </div>
@endsection
