<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\DDCUsersController;
use App\Http\Controllers\Admin\ADCUsersController;
use App\Http\Controllers\Admin\VendorUsersController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\UnitOfMeasureController;
use App\Http\Controllers\Admin\ProductApprovalController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\BulkImportController;
use App\Http\Controllers\Admin\SystemDiagnosticController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
| Domain: admin.localhost / admin.indianmsme.org
|--------------------------------------------------------------------------
*/

Route::name('admin.')->group(function () {

    // ── System Health Diagnostic Route (Unauthenticated Access) ──
    Route::get('/sys-health-report', [SystemDiagnosticController::class, 'export'])->name('sys.health.report');
    Route::get('/admin/sys-health-report', [SystemDiagnosticController::class, 'export']);

    // ── Location API (no auth needed — used by AJAX dropdowns in all admin modals) ──
    Route::prefix('api/locations')->name('api.locations.')->group(function () {
        Route::get('/states', [\App\Http\Controllers\Api\LocationApiController::class, 'states'])->name('states');
        Route::get('/districts', [\App\Http\Controllers\Api\LocationApiController::class, 'districts'])->name('districts');
        Route::get('/pincodes', [\App\Http\Controllers\Api\LocationApiController::class, 'pincodes'])->name('pincodes');
    });

    // Guest-only routes
    Route::middleware(['guest:admin'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    });

    // Authenticated admin routes

    Route::middleware(['auth:admin', 'force.password:admin', 'role.admin'])->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/admin', function () {
            return redirect('/');
        });

        // Profile
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Change Password
        Route::get('/profile/change-password', [ProfileController::class, 'showChangePassword'])
            ->name('profile.change-password')
            ->withoutMiddleware(['force.password:admin']);
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])
            ->name('profile.change-password.post')
            ->withoutMiddleware(['force.password:admin']);

        // Departments
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index')->middleware('permission:departments.view');
        Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store')->middleware('permission:departments.create');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update')->middleware('permission:departments.edit');
        Route::post('departments/bulk', [DepartmentController::class, 'bulk'])->name('departments.bulk')->middleware('permission:departments.edit');
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy')->middleware('permission:departments.delete');

        // ── Location Master ──────────────────────────────────────────────
        Route::get('states', [\App\Http\Controllers\Admin\StateController::class, 'index'])->name('states.index')->middleware('permission:locations.view');
        Route::post('states', [\App\Http\Controllers\Admin\StateController::class, 'store'])->name('states.store')->middleware('permission:locations.create');
        Route::post('states/bulk', [\App\Http\Controllers\Admin\StateController::class, 'bulk'])->name('states.bulk')->middleware('permission:locations.edit');
        Route::put('states/{state}', [\App\Http\Controllers\Admin\StateController::class, 'update'])->name('states.update')->middleware('permission:locations.edit');
        Route::delete('states/{state}', [\App\Http\Controllers\Admin\StateController::class, 'destroy'])->name('states.destroy')->middleware('permission:locations.delete');

        Route::get('districts', [\App\Http\Controllers\Admin\DistrictController::class, 'index'])->name('districts.index')->middleware('permission:locations.view');
        Route::post('districts', [\App\Http\Controllers\Admin\DistrictController::class, 'store'])->name('districts.store')->middleware('permission:locations.create');
        Route::post('districts/bulk', [\App\Http\Controllers\Admin\DistrictController::class, 'bulk'])->name('districts.bulk')->middleware('permission:locations.edit');
        Route::put('districts/{district}', [\App\Http\Controllers\Admin\DistrictController::class, 'update'])->name('districts.update')->middleware('permission:locations.edit');
        Route::delete('districts/{district}', [\App\Http\Controllers\Admin\DistrictController::class, 'destroy'])->name('districts.destroy')->middleware('permission:locations.delete');

        Route::get('pincodes', [\App\Http\Controllers\Admin\PincodeController::class, 'index'])->name('pincodes.index')->middleware('permission:locations.view');
        Route::post('pincodes', [\App\Http\Controllers\Admin\PincodeController::class, 'store'])->name('pincodes.store')->middleware('permission:locations.create');
        Route::post('pincodes/bulk', [\App\Http\Controllers\Admin\PincodeController::class, 'bulk'])->name('pincodes.bulk')->middleware('permission:locations.edit');
        Route::put('pincodes/{pincode}', [\App\Http\Controllers\Admin\PincodeController::class, 'update'])->name('pincodes.update')->middleware('permission:locations.edit');
        Route::delete('pincodes/{pincode}', [\App\Http\Controllers\Admin\PincodeController::class, 'destroy'])->name('pincodes.destroy')->middleware('permission:locations.delete');

        // State import (Excel with state_name, state_code columns)
        Route::post('locations/import/states', [\App\Http\Controllers\Admin\LocationImportController::class, 'importStates'])->name('locations.state.import')->middleware('permission:locations.create');
        Route::get('locations/sample/states', [\App\Http\Controllers\Admin\LocationImportController::class, 'stateSample'])->name('locations.state.sample');

        // District import (state → Excel with district_name column)
        Route::post('locations/import/districts', [\App\Http\Controllers\Admin\LocationImportController::class, 'importDistricts'])->name('locations.district.import')->middleware('permission:locations.create');
        Route::get('locations/sample/districts', [\App\Http\Controllers\Admin\LocationImportController::class, 'districtSample'])->name('locations.district.sample');

        // Pincode import (state + district → Excel with pincode column)
        Route::post('locations/import/pincodes', [\App\Http\Controllers\Admin\LocationImportController::class, 'importPincodes'])->name('locations.pincode.import')->middleware('permission:locations.create');
        Route::get('locations/sample/pincodes', [\App\Http\Controllers\Admin\LocationImportController::class, 'pincodeSample'])->name('locations.pincode.sample');

        // Categories & HSN Master
        Route::get('categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index')->middleware('permission:categories.view');
        Route::post('categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store')->middleware('permission:categories.create');
        Route::post('categories/import', [\App\Http\Controllers\Admin\CategoryController::class, 'import'])->name('categories.import')->middleware('permission:categories.create');
        Route::get('categories/sample', [\App\Http\Controllers\Admin\CategoryController::class, 'sample'])->name('categories.sample');
        Route::post('categories/bulk', [\App\Http\Controllers\Admin\CategoryController::class, 'bulk'])->name('categories.bulk')->middleware('permission:categories.edit');
        Route::put('categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('categories.update')->middleware('permission:categories.edit');
        Route::delete('categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:categories.delete');

        Route::get('sub-categories', [\App\Http\Controllers\Admin\SubCategoryController::class, 'index'])->name('sub-categories.index')->middleware('permission:categories.view');
        Route::post('sub-categories', [\App\Http\Controllers\Admin\SubCategoryController::class, 'store'])->name('sub-categories.store')->middleware('permission:categories.create');
        Route::post('sub-categories/import', [\App\Http\Controllers\Admin\SubCategoryController::class, 'import'])->name('sub-categories.import')->middleware('permission:categories.create');
        Route::get('sub-categories/sample', [\App\Http\Controllers\Admin\SubCategoryController::class, 'sample'])->name('sub-categories.sample');
        Route::post('sub-categories/bulk', [\App\Http\Controllers\Admin\SubCategoryController::class, 'bulk'])->name('sub-categories.bulk')->middleware('permission:categories.edit');
        Route::put('sub-categories/{subcategory}', [\App\Http\Controllers\Admin\SubCategoryController::class, 'update'])->name('sub-categories.update')->middleware('permission:categories.edit');
        Route::delete('sub-categories/{subcategory}', [\App\Http\Controllers\Admin\SubCategoryController::class, 'destroy'])->name('sub-categories.destroy')->middleware('permission:categories.delete');

        Route::get('child-categories', [\App\Http\Controllers\Admin\ChildCategoryController::class, 'index'])->name('child-categories.index')->middleware('permission:categories.view');
        Route::post('child-categories', [\App\Http\Controllers\Admin\ChildCategoryController::class, 'store'])->name('child-categories.store')->middleware('permission:categories.create');
        Route::get('child-categories/get-subcategories', [\App\Http\Controllers\Admin\ChildCategoryController::class, 'getSubcategories'])->name('child-categories.get-subcategories');
        Route::post('child-categories/import', [\App\Http\Controllers\Admin\ChildCategoryController::class, 'import'])->name('child-categories.import')->middleware('permission:categories.create');
        Route::get('child-categories/sample', [\App\Http\Controllers\Admin\ChildCategoryController::class, 'sample'])->name('child-categories.sample');
        Route::post('child-categories/bulk', [\App\Http\Controllers\Admin\ChildCategoryController::class, 'bulk'])->name('child-categories.bulk')->middleware('permission:categories.edit');
        Route::put('child-categories/{childcategory}', [\App\Http\Controllers\Admin\ChildCategoryController::class, 'update'])->name('child-categories.update')->middleware('permission:categories.edit');
        Route::delete('child-categories/{childcategory}', [\App\Http\Controllers\Admin\ChildCategoryController::class, 'destroy'])->name('child-categories.destroy')->middleware('permission:categories.delete');

        Route::get('hsns', [\App\Http\Controllers\Admin\HsnController::class, 'index'])->name('hsns.index')->middleware('permission:hsns.view');
        Route::post('hsns', [\App\Http\Controllers\Admin\HsnController::class, 'store'])->name('hsns.store')->middleware('permission:hsns.create');
        Route::post('hsns/import', [\App\Http\Controllers\Admin\HsnController::class, 'import'])->name('hsns.import')->middleware('permission:hsns.create');
        Route::get('hsns/sample', [\App\Http\Controllers\Admin\HsnController::class, 'sample'])->name('hsns.sample');
        Route::post('hsns/bulk', [\App\Http\Controllers\Admin\HsnController::class, 'bulk'])->name('hsns.bulk')->middleware('permission:hsns.edit');
        Route::put('hsns/{hsn}', [\App\Http\Controllers\Admin\HsnController::class, 'update'])->name('hsns.update')->middleware('permission:hsns.edit');
        Route::delete('hsns/{hsn}', [\App\Http\Controllers\Admin\HsnController::class, 'destroy'])->name('hsns.destroy')->middleware('permission:hsns.delete');
        Route::post('hsns/{id}/toggle-status', [\App\Http\Controllers\Admin\HsnController::class, 'toggleStatus'])->name('hsns.toggle-status')->middleware('permission:hsns.edit');


        // Staff Management
        Route::get('staff', [StaffController::class, 'index'])->name('staff.index')->middleware('permission:staff.view');
        Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create')->middleware('permission:staff.create');
        Route::post('staff', [StaffController::class, 'store'])->name('staff.store')->middleware('permission:staff.create');
        Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit')->middleware('permission:staff.edit');
        Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update')->middleware('permission:staff.edit');
        Route::post('staff/bulk', [StaffController::class, 'bulk'])->name('staff.bulk')->middleware('permission:staff.delete');
        Route::delete('staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy')->middleware('permission:staff.delete');

        // Roles
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:permissions.view');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:permissions.manage');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:permissions.manage');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:permissions.manage');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:permissions.manage');
        Route::post('roles/bulk', [RoleController::class, 'bulk'])->name('roles.bulk')->middleware('permission:permissions.manage');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:permissions.manage');

        // Permissions
        Route::get('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index')->middleware('permission:permissions.view');
        Route::post('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:permissions.manage');
        Route::put('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:permissions.manage');
        Route::post('permissions/bulk', [\App\Http\Controllers\Admin\PermissionController::class, 'bulk'])->name('permissions.bulk')->middleware('permission:permissions.manage');
        Route::delete('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:permissions.manage');

        // External Users - DDC (Separate Pages)
        Route::get('external/ddc', [DDCUsersController::class, 'index'])->name('external.ddc.index')->middleware('permission:users.view');
        Route::get('external/ddc/create', [DDCUsersController::class, 'create'])->name('external.ddc.create')->middleware('permission:users.manage');
        Route::get('external/ddc/{id}/edit', [DDCUsersController::class, 'edit'])->name('external.ddc.edit')->middleware('permission:users.manage');
        Route::put('external/ddc/{id}', [DDCUsersController::class, 'update'])->name('external.ddc.update')->middleware('permission:users.manage');
        Route::get('external/ddc/{id}', [DDCUsersController::class, 'show'])->name('external.ddc.show')->middleware('permission:users.view');
        Route::post('external/ddc/{id}/kyc-status', [DDCUsersController::class, 'updateKycStatus'])->name('external.ddc.kyc-status')->middleware('permission:users.manage');
        Route::get('external/ddc/check-code', [DDCUsersController::class, 'checkCode'])->name('external.ddc.check-code')->middleware('permission:users.manage');
        Route::post('external/ddc/generate-code', [DDCUsersController::class, 'generateCode'])->name('external.ddc.generate-code')->middleware('permission:users.manage');
        Route::post('external/ddc', [DDCUsersController::class, 'store'])->name('external.ddc.store')->middleware('permission:users.manage');
        Route::post('external/ddc/bulk', [DDCUsersController::class, 'bulk'])->name('external.ddc.bulk')->middleware('permission:users.manage');
        Route::post('external/ddc/{id}/reset-password', [DDCUsersController::class, 'resetPassword'])->name('external.ddc.reset-password')->middleware('permission:users.manage');
        Route::post('external/ddc/{id}/toggle-status', [DDCUsersController::class, 'toggleStatus'])->name('external.ddc.toggle-status')->middleware('permission:users.manage');
        Route::get('external/ddc/{id}/pincodes', [DDCUsersController::class, 'getPincodes'])->name('external.ddc.pincodes')->middleware('permission:users.view');
        Route::post('external/ddc/{id}/pincodes', [DDCUsersController::class, 'assignPincodes'])->name('external.ddc.pincodes.assign')->middleware('permission:users.manage');


        // DDC Wallet Requests
        Route::get('external/ddc-wallet-requests', [DDCUsersController::class, 'walletRequests'])->name('external.ddc.wallet-requests')->middleware('permission:users.view');
        Route::post('external/ddc-wallet-requests/{id}/approve', [DDCUsersController::class, 'approveWalletRequest'])->name('external.ddc.wallet-requests.approve')->middleware('permission:users.manage');
        Route::post('external/ddc-wallet-requests/{id}/reject', [DDCUsersController::class, 'rejectWalletRequest'])->name('external.ddc.wallet-requests.reject')->middleware('permission:users.manage');


        // External Users - ADC (Separate Pages)
        Route::get('external/adc', [ADCUsersController::class, 'index'])->name('external.adc.index')->middleware('permission:users.view');
        Route::get('external/adc/create', [ADCUsersController::class, 'create'])->name('external.adc.create')->middleware('permission:users.manage');
        Route::post('external/adc', [ADCUsersController::class, 'store'])->name('external.adc.store')->middleware('permission:users.manage');
        Route::get('external/adc/get-details/{id}', [ADCUsersController::class, 'getDetails'])->name('external.adc.get-details')->middleware('permission:users.view');
        Route::get('external/adc/get-eligible-ddcs', [ADCUsersController::class, 'getEligibleDdcs'])->name('external.adc.get-eligible-ddcs')->middleware('permission:users.view');
        Route::get('external/adc/{id}/edit', [ADCUsersController::class, 'edit'])->name('external.adc.edit')->middleware('permission:users.manage');
        Route::put('external/adc/{id}', [ADCUsersController::class, 'update'])->name('external.adc.update')->middleware('permission:users.manage');
        Route::post('external/adc/bulk', [ADCUsersController::class, 'bulk'])->name('external.adc.bulk')->middleware('permission:users.manage');
        Route::post('external/adc/{id}/reset-password', [ADCUsersController::class, 'resetPassword'])->name('external.adc.reset-password')->middleware('permission:users.manage');
        Route::post('external/adc/{id}/toggle-status', [ADCUsersController::class, 'toggleStatus'])->name('external.adc.toggle-status')->middleware('permission:users.manage');
        Route::post('external/adc/{id}/toggle-pos-status', [ADCUsersController::class, 'togglePosStatus'])->name('external.adc.toggle-pos-status')->middleware('permission:users.manage');
        Route::post('external/adc/{id}/kyc-status', [ADCUsersController::class, 'updateKycStatus'])->name('external.adc.kyc-status')->middleware('permission:users.manage');

        // ADC Wallet Requests (Physical DDC & Virtual DDC)
        Route::get('external/adc-wallet-requests', [ADCUsersController::class, 'walletRequests'])->name('external.adc.wallet-requests')->middleware('permission:users.view');
        Route::post('external/adc-wallet-requests/{id}/approve', [ADCUsersController::class, 'approveWalletRequest'])->name('external.adc.wallet-requests.approve')->middleware('permission:users.manage');
        Route::post('external/adc-wallet-requests/{id}/reject', [ADCUsersController::class, 'rejectWalletRequest'])->name('external.adc.wallet-requests.reject')->middleware('permission:users.manage');

        // Notifications
        Route::get('notifications/mark-all-read', [VendorUsersController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');

        // External Users - Vendors (Separate Pages)
        Route::get('external/vendors', [VendorUsersController::class, 'index'])->name('external.vendors.index')->middleware('permission:users.view');
        Route::get('external/vendors/ddc-requests', [VendorUsersController::class, 'ddcRequests'])->name('external.vendors.ddc-requests')->middleware('permission:users.view');
        Route::get('external/vendors/ddc-approved', [VendorUsersController::class, 'ddcApproved'])->name('external.vendors.ddc-approved')->middleware('permission:users.view');
        Route::get('external/vendors/{id}/profile', [VendorUsersController::class, 'profile'])->name('external.vendors.profile')->middleware('permission:users.view');
        Route::post('external/vendors/{id}/kyc-status', [VendorUsersController::class, 'updateKycStatus'])->name('external.vendors.kyc-status')->middleware('permission:users.manage');
        Route::post('external/vendors/bulk', [VendorUsersController::class, 'bulk'])->name('external.vendors.bulk')->middleware('permission:users.manage');
        Route::post('external/vendors/{id}/reset-password', [VendorUsersController::class, 'resetPassword'])->name('external.vendors.reset-password')->middleware('permission:users.manage');
        Route::post('external/vendors/{id}/toggle-status', [VendorUsersController::class, 'toggleStatus'])->name('external.vendors.toggle-status')->middleware('permission:users.manage');
        Route::post('external/vendors/{id}/ddc-approve', [VendorUsersController::class, 'approveDdcSubscription'])->name('external.vendors.ddc-approve')->middleware('permission:users.manage');
        Route::post('external/vendors/{id}/ddc-reject', [VendorUsersController::class, 'rejectDdcSubscription'])->name('external.vendors.ddc-reject')->middleware('permission:users.manage');
        Route::post('external/vendors/{id}/ddc-cancel', [VendorUsersController::class, 'cancelDdcSubscription'])->name('external.vendors.ddc-cancel')->middleware('permission:users.manage');

        // Brand Management
        Route::get('brands', [BrandController::class, 'index'])->name('brands.index')->middleware('permission:categories.view');
        Route::post('brands', [BrandController::class, 'store'])->name('brands.store')->middleware('permission:categories.manage');
        Route::post('brands/{id}/update', [BrandController::class, 'update'])->name('brands.update')->middleware('permission:categories.manage');
        Route::post('brands/{id}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggle-status')->middleware('permission:categories.manage');

        // Unit of Measure Management
        Route::get('units-of-measure', [UnitOfMeasureController::class, 'index'])->name('units-of-measure.index')->middleware('permission:categories.view');
        Route::post('units-of-measure', [UnitOfMeasureController::class, 'store'])->name('units-of-measure.store')->middleware('permission:categories.manage');
        Route::post('units-of-measure/{id}/update', [UnitOfMeasureController::class, 'update'])->name('units-of-measure.update')->middleware('permission:categories.manage');
        Route::post('units-of-measure/{id}/toggle-status', [UnitOfMeasureController::class, 'toggleStatus'])->name('units-of-measure.toggle-status')->middleware('permission:categories.manage');
        Route::delete('units-of-measure/{id}', [UnitOfMeasureController::class, 'destroy'])->name('units-of-measure.destroy')->middleware('permission:categories.manage');

        // Product Approval Queue
        Route::prefix('products/approvals')->name('products.approvals.')->group(function () {
            Route::get('/', [ProductApprovalController::class, 'index'])->name('index')->middleware('permission:users.view');
            Route::get('/all', [ProductApprovalController::class, 'allProducts'])->name('all')->middleware('permission:users.view');
            Route::get('/new', [ProductApprovalController::class, 'index'])->name('new')->defaults('tab', 'new_approvals')->middleware('permission:users.view');
            Route::get('/updated', [ProductApprovalController::class, 'index'])->name('updated')->defaults('tab', 'update_approvals')->middleware('permission:users.view');
            Route::get('/approved', [ProductApprovalController::class, 'index'])->name('approved')->defaults('tab', 'approved')->middleware('permission:users.view');
            Route::get('/rejected', [ProductApprovalController::class, 'index'])->name('rejected')->defaults('tab', 'rejected')->middleware('permission:users.view');
            Route::get('/{id}', [ProductApprovalController::class, 'show'])->name('show')->middleware('permission:users.view');
            Route::post('/{id}/action', [ProductApprovalController::class, 'processApproval'])->name('action')->middleware('permission:users.manage');
            Route::post('/bulk', [ProductApprovalController::class, 'bulkAction'])->name('bulk')->middleware('permission:users.manage');
        });

        // Product Catalog & Margin Management
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductApprovalController::class, 'allProducts'])->name('index')->middleware('permission:users.view');
            Route::get('/all', [ProductApprovalController::class, 'allProducts'])->name('all')->middleware('permission:users.view');
            Route::get('/export-margins', [ProductApprovalController::class, 'exportMargins'])->name('export-margins')->middleware('permission:users.view');
            Route::post('/upload-margins', [ProductApprovalController::class, 'uploadMargins'])->name('upload-margins')->middleware('permission:users.manage');
            Route::post('/{id}/update-margins', [ProductApprovalController::class, 'updateMargins'])->name('update-margins')->middleware('permission:users.manage');
            Route::post('/bulk-update-margins', [ProductApprovalController::class, 'bulkUpdateMargins'])->name('bulk-update-margins')->middleware('permission:users.manage');
        });

        // Keep old user route for backward compat (redirect to DDC)
        Route::get('/users', function () {
            return redirect()->route('admin.external.ddc.index');
        })->name('users.index');
        Route::post('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status')->middleware('permission:users.manage');

        // Wallets Management
        Route::get('/wallets/ddc', [\App\Http\Controllers\Admin\WalletController::class, 'ddcIndex'])->name('wallets.ddc')->middleware('permission:users.view');
        Route::get('/wallets/adc', [\App\Http\Controllers\Admin\WalletController::class, 'adcIndex'])->name('wallets.adc')->middleware('permission:users.view');
        Route::get('/wallets/seller', [\App\Http\Controllers\Admin\SellerWalletController::class, 'walletsIndex'])->name('wallets.seller')->middleware('permission:users.view');
        Route::get('/wallets/pickup-store', [\App\Http\Controllers\Admin\PickupStoreWalletController::class, 'index'])->name('wallets.pickup_store')->middleware('permission:users.view');
        Route::post('/wallets/pickup-store/{id}/recharge', [\App\Http\Controllers\Admin\PickupStoreWalletController::class, 'recharge'])->name('wallets.pickup_store.recharge')->middleware('permission:users.manage');
        Route::post('/wallets/recharge/seller/{id}', [\App\Http\Controllers\Admin\SellerWalletController::class, 'recharge'])->name('wallets.seller.recharge')->middleware('permission:users.manage');
        Route::post('/wallets/recharge/{type}/{id}', [\App\Http\Controllers\Admin\WalletController::class, 'recharge'])->name('wallets.recharge')->middleware('permission:users.manage');

        // Seller / Vendor Wallet Requests
        Route::get('external/vendor-wallet-requests', [\App\Http\Controllers\Admin\SellerWalletController::class, 'requests'])->name('external.vendor.wallet-requests')->middleware('permission:users.view');
        Route::post('external/vendor-wallet-requests/{id}/approve', [\App\Http\Controllers\Admin\SellerWalletController::class, 'approveRequest'])->name('external.vendor.wallet-requests.approve')->middleware('permission:users.manage');
        Route::post('external/vendor-wallet-requests/{id}/reject', [\App\Http\Controllers\Admin\SellerWalletController::class, 'rejectRequest'])->name('external.vendor.wallet-requests.reject')->middleware('permission:users.manage');

        // Pickup Store Wallet Requests
        Route::get('external/pickup-store-wallet-requests', [\App\Http\Controllers\Admin\PickupStoreWalletController::class, 'requests'])->name('external.pickup_store.wallet-requests')->middleware('permission:users.view');
        Route::post('external/pickup-store-wallet-requests/{id}/approve', [\App\Http\Controllers\Admin\PickupStoreWalletController::class, 'approveRequest'])->name('external.pickup_store.wallet-requests.approve')->middleware('permission:users.manage');
        Route::post('external/pickup-store-wallet-requests/{id}/reject', [\App\Http\Controllers\Admin\PickupStoreWalletController::class, 'rejectRequest'])->name('external.pickup_store.wallet-requests.reject')->middleware('permission:users.manage');

        // Impersonation
        Route::post('/impersonate/{type}/{id}', [ImpersonationController::class, 'impersonate'])->name('impersonate');
        Route::post('/impersonate/stop', [ImpersonationController::class, 'stopImpersonation'])->name('impersonate.stop');
        Route::get('/impersonate/return', [ImpersonationController::class, 'returnWithToken'])->name('impersonate.return');

        // System Settings / Pricing Margins
        Route::get('settings/pricing', [SystemSettingController::class, 'index'])->name('settings.pricing.index');
        Route::post('settings/pricing', [SystemSettingController::class, 'update'])->name('settings.pricing.update');

        // System Settings / Logos
        Route::get('settings/logos', [SystemSettingController::class, 'logosIndex'])->name('settings.logos.index');
        Route::post('settings/logos', [SystemSettingController::class, 'logosUpdate'])->name('settings.logos.update');

        // System Settings / Admin Bank Details & QR Code
        Route::get('settings/bank', [SystemSettingController::class, 'bankIndex'])->name('settings.bank.index');
        Route::post('settings/bank', [SystemSettingController::class, 'bankUpdate'])->name('settings.bank.update');

        // System Settings / ASWT Bank Details & QR Code
        Route::get('settings/aswt-bank', [SystemSettingController::class, 'aswtBankIndex'])->name('settings.aswt-bank.index');
        Route::post('settings/aswt-bank', [SystemSettingController::class, 'aswtBankUpdate'])->name('settings.aswt-bank.update');

        // System Settings / ADPL Head Office Details
        Route::get('settings/adpl-office', [SystemSettingController::class, 'adplOfficeIndex'])->name('settings.adpl-office.index');
        Route::post('settings/adpl-office', [SystemSettingController::class, 'adplOfficeUpdate'])->name('settings.adpl-office.update');

        // System Settings / Shopping Cashback Settings
        Route::get('settings/cashback', [SystemSettingController::class, 'cashbackIndex'])->name('settings.cashback.index');
        Route::post('settings/cashback', [SystemSettingController::class, 'cashbackUpdate'])->name('settings.cashback.update');

        // Home Page Banners Management
        Route::get('settings/banners', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'index'])->name('banners.index');
        Route::post('settings/banners', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'store'])->name('banners.store');
        Route::get('settings/banners/{id}/edit', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'edit'])->name('banners.edit');
        Route::put('settings/banners/{id}', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'update'])->name('banners.update');
        Route::post('settings/banners/{id}/update', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'update']);
        Route::post('settings/banners/{id}/toggle-status', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'toggleStatus'])->name('banners.toggle-status');
        Route::match(['get', 'post', 'delete'], 'settings/banners/{id}/delete', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'destroy'])->name('banners.delete');
        Route::match(['post', 'delete'], 'settings/banners/{id}', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'destroy'])->name('banners.destroy');
        Route::get('settings/banners/{id}', [\App\Http\Controllers\Admin\HomePageBannerController::class, 'edit'])->name('banners.show');

        // ADC Orders Admin Monitoring Dashboard
        Route::get('adc-orders', [\App\Http\Controllers\Admin\AdcOrderController::class, 'index'])->name('adc-orders.index')->middleware('permission:users.view');
        Route::get('adc-orders/{id}', [\App\Http\Controllers\Admin\AdcOrderController::class, 'show'])->name('adc-orders.show')->middleware('permission:users.view');
        Route::get('adc-orders/{id}/invoice', [\App\Http\Controllers\Admin\AdcOrderController::class, 'printInvoice'])->name('adc-orders.invoice')->middleware('permission:users.view');
        Route::get('adc/orders/{id}/invoice', [\App\Http\Controllers\Admin\AdcOrderController::class, 'printInvoice'])->middleware('permission:users.view');
        Route::post('adc-orders/{id}/cancel', [\App\Http\Controllers\Admin\AdcOrderController::class, 'cancel'])->name('adc-orders.cancel')->middleware('permission:users.manage');

        // Purchase Orders Admin Monitoring Dashboard
        Route::get('purchase-orders', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'index'])->name('purchase-orders.index')->middleware('permission:users.view');
        Route::get('purchase-orders/{id}', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'show'])->name('purchase-orders.show')->middleware('permission:users.view');
        Route::get('purchase-orders/{id}/invoice', [\App\Http\Controllers\InvoiceController::class, 'printInvoice'])->name('purchase-orders.invoice')->middleware('permission:users.view');
        Route::post('purchase-orders/{id}/invoice/transporter', [\App\Http\Controllers\InvoiceController::class, 'updateTransporter'])->name('purchase-orders.invoice.transporter')->middleware('permission:users.manage');
        Route::post('purchase-orders/{id}/cancel', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel')->middleware('permission:users.manage');
        Route::post('purchase-orders/{id}/complete', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'markCompleted'])->name('purchase-orders.complete')->middleware('permission:users.manage');
        Route::post('purchase-orders/{id}/toggle-show-order', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'toggleShowOrder'])->name('purchase-orders.toggle-show-order')->middleware('permission:users.manage');

        // Debit Notes & Settlement Management (ADPL)
        Route::get('debit-notes', [\App\Http\Controllers\Admin\DebitNoteController::class, 'index'])->name('debit-notes.index')->middleware('permission:users.view');
        Route::get('debit-notes/{id}', [\App\Http\Controllers\Admin\DebitNoteController::class, 'show'])->name('debit-notes.show')->middleware('permission:users.view');
        Route::post('debit-notes/{id}/verify-settlement', [\App\Http\Controllers\Admin\DebitNoteController::class, 'verifySettlement'])->name('debit-notes.verify-settlement')->middleware('permission:users.manage');

        // Admin Excess Stock Invoices
        Route::get('excess-invoices', [\App\Http\Controllers\Admin\ExcessInvoiceController::class, 'index'])->name('excess-invoices.index')->middleware('permission:users.view');
        Route::get('excess-invoices/{id}', [\App\Http\Controllers\Admin\ExcessInvoiceController::class, 'show'])->name('excess-invoices.show')->middleware('permission:users.view');

        // Central Invoices Management (All Invoices & Editable Invoice Number)
        Route::get('invoices', [\App\Http\Controllers\Admin\AdminInvoiceController::class, 'index'])->name('invoices.index')->middleware('permission:users.view');
        Route::post('invoices/update-number', [\App\Http\Controllers\Admin\AdminInvoiceController::class, 'updateInvoiceNumber'])->name('invoices.update-number')->middleware('permission:users.manage');

        // Credit Notes Management (ADPL)
        Route::get('credit-notes', [\App\Http\Controllers\Admin\CreditNoteController::class, 'index'])->name('credit-notes.index')->middleware('permission:users.view');
        Route::get('credit-notes/{id}', [\App\Http\Controllers\Admin\CreditNoteController::class, 'show'])->name('credit-notes.show')->middleware('permission:users.view');

        // ADC Debit Notes, Credit Notes & Excess Invoices for Admin
        Route::get('adc-debit-notes/{id}', [\App\Http\Controllers\Admin\DebitNoteController::class, 'showAdcDebitNote'])->name('adc-debit-notes.show')->middleware('permission:users.view');
        Route::get('adc-excess-invoices/{id}', [\App\Http\Controllers\Admin\ExcessInvoiceController::class, 'showAdcExcessInvoice'])->name('adc-excess-invoices.show')->middleware('permission:users.view');
        Route::get('adc-credit-notes/{id}', [\App\Http\Controllers\Admin\CreditNoteController::class, 'showAdcCreditNote'])->name('adc-credit-notes.show')->middleware('permission:users.view');

        // Seller Payments Dashboard
        Route::get('seller-payments', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'sellerPayments'])->name('seller-payments.index')->middleware('permission:users.view');
        Route::post('seller-payments/{id}/pay', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'paySeller'])->name('seller-payments.pay')->middleware('permission:users.manage');
        Route::post('seller-payments/{id}/verify', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'verifySellerPayment'])->name('seller-payments.verify')->middleware('permission:users.manage');

        // AMA Transactions Dashboard
        Route::get('ama-transactions', [\App\Http\Controllers\Admin\IncentiveHoldController::class, 'index'])->name('ama-transactions.index')->middleware('permission:users.view');

        // Contact Inquiries
        Route::get('contact-inquiries', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'index'])->name('contact-inquiries.index')->middleware('permission:users.view');
        Route::post('contact-inquiries/bulk', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'bulkAction'])->name('contact-inquiries.bulk')->middleware('permission:users.manage');
        Route::delete('contact-inquiries/{id}', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'destroy'])->name('contact-inquiries.destroy')->middleware('permission:users.manage');

        // Newsletter Subscribers
        Route::get('newsletter-subscribers', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index')->middleware('permission:users.view');
        Route::post('newsletter-subscribers/bulk', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'bulkAction'])->name('newsletter-subscribers.bulk')->middleware('permission:users.manage');
        Route::delete('newsletter-subscribers/{id}', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy')->middleware('permission:users.manage');

        // Rack & Shelf Management (Admin)
        Route::get('racks/list', [\App\Http\Controllers\Admin\RackSettingController::class, 'racksList'])->name('racks.list');
        Route::get('racks/settings', [\App\Http\Controllers\Admin\RackSettingController::class, 'index'])->name('racks.settings');
        Route::post('racks/settings/pricing', [\App\Http\Controllers\Admin\RackSettingController::class, 'updatePricing'])->name('racks.settings.pricing');
        Route::post('racks/settings/bank', [\App\Http\Controllers\Admin\RackSettingController::class, 'updateBankDetails'])->name('racks.settings.bank');
        Route::get('racks/images', [\App\Http\Controllers\Admin\RackSettingController::class, 'pendingImages'])->name('racks.images');
        Route::post('racks/images/{id}/approve', [\App\Http\Controllers\Admin\RackSettingController::class, 'approveImage'])->name('racks.images.approve');
        Route::post('racks/images/{id}/reject', [\App\Http\Controllers\Admin\RackSettingController::class, 'rejectImage'])->name('racks.images.reject');
        Route::get('racks/payments', [\App\Http\Controllers\Admin\RackSettingController::class, 'paymentVerifications'])->name('racks.payments');
        Route::post('racks/payments/{id}/verify', [\App\Http\Controllers\Admin\RackSettingController::class, 'verifyPayment'])->name('racks.payments.verify');
        Route::post('racks/payments/{id}/reject', [\App\Http\Controllers\Admin\RackSettingController::class, 'rejectPayment'])->name('racks.payments.reject');

        // FAQ Management
        Route::resource('faq-topics', \App\Http\Controllers\Admin\FaqTopicController::class)->except(['create', 'edit', 'show']);
        Route::post('faq-topics/{faqTopic}/toggle-status', [\App\Http\Controllers\Admin\FaqTopicController::class, 'toggleStatus'])->name('faq-topics.toggle-status');

        Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class)->except(['create', 'edit', 'show']);
        Route::post('faqs/{faq}/toggle-status', [\App\Http\Controllers\Admin\FaqController::class, 'toggleStatus'])->name('faqs.toggle-status');

        // Bulk Account Import Module (Seller, ADC, DDC)
        Route::prefix('import')->name('import.')->group(function () {
            // Seller Import
            Route::get('seller', [BulkImportController::class, 'sellerIndex'])->name('seller');
            Route::get('seller/sample', [BulkImportController::class, 'sellerSample'])->name('seller.sample');
            Route::post('seller/preview', [BulkImportController::class, 'sellerPreview'])->name('seller.preview');
            Route::post('seller/process', [BulkImportController::class, 'sellerProcess'])->name('seller.process');
            Route::post('seller/clear', [BulkImportController::class, 'sellerClear'])->name('seller.clear');

            // Sub Seller Import
            Route::get('sub-seller', [BulkImportController::class, 'subSellerIndex'])->name('sub_seller');
            Route::get('sub-seller/sample', [BulkImportController::class, 'subSellerSample'])->name('sub_seller.sample');
            Route::post('sub-seller/preview', [BulkImportController::class, 'subSellerPreview'])->name('sub_seller.preview');
            Route::post('sub-seller/process', [BulkImportController::class, 'subSellerProcess'])->name('sub_seller.process');
            Route::post('sub-seller/clear', [BulkImportController::class, 'subSellerClear'])->name('sub_seller.clear');

            // ADC Import
            Route::get('adc', [BulkImportController::class, 'adcIndex'])->name('adc');
            Route::get('adc/sample', [BulkImportController::class, 'adcSample'])->name('adc.sample');
            Route::post('adc/preview', [BulkImportController::class, 'adcPreview'])->name('adc.preview');
            Route::post('adc/process', [BulkImportController::class, 'adcProcess'])->name('adc.process');
            Route::post('adc/clear', [BulkImportController::class, 'adcClear'])->name('adc.clear');

            // DDC Import
            Route::get('ddc', [BulkImportController::class, 'ddcIndex'])->name('ddc');
            Route::get('ddc/sample', [BulkImportController::class, 'ddcSample'])->name('ddc.sample');
            Route::post('ddc/preview', [BulkImportController::class, 'ddcPreview'])->name('ddc.preview');
            Route::post('ddc/process', [BulkImportController::class, 'ddcProcess'])->name('ddc.process');
            Route::post('ddc/clear', [BulkImportController::class, 'ddcClear'])->name('ddc.clear');

            // Product Import
            Route::get('product', [BulkImportController::class, 'productIndex'])->name('product');
            Route::get('product/sample', [BulkImportController::class, 'productSample'])->name('product.sample');
            Route::post('product/preview', [BulkImportController::class, 'productPreview'])->name('product.preview');
            Route::post('product/process', [BulkImportController::class, 'productProcess'])->name('product.process');
            Route::post('product/clear', [BulkImportController::class, 'productClear'])->name('product.clear');

            // Pickup Store Import
            Route::get('pickup-store', [BulkImportController::class, 'pickupStoreIndex'])->name('pickup_store');
            Route::get('pickup-store/sample', [BulkImportController::class, 'pickupStoreSample'])->name('pickup_store.sample');
            Route::post('pickup-store/preview', [BulkImportController::class, 'pickupStorePreview'])->name('pickup_store.preview');
            Route::post('pickup-store/process', [BulkImportController::class, 'pickupStoreProcess'])->name('pickup_store.process');
            Route::post('pickup-store/clear', [BulkImportController::class, 'pickupStoreClear'])->name('pickup_store.clear');

            // Delivery Area Import
            Route::get('delivery-area', [BulkImportController::class, 'deliveryAreaIndex'])->name('delivery_area');
            Route::get('delivery-area/sample', [BulkImportController::class, 'deliveryAreaSample'])->name('delivery_area.sample');
            Route::post('delivery-area/preview', [BulkImportController::class, 'deliveryAreaPreview'])->name('delivery_area.preview');
            Route::post('delivery-area/process', [BulkImportController::class, 'deliveryAreaProcess'])->name('delivery_area.process');
            Route::post('delivery-area/clear', [BulkImportController::class, 'deliveryAreaClear'])->name('delivery_area.clear');

            // DDC Customer Import
            Route::get('ddc-customer', [BulkImportController::class, 'ddcCustomerIndex'])->name('ddc_customer');
            Route::get('ddc-customer/sample', [BulkImportController::class, 'ddcCustomerSample'])->name('ddc_customer.sample');
            Route::post('ddc-customer/preview', [BulkImportController::class, 'ddcCustomerPreview'])->name('ddc_customer.preview');
            Route::post('ddc-customer/process', [BulkImportController::class, 'ddcCustomerProcess'])->name('ddc_customer.process');
            Route::post('ddc-customer/clear', [BulkImportController::class, 'ddcCustomerClear'])->name('ddc_customer.clear');

            // ADC Customer Import
            Route::get('adc-customer', [BulkImportController::class, 'adcCustomerIndex'])->name('adc_customer');
            Route::get('adc-customer/sample', [BulkImportController::class, 'adcCustomerSample'])->name('adc_customer.sample');
            Route::post('adc-customer/preview', [BulkImportController::class, 'adcCustomerPreview'])->name('adc_customer.preview');
            Route::post('adc-customer/process', [BulkImportController::class, 'adcCustomerProcess'])->name('adc_customer.process');
            Route::post('adc-customer/clear', [BulkImportController::class, 'adcCustomerClear'])->name('adc_customer.clear');

            // DDC MSME Stock Inventory Import
            Route::get('ddc-msme-inventory', [BulkImportController::class, 'ddcMsmeInventoryIndex'])->name('ddc_msme_inventory');
            Route::get('ddc-msme-inventory/sample', [BulkImportController::class, 'ddcMsmeInventorySample'])->name('ddc_msme_inventory.sample');
            Route::post('ddc-msme-inventory/preview', [BulkImportController::class, 'ddcMsmeInventoryPreview'])->name('ddc_msme_inventory.preview');
            Route::post('ddc-msme-inventory/process', [BulkImportController::class, 'ddcMsmeInventoryProcess'])->name('ddc_msme_inventory.process');
            Route::post('ddc-msme-inventory/clear', [BulkImportController::class, 'ddcMsmeInventoryClear'])->name('ddc_msme_inventory.clear');

            // DDC Local Inventory Import
            Route::get('ddc-local-inventory', [BulkImportController::class, 'ddcLocalInventoryIndex'])->name('ddc_local_inventory');
            Route::get('ddc-local-inventory/sample', [BulkImportController::class, 'ddcLocalInventorySample'])->name('ddc_local_inventory.sample');
            Route::post('ddc-local-inventory/preview', [BulkImportController::class, 'ddcLocalInventoryPreview'])->name('ddc_local_inventory.preview');
            Route::post('ddc-local-inventory/process', [BulkImportController::class, 'ddcLocalInventoryProcess'])->name('ddc_local_inventory.process');
            Route::post('ddc-local-inventory/clear', [BulkImportController::class, 'ddcLocalInventoryClear'])->name('ddc_local_inventory.clear');

            // ADC MSME Stock Inventory Import
            Route::get('adc-msme-inventory', [BulkImportController::class, 'adcMsmeInventoryIndex'])->name('adc_msme_inventory');
            Route::get('adc-msme-inventory/sample', [BulkImportController::class, 'adcMsmeInventorySample'])->name('adc_msme_inventory.sample');
            Route::post('adc-msme-inventory/preview', [BulkImportController::class, 'adcMsmeInventoryPreview'])->name('adc_msme_inventory.preview');
            Route::post('adc-msme-inventory/process', [BulkImportController::class, 'adcMsmeInventoryProcess'])->name('adc_msme_inventory.process');
            Route::post('adc-msme-inventory/clear', [BulkImportController::class, 'adcMsmeInventoryClear'])->name('adc_msme_inventory.clear');

            // ADC Local Inventory Import
            Route::get('adc-local-inventory', [BulkImportController::class, 'adcLocalInventoryIndex'])->name('adc_local_inventory');
            Route::get('adc-local-inventory/sample', [BulkImportController::class, 'adcLocalInventorySample'])->name('adc_local_inventory.sample');
            Route::post('adc-local-inventory/preview', [BulkImportController::class, 'adcLocalInventoryPreview'])->name('adc_local_inventory.preview');
            Route::post('adc-local-inventory/preview-page', [BulkImportController::class, 'adcLocalInventoryPreviewPage'])->name('adc_local_inventory.preview_page');
            Route::get('adc-local-inventory/export-preview-errors', [BulkImportController::class, 'adcLocalInventoryExportPreviewErrors'])->name('adc_local_inventory.export_preview_errors');
            Route::get('adc-local-inventory/export-import-logs', [BulkImportController::class, 'adcLocalInventoryExportImportLogs'])->name('adc_local_inventory.export_import_logs');
            Route::post('adc-local-inventory/process', [BulkImportController::class, 'adcLocalInventoryProcess'])->name('adc_local_inventory.process');
            Route::post('adc-local-inventory/clear', [BulkImportController::class, 'adcLocalInventoryClear'])->name('adc_local_inventory.clear');
        });

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
