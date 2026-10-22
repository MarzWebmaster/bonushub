<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The site sits behind Cloudflare, so REMOTE_ADDR is a Cloudflare edge IP (shared by many visitors). Trust
        // X-Forwarded-For ONLY when the request really comes from a Cloudflare range, so rate limits, referral clicks
        // and (later) fraud checks see the real visitor. bootstrap/cloudflare-ips.txt = https://www.cloudflare.com/ips-v4
        // + ips-v6, one CIDR per line (refreshed by the deploy script). Missing file = no proxy trusted (old behaviour).
        $cloudflareFile = __DIR__.'/cloudflare-ips.txt';
        $cloudflare = is_readable($cloudflareFile)
            ? array_values(array_filter(array_map('trim', file($cloudflareFile) ?: []), fn ($l) => $l !== ''))
            : [];
        if ($cloudflare) {
            $middleware->trustProxies(at: $cloudflare, headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR);
        }

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'merchant.approved' => \App\Http\Middleware\CheckMerchantApproved::class,
            'merchant.writable' => \App\Http\Middleware\CheckSubscriptionWritable::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
