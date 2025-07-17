<?php

namespace App\Providers;

use App\Services\SmsServiceInterface;
use App\Services\TextSmsKenyaService;
use App\Services\TwilioService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('sms.twilio', function ($app) {
            $config = $app['config']['services.twilio'];
            
            return new TwilioService(
                $config['sid'] ?? '',
                $config['auth_token'] ?? '',
                $config['from'] ?? null
            );
        });

        $this->app->singleton('sms.textsms', function ($app) {
            $config = $app['config']['services.textsms'];
            
            return new TextSmsKenyaService(
                $config['api_key'] ?? '',
                $config['sender_id'] ?? null
            );
        });

        $this->app->bind(SmsServiceInterface::class, function ($app) {
            $defaultProvider = config('sms.default');
            return $app->get('sms.' . $defaultProvider);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
