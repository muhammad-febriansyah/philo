<?php

namespace App\Http\Middleware;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    /**
     * Route names that require a reCAPTCHA token. The map's value is the
     * field where the validation error will be attached.
     */
    private const PROTECTED_ROUTES = [
        'password.email' => 'email',
    ];

    public function __construct(private RecaptchaService $recaptcha) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->recaptcha->isEnabled()) {
            return $next($request);
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        if (! array_key_exists($routeName, self::PROTECTED_ROUTES)) {
            return $next($request);
        }

        $token = (string) $request->input('recaptcha_token', '');

        if (! $this->recaptcha->verify($token)) {
            throw ValidationException::withMessages([
                self::PROTECTED_ROUTES[$routeName] => __('Verifikasi reCAPTCHA gagal. Coba muat ulang halaman.'),
            ]);
        }

        return $next($request);
    }
}
