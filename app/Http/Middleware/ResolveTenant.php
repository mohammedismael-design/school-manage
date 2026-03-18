<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Resolve the current tenant from the request (subdomain, domain, or path).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // Try subdomain
        $host = $request->getHost();
        $appHost = config('app.url');
        $appDomain = parse_url($appHost, PHP_URL_HOST) ?? $appHost;

        if ($host !== $appDomain && str_ends_with($host, '.'.$appDomain)) {
            $subdomain = str_replace('.'.$appDomain, '', $host);
            $tenant = \App\Models\Tenant::where('subdomain', $subdomain)->first();
        }

        // Try custom domain
        if (! $tenant) {
            $tenant = \App\Models\Tenant::where('domain', $host)->first();
        }

        if ($tenant) {
            if ($tenant->isSuspended()) {
                abort(503, 'This school account has been suspended.');
            }

            app()->instance('current_tenant', $tenant);
            $request->attributes->set('current_tenant', $tenant);
        }

        return $next($request);
    }
}
