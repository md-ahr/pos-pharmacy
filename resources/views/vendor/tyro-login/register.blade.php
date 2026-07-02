@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container {{ $layout }}" @if($layout==='fullscreen' ) style="background-image: url('{{ $backgroundImage }}');" @endif @if($layout==='youtube-video') id="tyro-youtube-container" @endif>
    @if(in_array($layout, ['split-left', 'split-right']))
    <div class="background-panel" style="background-image: url('{{ $backgroundImage }}');">
        <div class="background-panel-content">
            <h1>{{ $pageContent['background_title'] ?? 'Join Us Today!' }}</h1>
            <p>{{ $pageContent['background_description'] ?? 'Create your account and start your journey with us. It only takes a minute to get started.' }}</p>
        </div>
    </div>
    @endif

    <div class="form-panel">
        <div class="form-card">
            @include('pharmacy.partials.auth-logo')

            <!-- Header -->
            <div class="form-header">
                <h2>Create an account</h2>
                <p>Enter your details below to create your account</p>
            </div>

            <!-- Registration Form -->
            <form method="POST" action="{{ route('tyro-login.register.submit') }}">
                @csrf

                <!-- Preserve invitation hash if present -->
                @if(request()->query('invite') ?? $inviteHash ?? null)
                <input type="hidden" name="invite" value="{{ request()->query('invite') ?? $inviteHash }}">
                @endif

                <!-- Pharmacy Name Field -->
                <div class="form-group">
                    <label for="pharmacy_name" class="form-label">Pharmacy name</label>
                    <input type="text" id="pharmacy_name" name="pharmacy_name" class="form-input @error('pharmacy_name') is-invalid @enderror" value="{{ old('pharmacy_name') }}" required autocomplete="organization" placeholder="Sunrise Pharmacy">
                    @error('pharmacy_name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Name Field -->
                <div class="form-group">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Name">
                    @error('name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" placeholder="email@example.com">
                    @error('email')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="Password" minlength="{{ config('tyro-login.password.min_length', 8) }}">
                    @error('password')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                @if($requirePasswordConfirmation ?? true)
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" placeholder="Confirm Password">
                    @error('password_confirmation')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
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

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                    Create account
                </button>
            </form>

            <!-- Login Link -->
            <div class="form-footer">
                <p>
                    Already have an account?
                    <a href="{{ route('tyro-login.login') }}" class="form-link">Log in</a>
                </p>
            </div>

            <!-- Social Login -->
            @include('tyro-login::partials.social-login', ['action' => 'register'])
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