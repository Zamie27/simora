<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureUserIsVerified;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('training:generate-recurring-logs')
            ->daily()
            ->at('00:01')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/recurring-training.log'));
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'verified-user' => EnsureUserIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, Throwable $exception, $request) {
            if (! in_array($response->getStatusCode(), [500, 503, 404, 403, 419, 401])) {
                return $response;
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return $response;
            }

            return Inertia\Inertia::render('Error', [
                'status' => $response->getStatusCode(),
            ])
                ->toResponse($request)
                ->setStatusCode($response->getStatusCode());
        });
    })->create();
