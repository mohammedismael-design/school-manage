<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\DashboardConfig;
use App\Models\Module;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardConfigController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function index(): Response
    {
        $tenant = auth()->user()->tenant;
        $userTypes = ['admin', 'principal', 'teacher', 'staff', 'parent', 'student'];

        $configs = DashboardConfig::forTenant($tenant->id)
            ->get()
            ->groupBy('user_type');

        $enabledModules = $tenant->enabledModules()->get();

        return Inertia::render('School/DashboardConfig/Index', [
            'configs' => $configs,
            'userTypes' => $userTypes,
            'enabledModules' => $enabledModules,
        ]);
    }

    public function update(Request $request, string $userType): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'configs' => 'required|array',
            'configs.*.config_key' => 'required|string|max:100',
            'configs.*.config_value' => 'nullable',
        ]);

        foreach ($validated['configs'] as $config) {
            DashboardConfig::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_type' => $userType,
                    'config_key' => $config['config_key'],
                ],
                [
                    'config_value' => $config['config_value'],
                    'created_by' => auth()->id(),
                ]
            );
        }

        return back()->with('success', "Dashboard configuration for {$userType} updated.");
    }

    public function overrideForUser(Request $request, int $userId): RedirectResponse
    {
        $validated = $request->validate([
            'config_key' => 'required|string|max:100',
            'config_value' => 'nullable',
        ]);

        \DB::table('user_dashboard_overrides')->updateOrInsert(
            ['user_id' => $userId, 'config_key' => $validated['config_key']],
            [
                'config_value' => json_encode($validated['config_value']),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'User dashboard override saved.');
    }

    public function resetToDefault(string $userType): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        DashboardConfig::forTenant($tenant->id)
            ->forUserType($userType)
            ->delete();

        return back()->with('success', "Dashboard configuration for {$userType} reset to defaults.");
    }
}
