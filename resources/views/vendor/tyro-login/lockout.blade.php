@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container {{ $layout }}" @if($layout==='fullscreen' ) style="background-image: url('{{ $backgroundImage }}');" @endif @if($layout==='youtube-video') id="tyro-youtube-container" @endif>
    @if(in_array($layout, ['split-left', 'split-right']))
    <div class="background-panel" style="background-image: url('{{ $backgroundImage }}');">
        <div class="background-panel-content">
            <h1>Security Notice</h1>
            <p>Your account has been temporarily locked for security reasons. Please wait and try again later.</p>
        </div>
    </div>
    @endif

    <div class="form-panel">
        <div class="form-card">
            @include('pharmacy.partials.auth-logo')

            <!-- Header -->
            <div class="form-header">
                <h2>{{ $title }}</h2>
                <p>{{ $subtitle }}</p>
            </div>

            <!-- Lockout Message -->
            <div class="lockout-message">
                <p>{{ $message }}</p>
            </div>

            <!-- Countdown Timer -->
            @if($releaseTime)
            <div class="countdown-container">
                <div class="countdown-label">Time remaining</div>
                <div class="countdown-timer" id="countdown" data-release="{{ $releaseTime }}">
                    <span class="countdown-value" id="countdown-minutes">{{ str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="countdown-separator">:</span>
                    <span class="countdown-value" id="countdown-seconds">00</span>
                </div>
            </div>
            @endif

            <!-- Try Again Button -->
            <a href="{{ route('tyro-login.login') }}" class="btn btn-primary" id="try-again-btn">
                Try again
            </a>

            <!-- Help Text -->
            <div class="form-footer">
                <p>
                    If you believe this is an error, please contact support.
                </p>
            </div>
        </div>
    </div>
</div>

@include('tyro-login::partials.backgrounds')

<style>
    .lockout-message {
        background-color: color-mix(in srgb, var(--destructive), transparent 90%);
        border: 1px solid var(--destructive);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .lockout-message p {
        color: var(--destructive);
        font-size: 0.9375rem;
        margin: 0;
        line-height: 1.5;
    }

    .countdown-container {
        text-align: center;
        margin-bottom: 1.5rem;
        padding: 1.25rem;
        background-color: var(--muted);
        border-radius: 0.5rem;
        border: 1px solid var(--border);
    }

    .countdown-label {
        font-size: 0.8125rem;
        color: var(--muted-foreground);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .countdown-timer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }

    .countdown-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--foreground);
        font-variant-numeric: tabular-nums;
        min-width: 3rem;
        text-align: center;
    }

    .countdown-separator {
        font-size: 2rem;
        font-weight: 700;
        color: var(--muted-foreground);
        animation: blink 1s infinite;
    }

    @keyframes blink {

        0%,
        50% {
            opacity: 1;
        }

        51%,
        100% {
            opacity: 0.3;
        }
    }

    .countdown-expired .countdown-value,
    .countdown-expired .countdown-separator {
        color: var(--muted-foreground);
        animation: none;
    }

    #try-again-btn {
        text-decoration: none;
    }

    #try-again-btn.disabled {
        opacity: 0.5;
        pointer-events: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countdownEl = document.getElementById('countdown');
        const tryAgainBtn = document.getElementById('try-again-btn');
        const loginUrl = '{{ route('tyro-login.login') }}';
        const autoRedirect = {{ config('tyro-login.lockout.auto_redirect', true) ? 'true' : 'false'
    }};

    if (!countdownEl) return;

    const releaseTime = parseInt(countdownEl.dataset.release, 10);
    const minutesEl = document.getElementById('countdown-minutes');
    const secondsEl = document.getElementById('countdown-seconds');

    function updateCountdown() {
        const now = Math.floor(Date.now() / 1000);
        const remaining = releaseTime - now;

        if (remaining <= 0) {
            // Lockout expired
            minutesEl.textContent = '00';
            secondsEl.textContent = '00';
            countdownEl.classList.add('countdown-expired');
            tryAgainBtn.classList.remove('disabled');

            // Auto redirect if enabled
            if (autoRedirect) {
                window.location.href = loginUrl;
            }
            return;
        }

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;

        minutesEl.textContent = String(minutes).padStart(2, '0');
        secondsEl.textContent = String(seconds).padStart(2, '0');

        requestAnimationFrame(() => {
            setTimeout(updateCountdown, 1000);
        });
    }

    // Initially disable the button if there's remaining time
    const now = Math.floor(Date.now() / 1000);
    if (releaseTime - now > 0) {
        tryAgainBtn.classList.add('disabled');
    }

    updateCountdown();
    });
</script>
@endsection