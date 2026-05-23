@extends('layouts.guest')

@section('title', 'Password dimenticata')

@section('content')
    <div class="auth-shell">
        <div class="auth-shell__art">
            <div class="auth-shell__brand">
                <div class="auth-shell__brand-mark">BF</div>
                <span>BrainFlow</span>
            </div>
            <p class="auth-shell__quote">
                Nessun problema,<br>
                <span>ti rimettiamo in pista.</span>
            </p>
        </div>

        <div class="auth-shell__form-wrap">
            <div class="auth-form">
                <h1 class="auth-form__title">Password dimenticata?</h1>
                <p class="auth-form__sub">
                    Inserisci la tua email. Ti invieremo un link per reimpostare la password.
                </p>

                @if (session('status'))
                    <div class="info-banner">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" class="col" style="gap:14px;margin-top:8px">
                    @csrf

                    <div class="field">
                        <label class="field__label" for="email">Email</label>
                        <input
                            id="email"
                            class="input {{ $errors->has('email') ? 'is-error' : '' }}"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nome@azienda.it"
                            required
                            autofocus
                            autocomplete="email"
                        >
                        @error('email')
                        <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button class="btn btn--accent btn--lg btn--block" type="submit">
                        Invia link di reset
                    </button>

                    <p class="muted" style="text-align:center;font-size:13px">
                        <a href="{{ route('login') }}" style="text-decoration:underline">
                            Torna al login
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
@endsection
