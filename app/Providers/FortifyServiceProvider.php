<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\Gallery;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureRecaptchaAuth();
    }

    private function configureRecaptchaAuth(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $recaptcha = app(RecaptchaService::class);

            if ($recaptcha->isEnabled()) {
                $token = (string) $request->input('recaptcha_token', '');

                if (! $recaptcha->verify($token)) {
                    throw ValidationException::withMessages([
                        Fortify::username() => __('Verifikasi reCAPTCHA gagal. Coba muat ulang halaman.'),
                    ]);
                }
            }

            $user = User::where(Fortify::username(), $request->input(Fortify::username()))->first();

            if ($user && Hash::check((string) $request->input('password'), $user->password)) {
                return $user;
            }

            return null;
        });
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => view('auth.login', [
            'status' => $request->session()->get('status'),
            'galleries' => Gallery::active()->take(6)->get(['id', 'title', 'image_path']),
            'recaptchaEnabled' => app(RecaptchaService::class)->isEnabled(),
            'recaptchaSiteKey' => app(RecaptchaService::class)->siteKey(),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/register', [
            'recaptchaEnabled' => app(RecaptchaService::class)->isEnabled(),
            'recaptchaSiteKey' => app(RecaptchaService::class)->siteKey(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/forgot-password', [
            'status' => $request->session()->get('status'),
            'recaptchaEnabled' => app(RecaptchaService::class)->isEnabled(),
            'recaptchaSiteKey' => app(RecaptchaService::class)->siteKey(),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/reset-password', [
            'token' => $request->route('token'),
            'email' => $request->query('email', ''),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/two-factor-challenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/confirm-password'));

        Fortify::verifyEmailView(fn () => Inertia::render('auth/verify-email'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
