<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // Core
            ['key' => 'core', 'name' => 'Core System', 'category' => 'core', 'is_core' => true, 'sort_order' => 1, 'icon' => 'HomeIcon', 'description' => 'Core system functionality required by all schools.'],
            ['key' => 'academics', 'name' => 'Academics', 'category' => 'academics', 'is_core' => false, 'sort_order' => 2, 'icon' => 'AcademicCapIcon', 'description' => 'Classes, subjects, timetables, and CBC/8-4-4 curriculum management.'],
            ['key' => 'attendance', 'name' => 'Attendance', 'category' => 'academics', 'is_core' => false, 'sort_order' => 3, 'icon' => 'CheckCircleIcon', 'description' => 'Student and staff attendance tracking.'],
            ['key' => 'exams', 'name' => 'Exams & Assessments', 'category' => 'academics', 'is_core' => false, 'sort_order' => 4, 'icon' => 'DocumentTextIcon', 'description' => 'Exam management, mark entry, and report generation.'],

            // Finance
            ['key' => 'finance', 'name' => 'Finance', 'category' => 'finance', 'is_core' => false, 'sort_order' => 10, 'icon' => 'CurrencyDollarIcon', 'description' => 'Fee management, invoicing, and financial reporting.'],
            ['key' => 'payroll', 'name' => 'Payroll', 'category' => 'finance', 'is_core' => false, 'sort_order' => 11, 'icon' => 'BanknotesIcon', 'description' => 'Staff payroll processing and payslip generation.'],

            // Communication
            ['key' => 'communication', 'name' => 'Communication', 'category' => 'communication', 'is_core' => false, 'sort_order' => 20, 'icon' => 'ChatBubbleLeftRightIcon', 'description' => 'SMS, email, and in-app notifications.'],
            ['key' => 'noticeboard', 'name' => 'Noticeboard', 'category' => 'communication', 'is_core' => false, 'sort_order' => 21, 'icon' => 'MegaphoneIcon', 'description' => 'School announcements and noticeboard.'],

            // Operations
            ['key' => 'transport', 'name' => 'Transport', 'category' => 'operations', 'is_core' => false, 'sort_order' => 30, 'icon' => 'TruckIcon', 'description' => 'School bus routes, tracking, and management.'],
            ['key' => 'store', 'name' => 'School Store', 'category' => 'operations', 'is_core' => false, 'sort_order' => 31, 'icon' => 'ShoppingBagIcon', 'description' => 'School shop inventory and sales management.'],
            ['key' => 'hostel', 'name' => 'Hostel', 'category' => 'operations', 'is_core' => false, 'sort_order' => 32, 'icon' => 'BuildingOfficeIcon', 'description' => 'Boarding/hostel room allocation and management.'],
            ['key' => 'library', 'name' => 'Library', 'category' => 'operations', 'is_core' => false, 'sort_order' => 33, 'icon' => 'BookOpenIcon', 'description' => 'Library book management and borrowing system.'],
            ['key' => 'lab', 'name' => 'Laboratory', 'category' => 'operations', 'is_core' => false, 'sort_order' => 34, 'icon' => 'BeakerIcon', 'description' => 'Science lab management and equipment tracking.'],

            // Student Services
            ['key' => 'student_portal', 'name' => 'Student Portal', 'category' => 'student_services', 'is_core' => false, 'sort_order' => 40, 'icon' => 'UserCircleIcon', 'description' => 'Self-service portal for students.'],
            ['key' => 'parent_portal', 'name' => 'Parent Portal', 'category' => 'student_services', 'is_core' => false, 'sort_order' => 41, 'icon' => 'UsersIcon', 'description' => 'Parent dashboard to monitor child progress.'],
            ['key' => 'alumni', 'name' => 'Alumni', 'category' => 'student_services', 'is_core' => false, 'sort_order' => 42, 'icon' => 'UserGroupIcon', 'description' => 'Alumni network and records management.'],
            ['key' => 'health', 'name' => 'Health & Medical', 'category' => 'student_services', 'is_core' => false, 'sort_order' => 43, 'icon' => 'HeartIcon', 'description' => 'Student medical records and clinic management.'],

            // Support
            ['key' => 'nemis', 'name' => 'NEMIS Integration', 'category' => 'support', 'is_core' => false, 'sort_order' => 50, 'icon' => 'LinkIcon', 'description' => 'National Education Management Information System integration.'],
            ['key' => 'cbc', 'name' => 'CBC Curriculum', 'category' => 'support', 'is_core' => false, 'sort_order' => 51, 'icon' => 'ClipboardDocumentListIcon', 'description' => 'Competency Based Curriculum tools and assessments.'],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['key' => $module['key']],
                $module
            );
        }
    }
}
