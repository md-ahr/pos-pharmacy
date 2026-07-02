@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container {{ $layout }}" @if($layout==='fullscreen' ) style="background-image: url('{{ $backgroundImage }}');" @endif @if($layout==='youtube-video') id="tyro-youtube-container" @endif>
    @if(in_array($layout, ['split-left', 'split-right']))
    <div class="background-panel" style="background-image: url('{{ $backgroundImage }}');">
        <div class="background-panel-content">
            <h1>{{ $pageContent['background_title'] ?? 'Forgot Your Password?' }}</h1>
            <p>{{ $pageContent['background_description'] ?? 'No worries! Enter your email and we\'ll send you a link to reset your password.' }}</p>
        </div>
    </div>
    @endif

    <div class="form-panel">
        <div class="form-card">
            @include('pharmacy.partials.auth-logo')

            <!-- Header -->
            <div class="form-header">
                <h2>{{ $pageContent['title'] ?? 'Forgot Password?' }}</h2>
                <p>{{ $pageContent['subtitle'] ?? 'Enter your email and we\'ll send you a reset link.' }}</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
            <div class="success-message">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
            <div class="error-list">
                <ul>
                    <li>{{ session('error') }}</li>
                </ul>
            </div>
            @endif

            <!-- Forgot Password Form -->
            <form method="POST" action="{{ route('tyro-login.password.email') }}">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="email@example.com">
                    @error('email')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    Send Reset Link
                </button>
            </form>

            <!-- Back to Login -->
            <div class="form-footer">
                <p>
                    Remember your password?
                    <a href="{{ route('tyro-login.login') }}" class="form-link">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

@include('tyro-login::partials.backgrounds')

<style>
    .success-message {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    html.dark .success-message {
        background-color: #052e16;
        border-color: #166534;
    }

    .success-message p {
        color: #059669;
        font-size: 0.875rem;
        margin: 0;
    }

    html.dark .success-message p {
        color: #34d399;
    }
</style>
@endsection