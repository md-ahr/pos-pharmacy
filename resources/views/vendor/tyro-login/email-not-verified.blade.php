@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container {{ $layout }}" @if($layout==='fullscreen' ) style="background-image: url('{{ $backgroundImage }}');" @endif @if($layout==='youtube-video') id="tyro-youtube-container" @endif>
    @if(in_array($layout, ['split-left', 'split-right']))
    <div class="background-panel" style="background-image: url('{{ $backgroundImage }}');">
        <div class="background-panel-content">
            <h1>{{ $pageContent['background_title'] ?? 'Email Verification Required' }}</h1>
            <p>{{ $pageContent['background_description'] ?? 'Your email address needs to be verified before you can access your account.' }}</p>
        </div>
    </div>
    @endif

    <div class="form-panel">
        <div class="form-card">
            @include('pharmacy.partials.auth-logo')

            <!-- Warning Icon -->
            <!-- <div class="warning-icon-container">
                <svg class="warning-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div> -->

            <!-- Header -->
            <div class="form-header">
                <h2>{{ $pageContent['title'] ?? 'Email Not Verified' }}</h2>
                <p>{{ $pageContent['subtitle'] ?? 'Please verify your email address to continue.' }}</p>
            </div>

            <!-- Email Address -->
            <div class="email-notice">
                <p>A verification link was sent to:</p>
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
                <p>Please check your inbox and click the verification link to activate your account. If you don't see the email, check your spam folder.</p>
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
    .warning-icon-container {
        text-align: center;
        margin-bottom: 1rem;
    }

    .warning-icon {
        width: 3.5rem;
        height: 3.5rem;
        color: #f59e0b;
    }

    html.dark .warning-icon {
        color: #fbbf24;
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