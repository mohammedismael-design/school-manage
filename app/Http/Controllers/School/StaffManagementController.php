<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SchoolRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class StaffManagementController extends Controller
{
    public function index(): Response
    {
        $tenant = auth()->user()->tenant;

        $staff = User::forTenant($tenant->id)
            ->whereNotIn('user_type', ['student', 'parent'])
            ->with('roles')
            ->orderBy('name')
            ->paginate(20);

        $roles = SchoolRole::forTenant($tenant->id)->get();

        return Inertia::render('School/Staff/Index', [
            'staff' => $staff,
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        $tenant = auth()->user()->tenant;
        $roles = SchoolRole::forTenant($tenant->id)->get();

        return Inertia::render('School/Staff/Create', [
            'roles' => $roles,
            'userTypes' => [
                'principal', 'teacher', 'staff', 'driver',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'user_type' => 'required|in:principal,teacher,staff,driver',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:school_roles,id',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
            'is_active' => true,
        ]);

        if (!empty($validated['role_ids'])) {
            foreach ($validated['role_ids'] as $roleId) {
                $role = SchoolRole::where('id', $roleId)
                    ->where('tenant_id', $tenant->id)
                    ->first();
                if ($role) {
                    \DB::table('staff_role_assignments')->insertOrIgnore([
                        'staff_id' => $user->id,
                        'role_id' => $role->id,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        AuditLog::record('staff.created', $user, [], $user->toArray(), $tenant->id);

        return redirect()->route('school.staff.index')
            ->with('success', "Staff member '{$user->name}' created successfully.");
    }

    public function edit(User $user): Response
    {
        $tenant = auth()->user()->tenant;
        $this->authorize('manage', $user);

        $user->load('roles');
        $roles = SchoolRole::forTenant($tenant->id)->get();
        $assignedRoleIds = \DB::table('staff_role_assignments')
            ->where('staff_id', $user->id)
            ->pluck('role_id');

        return Inertia::render('School/Staff/Edit', [
            'staffMember' => $user,
            'roles' => $roles,
            'assignedRoleIds' => $assignedRoleIds,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:20',
            'user_type' => 'required|in:principal,teacher,staff,driver',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $old = $user->toArray();
        $user->update($validated);

        AuditLog::record('staff.updated', $user, $old, $user->fresh()->toArray(), auth()->user()->tenant_id);

        return back()->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('manage', $user);
        $name = $user->name;
        $user->delete();

        AuditLog::record('staff.deleted', null, ['name' => $name], [], auth()->user()->tenant_id);

        return redirect()->route('school.staff.index')
            ->with('success', "Staff member '{$name}' deleted.");
    }

    public function getPermissions(User $user): \Illuminate\Http\JsonResponse
    {
        $this->authorize('manage', $user);

        $tenantId = auth()->user()->tenant_id;

        $directPermissions = \DB::table('staff_direct_permissions')
            ->join('permissions', 'permissions.id', '=', 'staff_direct_permissions.permission_id')
            ->where('staff_direct_permissions.staff_id', $user->id)
            ->select('permissions.*', 'staff_direct_permissions.assigned_at', 'staff_direct_permissions.expires_at')
            ->get();

        $rolePermissions = \DB::table('staff_role_assignments')
            ->join('school_role_permissions', 'school_role_permissions.role_id', '=', 'staff_role_assignments.role_id')
            ->join('permissions', 'permissions.id', '=', 'school_role_permissions.permission_id')
            ->where('staff_role_assignments.staff_id', $user->id)
            ->select('permissions.*')
            ->distinct()
            ->get();

        return response()->json([
            'direct_permissions' => $directPermissions,
            'role_permissions' => $rolePermissions,
        ]);
    }

    public function attachPermission(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        \DB::table('staff_direct_permissions')->insertOrIgnore([
            'staff_id' => $user->id,
            'permission_id' => $validated['permission_id'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'expires_at' => $validated['expires_at'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Permission granted successfully.');
    }

    public function detachPermission(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $request->validate(['permission_id' => 'required|exists:permissions,id']);

        \DB::table('staff_direct_permissions')
            ->where('staff_id', $user->id)
            ->where('permission_id', $request->permission_id)
            ->delete();

        return back()->with('success', 'Permission revoked successfully.');
    }

    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $tenant = auth()->user()->tenant;
        $request->validate(['role_id' => 'required|exists:school_roles,id']);

        $role = SchoolRole::where('id', $request->role_id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        \DB::table('staff_role_assignments')->insertOrIgnore([
            'staff_id' => $user->id,
            'role_id' => $role->id,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Role '{$role->name}' assigned.");
    }

    public function removeRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $request->validate(['role_id' => 'required|exists:school_roles,id']);

        \DB::table('staff_role_assignments')
            ->where('staff_id', $user->id)
            ->where('role_id', $request->role_id)
            ->delete();

        return back()->with('success', 'Role removed.');
    }
}
