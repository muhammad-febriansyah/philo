<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
    }

    protected function shareSettingsToViews(): void
    {
        View::composer(['layouts.admin', 'app', 'auth.login'], function ($view) {
            try {
                $settings = Setting::getMany(['site_name', 'logo_path', 'favicon_path']);
            } catch (\Throwable) {
                $settings = ['site_name' => null, 'logo_path' => null, 'favicon_path' => null];
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
