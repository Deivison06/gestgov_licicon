<?php

namespace App\Providers;

use App\Services\OpenAiService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenAiService::class, function ($app) {
            $cfg = $app['config']->get('services.openai', []);

            return new OpenAiService(
                apiKey:      $cfg['api_key']     ?? null,
                baseUrl:     $cfg['base_url']    ?? 'https://api.openai.com/v1',
                model:       $cfg['model']       ?? 'gpt-4o-mini',
                timeout:     (int)   ($cfg['timeout']     ?? 30),
                maxTokens:   (int)   ($cfg['max_tokens']  ?? 1200),
                temperature: (float) ($cfg['temperature'] ?? 0.3),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate-limit para chamadas à OpenAI: 10 requisições por minuto por usuário.
        // No ambiente local cai para 3/min para evitar consumo acidental em testes.
        RateLimiter::for('ia-write', function ($request) {
            $perMinute = app()->environment('local') ? 3 : 10;
            return Limit::perMinute($perMinute)
                ->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
