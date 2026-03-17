<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ModuleSeeder::class,
            SubscriptionPlanSeeder::class,
            DefaultRolesSeeder::class,
        ]);

        // Create Super Admin
        User::updateOrCreate(
            ['email' => 'superadmin@feeyangu.com'],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@feeyangu.com',
                'password' => Hash::make('password'),
                'user_type' => 'super_admin',
                'is_active' => true,
            ]
        );

        // Create demo school
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'demo-school'],
            [
                'name' => 'Demo School',
                'slug' => 'demo-school',
                'email' => 'admin@demoschool.ac.ke',
                'phone' => '+254700000001',
                'address' => 'Nairobi, Kenya',
                'status' => 'active',
                'subscription_status' => 'active',
                'subdomain' => 'demo',
                'max_students' => 200,
                'max_staff' => 20,
                'max_storage_mb' => 2048,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@demoschool.ac.ke'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Admin',
                'email' => 'admin@demoschool.ac.ke',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
