<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            app()->instance('current_tenant', $tenant);
            $request->merge(['tenant' => $tenant]);
        }

        return $next($request);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        // Try to resolve from authenticated user
        if ($user = $request->user()) {
            if ($user->tenant_id) {
                return Tenant::findCached($user->tenant_id);
            }
        }

        // Try subdomain resolution
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST);

        if ($host !== $appDomain && str_ends_with($host, '.' . $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);

            return Cache::remember("tenant.subdomain.{$subdomain}", 600, function () use ($subdomain) {
                return Tenant::where('subdomain', $subdomain)
                    ->where('status', '!=', 'inactive')
                    ->first();
            });
        }

        // Try custom domain
        return Cache::remember("tenant.domain.{$host}", 600, function () use ($host) {
            return Tenant::where('domain', $host)
                ->where('status', '!=', 'inactive')
                ->first();
        });
    }
}
