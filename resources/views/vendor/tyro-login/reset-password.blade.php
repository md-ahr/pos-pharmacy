@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container {{ $layout }}" @if($layout==='fullscreen' ) style="background-image: url('{{ $backgroundImage }}');" @endif @if($layout==='youtube-video') id="tyro-youtube-container" @endif>
    @if(in_array($layout, ['split-left', 'split-right']))
    <div class="background-panel" style="background-image: url('{{ $backgroundImage }}');">
        <div class="background-panel-content">
            <h1>{{ $pageContent['background_title'] ?? 'Reset Your Password' }}</h1>
            <p>{{ $pageContent['background_description'] ?? 'Create a new secure password for your account.' }}</p>
        </div>
    </div>
    @endif

    <div class="form-panel">
        <div class="form-card">
            @include('pharmacy.partials.auth-logo')

            <!-- Header -->
            <div class="form-header">
                <h2>{{ $pageContent['title'] ?? 'Reset Password' }}</h2>
                <p>{{ $pageContent['subtitle'] ?? 'Enter your new password below.' }}</p>
            </div>

            <!-- Email Address -->
            <div class="email-notice">
                <p>Resetting password for:</p>
                <p class="email-address">{{ $email }}</p>
            </div>

            <!-- Error Message -->
            @if(session('error'))
            <div class="error-list">
                <ul>
                    <li>{{ session('error') }}</li>
                </ul>
            </div>
            @endif

            <!-- Reset Password Form -->
            <form method="POST" action="{{ route('tyro-login.password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror" required autocomplete="new-password" autofocus placeholder="New password" minlength="{{ config('tyro-login.password.min_length', 8) }}">
                    @error('password')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" placeholder="Confirm new password">
                    @error('password_confirmation')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                    Reset Password
                </button>
            </form>

            <!-- Back to Login -->
            <div class="form-footer">
                <p>
                    <a href="{{ route('tyro-login.login') }}" class="form-link">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

@include('tyro-login::partials.backgrounds')

<style>
    .email-notice {
        text-align: center;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background-color: var(--muted);
        border-radius: 0.5rem;
        border: 1px solid var(--border);
    }

    .email-notice p {
        color: var(--muted-foreground);
        font-size: 0.875rem;
        margin: 0;
    }

    .email-notice .email-address {
        color: var(--foreground);
        font-weight: 600;
        font-size: 1rem;
        margin-top: 0.25rem;
    }
</style>
@endsection