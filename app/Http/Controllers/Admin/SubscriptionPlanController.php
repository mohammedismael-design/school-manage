<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPlanController extends Controller
{
    public function index(): Response
    {
        $plans = SubscriptionPlan::with(['modules' => fn ($q) => $q->wherePivot('is_included', true)])
            ->orderBy('sort_order')
            ->withCount('tenants')
            ->get();

        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans,
        ]);
    }

    public function create(): Response
    {
        $modules = Module::active()->orderBy('category')->orderBy('sort_order')->get();

        return Inertia::render('Admin/Plans/Create', [
            'modules' => $modules,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:subscription_plans,code',
            'description' => 'nullable|string|max:500',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'student_limit' => 'required|integer|min:0',
            'staff_limit' => 'required|integer|min:0',
            'storage_limit_mb' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        $moduleIds = $validated['module_ids'] ?? [];
        unset($validated['module_ids']);

        $plan = SubscriptionPlan::create($validated);

        if ($moduleIds) {
            $plan->modules()->sync(
                collect($moduleIds)->mapWithKeys(fn ($id) => [$id => ['is_included' => true]])
            );
        }

        AuditLog::record('plan.created', $plan, [], $plan->toArray());

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan '{$plan->name}' created successfully.");
    }

    public function edit(SubscriptionPlan $plan): Response
    {
        $plan->load(['modules' => fn ($q) => $q->wherePivot('is_included', true)]);
        $modules = Module::active()->orderBy('category')->orderBy('sort_order')->get();

        return Inertia::render('Admin/Plans/Edit', [
            'plan' => $plan,
            'modules' => $modules,
            'selectedModuleIds' => $plan->modules->pluck('id'),
        ]);
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:50|unique:subscription_plans,code,{$plan->id}",
            'description' => 'nullable|string|max:500',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'student_limit' => 'required|integer|min:0',
            'staff_limit' => 'required|integer|min:0',
            'storage_limit_mb' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        $moduleIds = $validated['module_ids'] ?? [];
        unset($validated['module_ids']);

        $old = $plan->toArray();
        $plan->update($validated);

        if ($moduleIds !== null) {
            $plan->modules()->sync(
                collect($moduleIds)->mapWithKeys(fn ($id) => [$id => ['is_included' => true]])
            );
        }

        AuditLog::record('plan.updated', $plan, $old, $plan->fresh()->toArray());

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan '{$plan->name}' updated successfully.");
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->tenants()->exists()) {
            return back()->with('error', 'Cannot delete a plan with active subscribers. Deactivate it instead.');
        }

        $planName = $plan->name;
        $plan->delete();

        AuditLog::record('plan.deleted', null, ['name' => $planName], []);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan '{$planName}' deleted successfully.");
    }

    public function duplicate(SubscriptionPlan $plan): RedirectResponse
    {
        $newPlan = $plan->replicate();
        $newPlan->name = $plan->name . ' (Copy)';
        $newPlan->code = $plan->code . '_copy_' . time();
        $newPlan->is_active = false;
        $newPlan->save();

        $newPlan->modules()->sync(
            $plan->modules->mapWithKeys(fn ($m) => [$m->id => ['is_included' => $m->pivot->is_included]])
        );

        return redirect()->route('admin.plans.edit', $newPlan)
            ->with('success', "Plan duplicated. Edit it before activating.");
    }
}
