<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function index(Request $request): Response
    {
        $tenants = Tenant::withTrashed()
            ->with('plan')
            ->withCount('users')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Schools/Index', [
            'tenants' => $tenants,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Schools/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'required|string|max:100|unique:tenants,slug',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'subdomain' => 'nullable|string|max:100|unique:tenants,subdomain',
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'billing_cycle' => 'in:monthly,yearly',
            'admin_name' => 'required|string|max:200',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'subdomain' => $validated['subdomain'] ?? null,
            'plan_id' => $validated['plan_id'] ?? null,
            'billing_cycle' => $validated['billing_cycle'] ?? 'monthly',
            'status' => 'trial',
            'subscription_status' => 'trial',
        ]);

        // Create admin user
        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'user_type' => 'admin',
            'is_active' => true,
        ]);

        AuditLog::record('school.created', $tenant, [], $tenant->toArray());

        return redirect()->route('admin.schools.show', $tenant)
            ->with('success', "School '{$tenant->name}' created successfully.");
    }

    public function show(Tenant $tenant): Response
    {
        $tenant->load(['plan', 'users']);
        $usage = $this->subscriptionService->getUsageStats($tenant);

        return Inertia::render('Admin/Schools/Show', [
            'tenant' => $tenant,
            'usage' => $usage,
            'limits' => [
                'students' => $this->subscriptionService->checkSubscriptionLimits($tenant, 'students'),
                'staff' => $this->subscriptionService->checkSubscriptionLimits($tenant, 'staff'),
                'storage' => $this->subscriptionService->checkSubscriptionLimits($tenant, 'storage'),
            ],
        ]);
    }

    public function edit(Tenant $tenant): Response
    {
        $tenant->load('plan');

        return Inertia::render('Admin/Schools/Edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'email' => "required|email|unique:tenants,email,{$tenant->id}",
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'subdomain' => "nullable|string|max:100|unique:tenants,subdomain,{$tenant->id}",
            'domain' => "nullable|string|max:255|unique:tenants,domain,{$tenant->id}",
            'settings' => 'nullable|array',
        ]);

        $old = $tenant->toArray();
        $tenant->update($validated);
        $tenant->save(); // Force save to ensure persistence

        // Clear cache after update
        Cache::forget("tenant.{$tenant->id}");
        Cache::forget("tenant.slug.{$tenant->slug}");

        AuditLog::record('school.updated', $tenant, $old, $tenant->fresh()->toArray());

        return back()->with('success', 'School updated successfully.');
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        if ($tenant->isSuspended()) {
            return back()->with('error', 'School is already suspended.');
        }

        $tenant->suspend();

        // Clear all tenant-related caches
        Cache::forget("tenant.{$tenant->id}");
        Cache::forget("tenant.slug.{$tenant->slug}");
        Cache::tags(["tenant_{$tenant->id}"])->flush();

        AuditLog::record('school.suspended', $tenant);

        return back()->with('success', "School '{$tenant->name}' has been suspended.");
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $tenant->activate();

        Cache::forget("tenant.{$tenant->id}");
        Cache::forget("tenant.slug.{$tenant->slug}");

        AuditLog::record('school.activated', $tenant);

        return back()->with('success', "School '{$tenant->name}' has been activated.");
    }

    public function impersonate(Tenant $tenant): RedirectResponse
    {
        $admin = $tenant->users()
            ->where('user_type', 'admin')
            ->where('is_active', true)
            ->firstOrFail();

        AuditLog::record('school.impersonated', $tenant, [], ['admin_user_id' => $admin->id]);

        auth()->login($admin);

        return redirect()->route('school.dashboard')
            ->with('info', "You are now logged in as {$tenant->name} admin.");
    }
}
