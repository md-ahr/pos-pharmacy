@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container {{ $layout }}" @if($layout==='fullscreen' ) style="background-image: url('{{ $backgroundImage }}');" @endif @if($layout==='youtube-video') id="tyro-youtube-container" @endif>
    @if(in_array($layout, ['split-left', 'split-right']))
    <div class="background-panel" style="background-image: url('{{ $backgroundImage }}');">
        <div class="background-panel-content">
            <h1>{{ $otpConfig['background_title'] ?? 'Almost There!' }}</h1>
            <p>{{ $otpConfig['background_description'] ?? 'Enter the verification code we sent to your email to complete the login process.' }}</p>
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

            <!-- Success Message -->
            @if(session('success'))
            <div class="success-message-box">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            <!-- OTP Form -->
            <form method="POST" action="{{ route('tyro-login.otp.submit') }}">
                @csrf

                <!-- OTP Input -->
                <div class="form-group">
                    <label for="otp" class="form-label text-center">{{ $otpConfig['label'] ?? 'Verification Code' }}</label>
                    <div class="otp-input-container">
                        @for($i = 0; $i < $otpLength; $i++) <input type="text" class="otp-digit @error('otp') is-invalid @enderror" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" data-index="{{ $i }}" required>
                            @endfor
                    </div>
                    <input type="hidden" name="otp" id="otp-hidden" value="">
                    @error('otp')
                    <span class="error-message text-center">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    {{ $otpConfig['submit_button'] ?? 'Verify' }}
                </button>
            </form>

            <!-- Resend Section -->
            <div class="otp-actions">
                @if($canResend)
                <form method="POST" action="{{ route('tyro-login.otp.resend') }}" class="resend-form">
                    @csrf
                    <p class="resend-text">
                        Didn't receive the code?
                        <button type="submit" class="form-link resend-btn">
                            {{ $otpConfig['resend_button'] ?? 'Resend Code' }}
                        </button>
                    </p>
                    @if($resendCount > 0)
                    <p class="resend-count">Resends used: {{ $resendCount }}/{{ $maxResend }}</p>
                    @endif
                </form>
                @else
                <p class="resend-cooldown">
                    Resend available in <span id="cooldown-timer">{{ $remainingCooldown }}</span>s
                </p>
                @endif
            </div>

            <!-- Cancel Link -->
            <div class="form-footer">
                <p>
                    <a href="{{ route('tyro-login.otp.cancel') }}" class="form-link">Cancel and return to login</a>
                </p>
            </div>
        </div>
    </div>
</div>

@include('tyro-login::partials.backgrounds')

<style>
    .text-center {
        text-align: center;
        display: block;
        width: 100%;
    }

    .success-message-box {
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    html.dark .success-message-box {
        background-color: #052e16;
        border-color: #166534;
    }

    .success-message-box p {
        color: #059669;
        font-size: 0.9375rem;
        margin: 0;
    }

    html.dark .success-message-box p {
        color: #34d399;
    }

    .otp-input-container {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .otp-digit {
        width: 3rem;
        height: 3.5rem;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 600;
        border: 1px solid var(--input);
        border-radius: 0.5rem;
        background-color: var(--background);
        color: var(--foreground);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .otp-digit:focus {
        outline: none;
        border-color: var(--ring);
        box-shadow: 0 0 0 1px var(--ring);
    }

    .otp-digit.is-invalid {
        border-color: var(--destructive);
    }

    .otp-digit.filled {
        border-color: var(--ring);
        background-color: var(--muted);
    }

    .otp-actions {
        text-align: center;
        margin-top: 1.5rem;
    }

    .resend-form {
        display: inline;
    }

    .resend-text {
        color: var(--muted-foreground);
        font-size: 0.9375rem;
        margin: 0;
    }

    .resend-btn {
        background: none;
        border: none;
        padding: 0;
        font: inherit;
        cursor: pointer;
    }

    .resend-count {
        color: var(--muted-foreground);
        font-size: 0.8125rem;
        margin-top: 0.5rem;
    }

    .resend-cooldown {
        color: var(--muted-foreground);
        font-size: 0.9375rem;
    }

    #cooldown-timer {
        font-weight: 600;
        color: var(--foreground);
    }

    .form-footer {
        margin-top: 1.5rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const digits = document.querySelectorAll('.otp-digit');
        const hiddenInput = document.getElementById('otp-hidden');

        function updateHiddenInput() {
            let otp = '';
            digits.forEach(digit => {
                otp += digit.value;
            });
            hiddenInput.value = otp;
        }

        function updateFilledState() {
            digits.forEach(digit => {
                if (digit.value) {
                    digit.classList.add('filled');
                } else {
                    digit.classList.remove('filled');
                }
            });
        }

        digits.forEach((digit, index) => {
            digit.addEventListener('input', function (e) {
                // Allow only numbers
                this.value = this.value.replace(/[^0-9]/g, '');

                if (this.value && index < digits.length - 1) {
                    digits[index + 1].focus();
                }

                updateHiddenInput();
                updateFilledState();

                if (hiddenInput.value.length === digits.length) {
                    this.closest('form').requestSubmit();
                }
            });

            digit.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    digits[index - 1].focus();
                }

                // Allow paste
                if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                    return;
                }

                // Arrow navigation
                if (e.key === 'ArrowLeft' && index > 0) {
                    e.preventDefault();
                    digits[index - 1].focus();
                }
                if (e.key === 'ArrowRight' && index < digits.length - 1) {
                    e.preventDefault();
                    digits[index + 1].focus();
                }
            });

            digit.addEventListener('paste', function (e) {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                const numbers = pastedData.replace(/[^0-9]/g, '').split('').slice(0, digits.length);

                numbers.forEach((num, i) => {
                    if (digits[i]) {
                        digits[i].value = num;
                    }
                });

                if (numbers.length > 0) {
                    const lastIndex = Math.min(numbers.length - 1, digits.length - 1);
                    digits[lastIndex].focus();
                }

                updateHiddenInput();
                updateFilledState();
            });

            digit.addEventListener('focus', function () {
                this.select();
            });
        });

        // Focus first digit on load
        if (digits[0]) {
            digits[0].focus();
        }

        // Cooldown timer
        const cooldownEl = document.getElementById('cooldown-timer');
        if (cooldownEl) {
            let remaining = parseInt(cooldownEl.textContent, 10);

            const timer = setInterval(function () {
                remaining--;
                cooldownEl.textContent = remaining;

                if (remaining <= 0) {
                    clearInterval(timer);
                    location.reload();
                }
            }, 1000);
        }
    });
</script>
@endsection