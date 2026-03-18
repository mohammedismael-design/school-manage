<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    /**
     * Enforce subscription limits for the current tenant.
     *
     * Usage: Route::middleware(['subscription.limits:students'])
     */
    public function handle(Request $request, Closure $next, string $resource = 'students'): Response
    {
        $tenant = app('current_tenant') ?? $request->user()?->tenant;

        if ($tenant) {
            $reached = match ($resource) {
                'students' => $this->subscriptionService->hasReachedStudentLimit($tenant),
                'staff' => $this->subscriptionService->hasReachedStaffLimit($tenant),
                default => false,
            };

            if ($reached) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => "You have reached the {$resource} limit for your subscription plan.",
                    ], Response::HTTP_FORBIDDEN);
                }

                return back()->withErrors([
                    'limit' => "You have reached the {$resource} limit for your subscription plan.",
                ]);
            }
        }

        return $next($request);
    }
}
