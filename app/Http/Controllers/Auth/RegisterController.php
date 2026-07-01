<?php

namespace App\Http\Controllers\Auth;

use App\Services\TenantProvisioner;
use HasinHayder\TyroLogin\Helpers\InvitationHelper;
use HasinHayder\TyroLogin\Http\Controllers\RegisterController as TyroRegisterController;
use HasinHayder\TyroLogin\Http\Controllers\VerificationController;
use HasinHayder\TyroLogin\Mail\WelcomeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RegisterController extends TyroRegisterController
{
    /**
     * Handle a registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        if (! config('tyro-login.registration.enabled', true)) {
            abort(403, 'Registration is disabled.');
        }

        $rules = $this->getValidationRules();

        if (config('tyro-login.captcha.enabled_register', false)) {
            $rules['captcha_answer'] = ['required', 'numeric'];
        }

        $validated = $request->validate($rules);

        if (config('tyro-login.captcha.enabled_register', false)) {
            if (! $this->validateCaptcha($request, $validated['captcha_answer'])) {
                $this->generateCaptcha($request);

                throw ValidationException::withMessages([
                    'captcha_answer' => config('tyro-login.captcha.error_message', 'Incorrect answer. Please try again.'),
                ]);
            }

            unset($validated['captcha_answer']);
        }

        if (config('tyro-login.password.disallow_user_info', false)) {
            $this->validatePasswordNotContainingUserInfo($request, $validated);
        }

        $user = app(TenantProvisioner::class)->provision($validated);

        event(new Registered($user));

        $invitationHash = $request->input('invite') ?? $request->query('invite');
        if ($invitationHash) {
            try {
                InvitationHelper::trackReferral($invitationHash, $user->id);
            } catch (\Exception $e) {
                report($e);
            }
        }

        if (config('tyro-login.registration.require_email_verification', false)) {
            VerificationController::generateVerificationUrl($user);
            $request->session()->put('tyro-login.verification.email', $user->email);

            return redirect()->route('tyro-login.verification.notice');
        }

        if (config('tyro-login.emails.welcome.enabled', true)) {
            Mail::to($user->email)->send(new WelcomeMail(
                userName: $user->name ?? 'User',
                loginUrl: url(config('tyro-login.routes.prefix', '').'/login')
            ));
        }

        if (config('tyro-login.registration.auto_login', true)) {
            if (config('tyro-login.two_factor.enabled', false)) {
                $request->session()->put('login.id', $user->id);
                $request->session()->put('login.remember', true);

                return redirect()->route('tyro-login.two-factor.setup');
            }

            Auth::login($user);
        }

        return redirect(config('tyro-login.redirects.after_register', '/'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function getValidationRules(): array
    {
        $rules = parent::getValidationRules();

        $rules['pharmacy_name'] = ['required', 'string', 'max:255'];

        return $rules;
    }

    protected function assignTyroRole($user): void
    {
        // Owner role is assigned during tenant provisioning.
    }
}
