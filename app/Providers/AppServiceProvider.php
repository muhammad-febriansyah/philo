<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
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
        $this->configureDefaults();
        $this->shareSettingsToViews();
        $this->shareEmailLayoutData();
    }

    protected function shareEmailLayoutData(): void
    {
        View::composer('emails.*', function ($view) {
            try {
                $settings = Setting::getMany([
                    'site_name', 'logo_path', 'email_logo_url', 'footer_text',
                    'instagram_url', 'facebook_url', 'whatsapp_number',
                ]);
            } catch (\Throwable) {
                $settings = [];
            }

            $appUrl = (string) config('app.url');
            $isLocalUrl = preg_match('#^https?://(localhost|127\.0\.0\.1|192\.168\.|10\.|0\.0\.0\.0)#', $appUrl) === 1;

            // Resolve email logo: explicit override → uploaded logo via DB → null.
            // Override is useful when APP_URL is local (e.g. dev) and admin wants
            // to point to a publicly hosted version of the logo for emails.
            $logoUrl = null;
            $override = trim((string) ($settings['email_logo_url'] ?? ''));
            if ($override !== '' && filter_var($override, FILTER_VALIDATE_URL)) {
                $logoUrl = $override;
            } elseif (! empty($settings['logo_path'])) {
                $logoUrl = url(Storage::url($settings['logo_path']));
            }

            $view->with([
                'siteName' => $settings['site_name'] ?? config('app.name', 'Philo Photobooth'),
                'siteLogoUrl' => $logoUrl,
                'footerText' => $settings['footer_text'] ?? null,
                'appUrl' => $isLocalUrl ? null : $appUrl,
                'socials' => [
                    'instagram' => $settings['instagram_url'] ?? null,
                    'facebook' => $settings['facebook_url'] ?? null,
                    'whatsapp' => $settings['whatsapp_number'] ?? null,
                ],
            ]);
        });
    }

    protected function shareSettingsToViews(): void
    {
        View::composer(['layouts.admin', 'app', 'auth.login'], function ($view) {
            try {
                $settings = Setting::getMany(['site_name', 'site_description', 'logo_path', 'favicon_path']);
            } catch (\Throwable) {
                $settings = ['site_name' => null, 'site_description' => null, 'logo_path' => null, 'favicon_path' => null];
            }

            $view->with('siteSettings', $settings);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Paginator::useBootstrapFive();

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
