<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Module;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleManagementController extends Controller
{
    public function index(): Response
    {
        $modules = Module::orderBy('category')->orderBy('sort_order')->get()
            ->groupBy('category');

        return Inertia::render('Admin/Modules/Index', [
            'modules' => $modules,
            'categories' => Module::distinct()->pluck('category'),
        ]);
    }

    public function toggleGlobal(Request $request, string $moduleKey): RedirectResponse
    {
        $module = Module::where('key', $moduleKey)->firstOrFail();

        if ($module->is_core) {
            return back()->with('error', 'Core modules cannot be disabled globally.');
        }

        $module->is_active = !$module->is_active;
        $module->save();

        AuditLog::record(
            $module->is_active ? 'module.enabled_globally' : 'module.disabled_globally',
            $module
        );

        return back()->with('success', "Module '{$module->name}' has been " . ($module->is_active ? 'enabled' : 'disabled') . ' globally.');
    }

    public function tenantOverrides(string $moduleKey): Response
    {
        $module = Module::where('key', $moduleKey)->firstOrFail();

        $tenants = Tenant::with(['modules' => fn ($q) => $q->where('key', $moduleKey)])
            ->get()
            ->map(function (Tenant $tenant) use ($module) {
                $override = $tenant->modules->first();

                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'status' => $tenant->status,
                    'override_status' => $override ? ($override->pivot->is_enabled ? 'enabled' : 'disabled') : 'inherit',
                    'override_reason' => $override?->pivot->override_reason,
                ];
            });

        return Inertia::render('Admin/Modules/TenantOverrides', [
            'module' => $module,
            'tenants' => $tenants,
        ]);
    }

    public function setTenantOverride(Request $request, int $tenantId, string $moduleKey): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:enabled,disabled,inherit',
            'reason' => 'nullable|string|max:500',
        ]);

        $tenant = Tenant::findOrFail($tenantId);
        $module = Module::where('key', $moduleKey)->firstOrFail();

        if ($request->status === 'inherit') {
            $tenant->modules()->detach($module->id);
        } else {
            $tenant->modules()->syncWithoutDetaching([
                $module->id => [
                    'is_enabled' => $request->status === 'enabled',
                    'override_reason' => $request->reason,
                    'overridden_by' => auth()->id(),
                ],
            ]);
        }

        AuditLog::record(
            'module.tenant_override_set',
            $module,
            [],
            ['tenant_id' => $tenantId, 'status' => $request->status]
        );

        return back()->with('success', 'Module override updated successfully.');
    }

    public function planModules(): Response
    {
        $modules = Module::active()->with('plans')->orderBy('sort_order')->get();

        return Inertia::render('Admin/Modules/PlanModules', [
            'modules' => $modules,
        ]);
    }
}
