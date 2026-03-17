<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * Resource type should be passed via route parameter or query string: ?resource=students
     */
    public function handle(Request $request, Closure $next, string $resourceType = ''): Response
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            return $next($request);
        }

        if (!$resourceType) {
            $resourceType = $request->query('resource', '');
        }

        if (!$resourceType) {
            return $next($request);
        }

        $limits = $this->subscriptionService->checkSubscriptionLimits($tenant, $resourceType);

        if (!$limits['can_add']) {
            $messages = [
                'students' => "You have reached your student limit ({$limits['limit']}). Please upgrade your plan to add more students.",
                'staff' => "You have reached your staff limit ({$limits['limit']}). Please upgrade your plan to add more staff.",
                'storage' => "You have reached your storage limit. Please upgrade your plan for more storage.",
            ];

            $message = $messages[$resourceType] ?? 'You have reached your subscription limit.';

            if ($request->expectsJson() || $request->inertia()) {
                return response()->json([
                    'message' => $message,
                    'resource' => $resourceType,
                    'current' => $limits['current'],
                    'limit' => $limits['limit'],
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, $message);
        }

        return $next($request);
    }
}
