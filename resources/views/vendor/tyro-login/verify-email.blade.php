@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container {{ $layout }}" @if($layout==='fullscreen' ) style="background-image: url('{{ $backgroundImage }}');" @endif @if($layout==='youtube-video') id="tyro-youtube-container" @endif>
    @if(in_array($layout, ['split-left', 'split-right']))
    <div class="background-panel" style="background-image: url('{{ $backgroundImage }}');">
        <div class="background-panel-content">
            <h1>{{ $pageContent['background_title'] ?? 'Check Your Email' }}</h1>
            <p>{{ $pageContent['background_description'] ?? 'We\'ve sent a verification link to your email address. Click the link to verify your account.' }}</p>
        </div>
    </div>
    @endif

    <div class="form-panel">
        <div class="form-card">
            @include('pharmacy.partials.auth-logo')

            <!-- Email Icon -->
            <!-- <div class="email-icon-container">
                <svg class="email-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div> -->

            <!-- Header -->
            <div class="form-header">
                <h2>{{ $pageContent['title'] ?? 'Verify Your Email' }}</h2>
                <p>{{ $pageContent['subtitle'] ?? 'We\'ve sent a verification link to your email address.' }}</p>
            </div>

            <!-- Email Address -->
            <div class="email-notice">
                <p>We sent a verification link to:</p>
                <p class="email-address">{{ $email }}</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
            <div class="success-message">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            <!-- Instructions -->
            <div class="verify-instructions">
                <p>Click the link in your email to verify your account. If you don't see the email, check your spam folder.</p>
            </div>

            <!-- Resend Link -->
            <form method="POST" action="{{ route('tyro-login.verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Resend Verification Email
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
    .email-icon-container {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .email-icon {
        width: 4rem;
        height: 4rem;
        color: var(--foreground);
        opacity: 0.8;
    }

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

    .verify-instructions {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .verify-instructions p {
        color: var(--muted-foreground);
        font-size: 0.875rem;
        line-height: 1.6;
    }
</style>
@endsection