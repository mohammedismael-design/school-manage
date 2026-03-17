<?php

use App\Http\Controllers\Admin\ModuleManagementController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\School\DashboardConfigController;
use App\Http\Controllers\School\NotificationController;
use App\Http\Controllers\School\SettingsController;
use App\Http\Controllers\School\StaffManagementController;
use App\Http\Controllers\School\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    Route::get('/forgot-password', fn () => Inertia::render('Auth/ForgotPassword'))->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');

    // Module Management
    Route::prefix('module-management')->name('modules.')->group(function () {
        Route::get('/', [ModuleManagementController::class, 'index'])->name('index');
        Route::get('/plan-modules', [ModuleManagementController::class, 'planModules'])->name('plan-modules');
        Route::post('/{moduleKey}/toggle-global', [ModuleManagementController::class, 'toggleGlobal'])->name('toggle-global');
        Route::get('/{moduleKey}/tenant-overrides', [ModuleManagementController::class, 'tenantOverrides'])->name('tenant-overrides');
        Route::post('/{tenantId}/modules/{moduleKey}/override', [ModuleManagementController::class, 'setTenantOverride'])->name('set-override');
    });

    // Subscription Plans
    Route::resource('plans', SubscriptionPlanController::class)->names([
        'index' => 'plans.index',
        'create' => 'plans.create',
        'store' => 'plans.store',
        'edit' => 'plans.edit',
        'update' => 'plans.update',
        'destroy' => 'plans.destroy',
    ]);
    Route::post('/plans/{plan}/duplicate', [SubscriptionPlanController::class, 'duplicate'])->name('plans.duplicate');

    // Schools (Tenants)
    Route::resource('schools', TenantController::class)->names([
        'index' => 'schools.index',
        'create' => 'schools.create',
        'store' => 'schools.store',
        'show' => 'schools.show',
        'edit' => 'schools.edit',
        'update' => 'schools.update',
    ]);
    Route::post('/schools/{tenant}/suspend', [TenantController::class, 'suspend'])->name('schools.suspend');
    Route::post('/schools/{tenant}/activate', [TenantController::class, 'activate'])->name('schools.activate');
    Route::post('/schools/{tenant}/impersonate', [TenantController::class, 'impersonate'])->name('schools.impersonate');
});

/*
|--------------------------------------------------------------------------
| School (Tenant) Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('school')->name('school.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('School/Dashboard'))->name('dashboard');

    // Staff Management
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffManagementController::class, 'index'])->name('index');
        Route::get('/create', [StaffManagementController::class, 'create'])->name('create');
        Route::post('/', [StaffManagementController::class, 'store'])
            ->middleware('subscription.limits:staff')
            ->name('store');
        Route::get('/{user}/edit', [StaffManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [StaffManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [StaffManagementController::class, 'destroy'])->name('destroy');
        Route::get('/{user}/permissions', [StaffManagementController::class, 'getPermissions'])->name('permissions');
        Route::post('/{user}/permissions/attach', [StaffManagementController::class, 'attachPermission'])->name('permissions.attach');
        Route::delete('/{user}/permissions/detach', [StaffManagementController::class, 'detachPermission'])->name('permissions.detach');
        Route::post('/{user}/roles/assign', [StaffManagementController::class, 'assignRole'])->name('roles.assign');
        Route::delete('/{user}/roles/remove', [StaffManagementController::class, 'removeRole'])->name('roles.remove');
    });

    // Subscription & Billing (merged)
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/preview-upgrade', [SubscriptionController::class, 'previewUpgrade'])->name('preview-upgrade');
        Route::post('/process-payment', [SubscriptionController::class, 'processPayment'])->name('process-payment');
        Route::get('/invoices', [SubscriptionController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{payment}/download', [SubscriptionController::class, 'downloadInvoice'])->name('invoices.download');
    });

    // Dashboard Configuration
    Route::prefix('dashboard-config')->name('dashboard-config.')->group(function () {
        Route::get('/', [DashboardConfigController::class, 'index'])->name('index');
        Route::put('/{userType}', [DashboardConfigController::class, 'update'])->name('update');
        Route::post('/override/{userId}', [DashboardConfigController::class, 'overrideForUser'])->name('override');
        Route::delete('/reset/{userType}', [DashboardConfigController::class, 'resetToDefault'])->name('reset');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/logo', [SettingsController::class, 'uploadLogo'])->name('logo');
        Route::post('/favicon', [SettingsController::class, 'uploadFavicon'])->name('favicon');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    });
});
