@extends('tyro-login::layouts.auth')

@section('content')
@php
$passkeysEnabled = ($passkeysEnabled ?? false);
$webauthnToken = $passkeysEnabled ? ' webauthn' : '';
@endphp
<div class="auth-container {{ $layout }}" @if($layout==='fullscreen' ) style="background-image: url('{{ $backgroundImage }}');" @endif @if($layout==='youtube-video') id="tyro-youtube-container" @endif>
    @if(in_array($layout, ['split-left', 'split-right']))
    <div class="background-panel" style="background-image: url('{{ $backgroundImage }}');">
        <div class="background-panel-content">
            <h1>{{ $pageContent['background_title'] ?? 'Welcome Back!' }}</h1>
            <p>{{ $pageContent['background_description'] ?? 'Sign in to access your account and continue where you left off. We\'re glad to see you again.' }}</p>
        </div>
    </div>
    @endif

    <div class="form-panel">
        <div class="form-card">
            @include('pharmacy.partials.auth-logo')

            <!-- Header -->
            <div class="form-header">
                <h2>Log in to your account</h2>
                @if($features['disable_password'] ?? false)
                    @if(($loginField ?? 'email') === 'username')
                    <p>Enter your username below to log in</p>
                    @elseif(($loginField ?? 'email') === 'both')
                    <p>Enter your email or username below to log in</p>
                    @else
                    <p>Enter your email below to log in</p>
                    @endif
                @else
                    @if(($loginField ?? 'email') === 'both')
                    <p>Enter your email or username and password below to log in</p>
                    @elseif(($loginField ?? 'email') === 'username')
                    <p>Enter your username and password below to log in</p>
                    @else
                    <p>Enter your email and password below to log in</p>
                    @endif
                @endif
            </div>

            <!-- Success Message -->
            @if(session('success'))
            <div class="alert alert-success" style="padding: 0.875rem 1rem; margin-bottom: 1.5rem; background-color: #d1fae5; border: 1px solid #6ee7b7; border-radius: 0.5rem; color: #065f46; font-size: 0.9375rem;">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error" style="padding: 0.875rem 1rem; margin-bottom: 1.5rem; background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 0.5rem; color: #991b1b; font-size: 0.9375rem;">
                {{ $errors->first() }}
            </div>
            <style>
                html.dark .alert-error { background-color: #450a0a !important; border-color: #7f1d1d !important; color: #fca5a5 !important; }
            </style>
            @endif

            <!-- Passkey Login -->
            @if($passkeysEnabled)
            @include('tyro-login::partials.passkey-login', ['features' => $features ?? []])
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('tyro-login.login.submit') }}">
                @csrf

                <!-- Login Field (Email, Username, or Both) -->
                @if(($loginField ?? 'email') === 'both')
                <div class="form-group">
                    <label for="login" class="form-label">Email or Username</label>
                    <input type="text" id="login" name="login" class="form-input @error('login') is-invalid @enderror" value="{{ old('login') }}" required autocomplete="username{{ $webauthnToken }}" autofocus placeholder="Email or username">
                    @error('login')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                @elseif(($loginField ?? 'email') === 'username')
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-input @error('username') is-invalid @enderror" value="{{ old('username') }}" required autocomplete="username{{ $webauthnToken }}" autofocus placeholder="Username">
                    @error('username')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                @else
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email{{ $webauthnToken }}" autofocus placeholder="email@example.com">
                    @error('email')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                @if(!($features['disable_password'] ?? false))
                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="Password">
                    @error('password')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    @if($features['remember_me'] ?? true)
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="checkbox-label">Remember me</label>
                    </div>
                    @else
                    <div></div>
                    @endif

                    @if($features['forgot_password'] ?? true)
                    <a href="{{ route('tyro-login.password.request') }}" class="form-link">Forgot password?</a>
                    @endif
                </div>
                @endif

                <!-- Captcha -->
                @if($captchaEnabled ?? false)
                <div class="form-group captcha-group">
                    <label for="captcha_answer" class="form-label">{{ $captchaConfig['label'] ?? 'Security Check' }}</label>
                    <div class="captcha-container">
                        <span class="captcha-question">{{ $captchaQuestion }}</span>
                        <input type="number" id="captcha_answer" name="captcha_answer" class="form-input captcha-input @error('captcha_answer') is-invalid @enderror" required autocomplete="off" placeholder="{{ $captchaConfig['placeholder'] ?? 'Enter the answer' }}">
                    </div>
                    @error('captcha_answer')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                @if(!($features['disable_password'] ?? false))
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    Log in
                </button>
                @endif
            </form>

            <!-- Magic Login Button -->
            @if(config('tyro-login.features.magic_links_enabled', false))
            @if(!($features['disable_password'] ?? false))
            <div class="form-divider" style="margin: 1.5rem 0; text-align: center; position: relative;">
                <span style="background-color: var(--background); padding: 0 1rem; position: relative; z-index: 1; color: #9ca3af; font-size: 0.875rem;">or</span>
                <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background-color: #e5e7eb;"></div>
            </div>
            @endif

            <form method="POST" action="{{ route('tyro-login.magic-link.request') }}" id="magic-link-form" @if(!($features['disable_password'] ?? false)) style="margin-top: 0;" @else style="margin-top: 0.5rem;" @endif>
                @csrf
                <input type="hidden" name="email" id="magic-email" value="">
                <input type="hidden" name="username" id="magic-username" value="">
                <input type="hidden" name="login" id="magic-login" value="">
                
                <button type="submit" class="btn btn-secondary" id="magic-login-btn" style="width: 100%; background-color: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;">
                    <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; vertical-align: middle; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Send Magic Login Link
                </button>
            </form>

            <script>
                (function() {
                    var magicBtn = document.getElementById('magic-login-btn');
                    var magicForm = document.getElementById('magic-link-form');

                    magicBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        @if(($loginField ?? 'email') === 'both')
                            var loginValue = document.getElementById('login')?.value || '';
                            document.getElementById('magic-login').value = loginValue;
                        @elseif(($loginField ?? 'email') === 'username')
                            var usernameValue = document.getElementById('username')?.value || '';
                            document.getElementById('magic-username').value = usernameValue;
                        @else
                            var emailValue = document.getElementById('email')?.value || '';
                            document.getElementById('magic-email').value = emailValue;
                        @endif

                        magicBtn.disabled = true;
                        magicBtn.classList.add('loading');
                        magicBtn.innerHTML = '<svg style="width:1.25rem;height:1.25rem;display:inline-block;vertical-align:middle;margin-right:0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>Working...';

                        magicForm.submit();
                    });

                    magicForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        magicBtn.click();
                    });
                })();
            </script>
            @endif
            <!-- Register Link -->
            @if($registrationEnabled ?? true)
            <div class="form-footer">
                <p>
                    Don't have an account?
                    <a href="{{ route('tyro-login.register') }}" class="form-link">Sign up</a>
                </p>
            </div>
            @endif

            <!-- Social Login -->
            @include('tyro-login::partials.social-login', ['action' => 'login'])
        </div>
    </div>
</div>

@include('tyro-login::partials.backgrounds')

<style>
    .captcha-group {
        margin-bottom: 1.25rem;
    }

    .captcha-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .captcha-question {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1rem;
        background-color: var(--muted);
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 1rem;
        color: var(--foreground);
        white-space: nowrap;
        min-width: 100px;
        text-align: center;
    }

    .captcha-input {
        flex: 1;
        text-align: center;
        font-weight: 500;
    }

    /* Hide number input spinners */
    .captcha-input::-webkit-outer-spin-button,
    .captcha-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .captcha-input[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endsection
