<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'tenant_id' => $user->tenant_id,
                    'profile_photo' => $user->profile_photo,
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'roles' => $user->roles,
                ] : null,
            ],
            'tenant' => $user?->tenant ? [
                'id' => $user->tenant->id,
                'name' => $user->tenant->name,
                'slug' => $user->tenant->slug,
                'logo' => $user->tenant->logo,
                'favicon' => $user->tenant->favicon,
                'colors' => $user->tenant->colors,
                'status' => $user->tenant->status,
                'subscription_status' => $user->tenant->subscription_status,
                'settings' => $user->tenant->settings,
                'features' => $user->tenant->features,
                'addon_modules' => $user->tenant->addon_modules,
            ] : null,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
        ];
    }
}
