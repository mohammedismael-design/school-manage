<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\SchoolRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DefaultRolesSeeder extends Seeder
{
    /**
     * Default system roles for all schools.
     * Schools cannot create/edit these — Super Admin only.
     */
    public function run(): void
    {
        $defaultRoles = [
            'Principal',
            'Deputy Principal',
            'Head of Department',
            'Class Teacher',
            'Teacher',
            'Finance Officer',
            'Accountant',
            'Auditor',
            'Librarian',
            'Store Keeper',
            'Transport Manager',
            'Driver',
            'Nurse',
            'Secretary',
            'Exam Officer',
            'Admission Officer',
            'Discipline Master',
            'Games Master',
            'Lab Technician',
            'ICT Officer',
            'Boarding Master',
            'Alumni Coordinator',
        ];

        // Create permissions using the module:action format
        $permissions = [
            // Academics
            'academics:view_classes', 'academics:manage_classes',
            'academics:view_subjects', 'academics:manage_subjects',
            'academics:view_timetable', 'academics:manage_timetable',
            // Attendance
            'attendance:view', 'attendance:mark', 'attendance:manage',
            // Finance
            'finance:view', 'finance:manage_fees', 'finance:view_reports',
            'finance:manage_payments', 'finance:export',
            // Staff
            'staff:view', 'staff:create', 'staff:edit', 'staff:delete',
            // Students
            'students:view', 'students:create', 'students:edit', 'students:delete',
            // Reports
            'reports:view', 'reports:generate', 'reports:export',
            // Settings
            'settings:view', 'settings:manage',
            // Library
            'library:view', 'library:manage',
            // Transport
            'transport:view', 'transport:manage',
            // Store
            'store:view', 'store:manage',
            // Exams
            'exams:view', 'exams:manage', 'exams:mark_entry',
            // Communication
            'communication:view', 'communication:send',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // For the demo tenant, create system roles
        $tenant = Tenant::where('slug', 'demo-school')->first();

        if ($tenant) {
            foreach ($defaultRoles as $roleName) {
                SchoolRole::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $roleName],
                    ['is_system' => true]
                );
            }
        }
    }
}
