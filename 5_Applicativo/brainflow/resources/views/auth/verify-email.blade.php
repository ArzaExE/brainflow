@extends('layouts.guest')

@section('title', 'Verifica email')

@section('content')
    <div class="auth-shell">
        <div class="auth-shell__art">
            <div class="auth-shell__brand">
                <div class="auth-shell__brand-mark">BF</div>
                <span>BrainFlow</span>
            </div>
            <p class="auth-shell__quote">
                Un ultimo passo,<br>
                <span>per iniziare.</span>
            </p>
        </div>

        <div class="auth-shell__form-wrap">
            <div class="auth-form">
                <h1 class="auth-form__title">Verifica la tua email</h1>
                <p class="auth-form__sub">
                    Grazie per esserti registrato! Ti abbiamo inviato un'email con un link di conferma.
                    Cliccalo per attivare il tuo account.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="info-banner">
                        Una nuova email di verifica è stata inviata all'indirizzo che hai fornito.
                    </div>
                @endif

                <div class="col" style="gap:12px;margin-top:16px">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn--accent btn--block">
                            Reinvia email di verifica
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn--ghost btn--block">
                            Esci
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
