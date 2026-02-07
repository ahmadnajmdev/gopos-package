<?php

namespace Gopos\Services;

use Gopos\Models\Permission;
use Gopos\Models\Role;
use Gopos\Models\User;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Get all module permissions organized by module.
     */
    public static function getModulePermissions(): array
    {
        return [
            'pos' => [
                'pos.access' => 'Access POS',
                'pos.process_sale' => 'Process Sales',
                'pos.apply_discount' => 'Apply Discounts',
                'pos.void_sale' => 'Void Sales',
                'pos.view_shift_report' => 'View Shift Reports',
                'pos.open_drawer' => 'Open Cash Drawer',
                'pos.process_refund' => 'Process Refunds',
                'pos.hold_sale' => 'Hold/Recall Sales',
            ],
            'inventory' => [
                'inventory.view' => 'View Inventory',
                'inventory.create' => 'Create Products',
                'inventory.edit' => 'Edit Products',
                'inventory.delete' => 'Delete Products',
                'inventory.adjust_stock' => 'Adjust Stock',
                'inventory.transfer' => 'Transfer Stock',
                'inventory.view_cost' => 'View Cost Prices',
                'inventory.manage_warehouses' => 'Manage Warehouses',
            ],
            'sales' => [
                'sales.view' => 'View Sales',
                'sales.create' => 'Create Sales',
                'sales.edit' => 'Edit Sales',
                'sales.delete' => 'Delete Sales',
                'sales.view_all' => 'View All Sales',
                'sales.export' => 'Export Sales',
            ],
            'purchases' => [
                'purchases.view' => 'View Purchases',
                'purchases.create' => 'Create Purchases',
                'purchases.edit' => 'Edit Purchases',
                'purchases.delete' => 'Delete Purchases',
                'purchases.approve' => 'Approve Purchases',
            ],
            'customers' => [
                'customers.view' => 'View Customers',
                'customers.create' => 'Create Customers',
                'customers.edit' => 'Edit Customers',
                'customers.delete' => 'Delete Customers',
                'customers.view_balance' => 'View Customer Balances',
            ],
            'suppliers' => [
                'suppliers.view' => 'View Suppliers',
                'suppliers.create' => 'Create Suppliers',
                'suppliers.edit' => 'Edit Suppliers',
                'suppliers.delete' => 'Delete Suppliers',
            ],
            'accounting' => [
                'accounting.view' => 'View Accounting',
                'accounting.create_journal' => 'Create Journal Entries',
                'accounting.post_journal' => 'Post Journal Entries',
                'accounting.void_journal' => 'Void Journal Entries',
                'accounting.view_reports' => 'View Financial Reports',
                'accounting.close_period' => 'Close Fiscal Periods',
                'accounting.manage_accounts' => 'Manage Chart of Accounts',
                'accounting.bank_reconciliation' => 'Bank Reconciliation',
            ],
            'hr' => [
                // Employees
                'hr.view_employees' => 'View Employees',
                'hr.create_employees' => 'Create Employees',
                'hr.edit_employees' => 'Edit Employees',
                'hr.delete_employees' => 'Delete Employees',
                'hr.terminate_employees' => 'Terminate Employees',
                // Departments & Positions
                'hr.manage_departments' => 'Manage Departments',
                'hr.manage_positions' => 'Manage Positions',
                // Attendance
                'hr.view_attendance' => 'View Attendance',
                'hr.manage_attendance' => 'Manage Attendance',
                'hr.clock_in_out' => 'Clock In/Out',
                'hr.manage_schedules' => 'Manage Work Schedules',
                'hr.approve_overtime' => 'Approve Overtime',
                // Leave
                'hr.view_leave' => 'View Leave Requests',
                'hr.manage_leave' => 'Manage Leave',
                'hr.approve_leave' => 'Approve Leave Requests',
                'hr.manage_leave_types' => 'Manage Leave Types',
                // Payroll
                'hr.view_payroll' => 'View Payroll',
                'hr.process_payroll' => 'Process Payroll',
                'hr.approve_payroll' => 'Approve Payroll',
                'hr.manage_components' => 'Manage Payroll Components',
                'hr.view_payslips' => 'View Payslips',
                // Loans
                'hr.view_loans' => 'View Employee Loans',
                'hr.manage_loans' => 'Manage Employee Loans',
                'hr.approve_loans' => 'Approve Loans',
                // Reports
                'hr.view_reports' => 'View HR Reports',
                // Holidays
                'hr.manage_holidays' => 'Manage Holidays',
            ],
            'reports' => [
                'reports.sales' => 'Sales Reports',
                'reports.inventory' => 'Inventory Reports',
                'reports.financial' => 'Financial Reports',
                'reports.hr' => 'HR Reports',
                'reports.export' => 'Export Reports',
            ],
            'settings' => [
                'settings.view' => 'View Settings',
                'settings.manage' => 'Manage Settings',
                'settings.manage_users' => 'Manage Users',
                'settings.manage_roles' => 'Manage Roles',
            ],
        ];
    }

    /**
     * Get Arabic translations for permissions.
     */
    public static function getArabicTranslations(): array
    {
        return [
            // POS
            'pos.access' => 'الوصول إلى نقطة البيع',
            'pos.process_sale' => 'معالجة المبيعات',
            'pos.apply_discount' => 'تطبيق الخصومات',
            'pos.void_sale' => 'إلغاء المبيعات',
            'pos.view_shift_report' => 'عرض تقارير الوردية',
            'pos.open_drawer' => 'فتح درج النقد',
            'pos.process_refund' => 'معالجة المرتجعات',
            'pos.hold_sale' => 'تعليق/استرجاع المبيعات',
            // Inventory
            'inventory.view' => 'عرض المخزون',
            'inventory.create' => 'إنشاء المنتجات',
            'inventory.edit' => 'تعديل المنتجات',
            'inventory.delete' => 'حذف المنتجات',
            'inventory.adjust_stock' => 'تعديل المخزون',
            'inventory.transfer' => 'نقل المخزون',
            'inventory.view_cost' => 'عرض أسعار التكلفة',
            'inventory.manage_warehouses' => 'إدارة المستودعات',
            // Sales
            'sales.view' => 'عرض المبيعات',
            'sales.create' => 'إنشاء المبيعات',
            'sales.edit' => 'تعديل المبيعات',
            'sales.delete' => 'حذف المبيعات',
            'sales.view_all' => 'عرض جميع المبيعات',
            'sales.export' => 'تصدير المبيعات',
            // Purchases
            'purchases.view' => 'عرض المشتريات',
            'purchases.create' => 'إنشاء المشتريات',
            'purchases.edit' => 'تعديل المشتريات',
            'purchases.delete' => 'حذف المشتريات',
            'purchases.approve' => 'الموافقة على المشتريات',
            // Customers
            'customers.view' => 'عرض العملاء',
            'customers.create' => 'إنشاء العملاء',
            'customers.edit' => 'تعديل العملاء',
            'customers.delete' => 'حذف العملاء',
            'customers.view_balance' => 'عرض أرصدة العملاء',
            // Suppliers
            'suppliers.view' => 'عرض الموردين',
            'suppliers.create' => 'إنشاء الموردين',
            'suppliers.edit' => 'تعديل الموردين',
            'suppliers.delete' => 'حذف الموردين',
            // Accounting
            'accounting.view' => 'عرض المحاسبة',
            'accounting.create_journal' => 'إنشاء القيود اليومية',
            'accounting.post_journal' => 'ترحيل القيود اليومية',
            'accounting.void_journal' => 'إلغاء القيود اليومية',
            'accounting.view_reports' => 'عرض التقارير المالية',
            'accounting.close_period' => 'إغلاق الفترات المالية',
            'accounting.manage_accounts' => 'إدارة شجرة الحسابات',
            'accounting.bank_reconciliation' => 'تسوية البنوك',
            // HR - Employees
            'hr.view_employees' => 'عرض الموظفين',
            'hr.create_employees' => 'إنشاء الموظفين',
            'hr.edit_employees' => 'تعديل الموظفين',
            'hr.delete_employees' => 'حذف الموظفين',
            'hr.terminate_employees' => 'إنهاء خدمة الموظفين',
            // HR - Departments & Positions
            'hr.manage_departments' => 'إدارة الأقسام',
            'hr.manage_positions' => 'إدارة المناصب',
            // HR - Attendance
            'hr.view_attendance' => 'عرض الحضور',
            'hr.manage_attendance' => 'إدارة الحضور',
            'hr.clock_in_out' => 'تسجيل الدخول/الخروج',
            'hr.manage_schedules' => 'إدارة جداول العمل',
            'hr.approve_overtime' => 'الموافقة على العمل الإضافي',
            // HR - Leave
            'hr.view_leave' => 'عرض طلبات الإجازة',
            'hr.manage_leave' => 'إدارة الإجازات',
            'hr.approve_leave' => 'الموافقة على الإجازات',
            'hr.manage_leave_types' => 'إدارة أنواع الإجازات',
            // HR - Payroll
            'hr.view_payroll' => 'عرض الرواتب',
            'hr.process_payroll' => 'معالجة الرواتب',
            'hr.approve_payroll' => 'الموافقة على الرواتب',
            'hr.manage_components' => 'إدارة مكونات الرواتب',
            'hr.view_payslips' => 'عرض كشوف الرواتب',
            // HR - Loans
            'hr.view_loans' => 'عرض قروض الموظفين',
            'hr.manage_loans' => 'إدارة قروض الموظفين',
            'hr.approve_loans' => 'الموافقة على القروض',
            // HR - Reports & Holidays
            'hr.view_reports' => 'عرض تقارير الموارد البشرية',
            'hr.manage_holidays' => 'إدارة العطلات',
            // Reports
            'reports.sales' => 'تقارير المبيعات',
            'reports.inventory' => 'تقارير المخزون',
            'reports.financial' => 'التقارير المالية',
            'reports.hr' => 'تقارير الموارد البشرية',
            'reports.export' => 'تصدير التقارير',
            // Settings
            'settings.view' => 'عرض الإعدادات',
            'settings.manage' => 'إدارة الإعدادات',
            'settings.manage_users' => 'إدارة المستخدمين',
            'settings.manage_roles' => 'إدارة الأدوار',
        ];
    }

    /**
     * Get Kurdish (Sorani) translations for permissions.
     */
    public static function getKurdishTranslations(): array
    {
        return [
            // POS
            'pos.access' => 'دەستگەیشتن بە سندوقی فرۆشتن',
            'pos.process_sale' => 'جێبەجێکردنی فرۆشتن',
            'pos.apply_discount' => 'داشکاندن بەکاربێنە',
            'pos.void_sale' => 'هەڵوەشاندنەوەی فرۆشتن',
            'pos.view_shift_report' => 'بینینی ڕاپۆرتی شیفت',
            'pos.open_drawer' => 'کردنەوەی دراوەری پارە',
            'pos.process_refund' => 'گەڕاندنەوەی پارە',
            'pos.hold_sale' => 'ڕاگرتن/بەردەوامکردنی فرۆشتن',
            // Inventory
            'inventory.view' => 'بینینی کۆگا',
            'inventory.create' => 'دروستکردنی بەرهەم',
            'inventory.edit' => 'دەستکاریکردنی بەرهەم',
            'inventory.delete' => 'سڕینەوەی بەرهەم',
            'inventory.adjust_stock' => 'ڕێکخستنی کۆگا',
            'inventory.transfer' => 'گواستنەوەی کۆگا',
            'inventory.view_cost' => 'بینینی نرخی تێچوو',
            'inventory.manage_warehouses' => 'بەڕێوەبردنی کۆگاکان',
            // Sales
            'sales.view' => 'بینینی فرۆشتنەکان',
            'sales.create' => 'دروستکردنی فرۆشتن',
            'sales.edit' => 'دەستکاریکردنی فرۆشتن',
            'sales.delete' => 'سڕینەوەی فرۆشتن',
            'sales.view_all' => 'بینینی هەموو فرۆشتنەکان',
            'sales.export' => 'هەناردەکردنی فرۆشتن',
            // Purchases
            'purchases.view' => 'بینینی کڕینەکان',
            'purchases.create' => 'دروستکردنی کڕین',
            'purchases.edit' => 'دەستکاریکردنی کڕین',
            'purchases.delete' => 'سڕینەوەی کڕین',
            'purchases.approve' => 'پەسەندکردنی کڕین',
            // Customers
            'customers.view' => 'بینینی کڕیارەکان',
            'customers.create' => 'دروستکردنی کڕیار',
            'customers.edit' => 'دەستکاریکردنی کڕیار',
            'customers.delete' => 'سڕینەوەی کڕیار',
            'customers.view_balance' => 'بینینی باڵانسی کڕیارەکان',
            // Suppliers
            'suppliers.view' => 'بینینی دابینکەرەکان',
            'suppliers.create' => 'دروستکردنی دابینکەر',
            'suppliers.edit' => 'دەستکاریکردنی دابینکەر',
            'suppliers.delete' => 'سڕینەوەی دابینکەر',
            // Accounting
            'accounting.view' => 'بینینی ژمێریاری',
            'accounting.create_journal' => 'دروستکردنی تۆمارەکانی ڕۆژانە',
            'accounting.post_journal' => 'ناردنی تۆمارەکانی ڕۆژانە',
            'accounting.void_journal' => 'هەڵوەشاندنەوەی تۆمارەکانی ڕۆژانە',
            'accounting.view_reports' => 'بینینی ڕاپۆرتە داراییەکان',
            'accounting.close_period' => 'داخستنی ماوەی دارایی',
            'accounting.manage_accounts' => 'بەڕێوەبردنی ڕووکاری هەژمارەکان',
            'accounting.bank_reconciliation' => 'ڕێکخستنی بانک',
            // HR - Employees
            'hr.view_employees' => 'بینینی کارمەندەکان',
            'hr.create_employees' => 'دروستکردنی کارمەند',
            'hr.edit_employees' => 'دەستکاریکردنی کارمەند',
            'hr.delete_employees' => 'سڕینەوەی کارمەند',
            'hr.terminate_employees' => 'کۆتایی هێنان بە خزمەت',
            // HR - Departments & Positions
            'hr.manage_departments' => 'بەڕێوەبردنی بەشەکان',
            'hr.manage_positions' => 'بەڕێوەبردنی پۆستەکان',
            // HR - Attendance
            'hr.view_attendance' => 'بینینی ئامادەبوون',
            'hr.manage_attendance' => 'بەڕێوەبردنی ئامادەبوون',
            'hr.clock_in_out' => 'تۆمارکردنی هاتن/ڕۆیشتن',
            'hr.manage_schedules' => 'بەڕێوەبردنی خشتەی کار',
            'hr.approve_overtime' => 'پەسەندکردنی کاری زیادە',
            // HR - Leave
            'hr.view_leave' => 'بینینی داواکاریەکانی مۆڵەت',
            'hr.manage_leave' => 'بەڕێوەبردنی مۆڵەت',
            'hr.approve_leave' => 'پەسەندکردنی مۆڵەت',
            'hr.manage_leave_types' => 'بەڕێوەبردنی جۆرەکانی مۆڵەت',
            // HR - Payroll
            'hr.view_payroll' => 'بینینی مووچەکان',
            'hr.process_payroll' => 'جێبەجێکردنی مووچە',
            'hr.approve_payroll' => 'پەسەندکردنی مووچە',
            'hr.manage_components' => 'بەڕێوەبردنی پێکهاتەکانی مووچە',
            'hr.view_payslips' => 'بینینی پسوولەی مووچە',
            // HR - Loans
            'hr.view_loans' => 'بینینی قەرزەکانی کارمەندان',
            'hr.manage_loans' => 'بەڕێوەبردنی قەرزەکانی کارمەندان',
            'hr.approve_loans' => 'پەسەندکردنی قەرز',
            // HR - Reports & Holidays
            'hr.view_reports' => 'بینینی ڕاپۆرتەکانی کەسایەتی',
            'hr.manage_holidays' => 'بەڕێوەبردنی پشووەکان',
            // Reports
            'reports.sales' => 'ڕاپۆرتی فرۆشتن',
            'reports.inventory' => 'ڕاپۆرتی کۆگا',
            'reports.financial' => 'ڕاپۆرتی دارایی',
            'reports.hr' => 'ڕاپۆرتی HR',
            'reports.export' => 'هەناردەکردنی ڕاپۆرت',
            // Settings
            'settings.view' => 'بینینی ڕێکخستنەکان',
            'settings.manage' => 'بەڕێوەبردنی ڕێکخستنەکان',
            'settings.manage_users' => 'بەڕێوەبردنی بەکارهێنەرەکان',
            'settings.manage_roles' => 'بەڕێوەبردنی ڕۆڵەکان',
        ];
    }

    /**
     * Get default role definitions.
     */
    public static function getDefaultRoles(): array
    {
        return [
            'super_admin' => [
                'name_ar' => 'مدير النظام',
                'name_ckb' => 'بەڕێوەبەری سیستەم',
                'description' => 'Full access to all system features',
                'description_ar' => 'وصول كامل لجميع ميزات النظام',
                'description_ckb' => 'دەستگەیشتنی تەواو بە هەموو تایبەتمەندییەکانی سیستەم',
                'is_system' => true,
                'permissions' => ['*'],
            ],
            'manager' => [
                'name_ar' => 'مدير',
                'name_ckb' => 'بەڕێوەبەر',
                'description' => 'Manager with access to POS, inventory, and reports',
                'description_ar' => 'مدير مع وصول لنقطة البيع والمخزون والتقارير',
                'description_ckb' => 'بەڕێوەبەر لەگەڵ دەستگەیشتن بە سندوقی فرۆشتن و کۆگا و ڕاپۆرتەکان',
                'is_system' => true,
                'permissions' => [
                    'pos.*',
                    'inventory.*',
                    'sales.*',
                    'purchases.*',
                    'customers.*',
                    'suppliers.*',
                    'accounting.view',
                    'accounting.view_reports',
                    // HR view permissions
                    'hr.view_employees',
                    'hr.view_attendance',
                    'hr.view_leave',
                    'hr.view_payroll',
                    'hr.view_payslips',
                    'hr.view_loans',
                    'hr.view_reports',
                    'reports.*',
                    'settings.view',
                ],
            ],
            'accountant' => [
                'name_ar' => 'محاسب',
                'name_ckb' => 'ژمێریار',
                'description' => 'Full access to accounting features',
                'description_ar' => 'وصول كامل لميزات المحاسبة',
                'description_ckb' => 'دەستگەیشتنی تەواو بە تایبەتمەندییەکانی ژمێریاری',
                'is_system' => true,
                'permissions' => [
                    'accounting.*',
                    'reports.financial',
                    'reports.export',
                    'sales.view',
                    'purchases.view',
                    'customers.view',
                    'customers.view_balance',
                    'suppliers.view',
                ],
            ],
            'cashier' => [
                'name_ar' => 'كاشير',
                'name_ckb' => 'کاشێر',
                'description' => 'POS access only',
                'description_ar' => 'وصول لنقطة البيع فقط',
                'description_ckb' => 'دەستگەیشتن بە سندوقی فرۆشتن تەنها',
                'is_system' => true,
                'permissions' => [
                    'pos.access',
                    'pos.process_sale',
                    'pos.apply_discount',
                    'customers.view',
                    'customers.create',
                ],
            ],
            'warehouse_staff' => [
                'name_ar' => 'موظف مستودع',
                'name_ckb' => 'کارمەندی کۆگا',
                'description' => 'Inventory operations',
                'description_ar' => 'عمليات المستودع',
                'description_ckb' => 'کارەکانی کۆگا',
                'is_system' => true,
                'permissions' => [
                    'inventory.view',
                    'inventory.adjust_stock',
                    'inventory.transfer',
                    'purchases.view',
                    'purchases.create',
                ],
            ],
            'hr_manager' => [
                'name_ar' => 'مدير الموارد البشرية',
                'name_ckb' => 'بەڕێوەبەری کەسایەتی',
                'description' => 'Full HR access',
                'description_ar' => 'وصول كامل للموارد البشرية',
                'description_ckb' => 'دەستگەیشتنی تەواو بە کەسایەتی',
                'is_system' => true,
                'permissions' => [
                    'hr.*',
                    'reports.hr',
                    'reports.export',
                ],
            ],
            'hr_staff' => [
                'name_ar' => 'موظف موارد بشرية',
                'name_ckb' => 'کارمەندی کەسایەتی',
                'description' => 'Limited HR access',
                'description_ar' => 'وصول محدود للموارد البشرية',
                'description_ckb' => 'دەستگەیشتنی سنووردار بە کەسایەتی',
                'is_system' => true,
                'permissions' => [
                    // Employee viewing
                    'hr.view_employees',
                    // Attendance
                    'hr.view_attendance',
                    'hr.manage_attendance',
                    'hr.clock_in_out',
                    // Leave
                    'hr.view_leave',
                    'hr.manage_leave',
                    // Payroll (view only)
                    'hr.view_payslips',
                ],
            ],
        ];
    }

    /**
     * Check if user has permission.
     */
    public function userCan(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }

    /**
     * Check if user has role.
     */
    public function userHasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Assign role to user.
     */
    public function assignRole(User $user, string|Role $role): void
    {
        $user->assignRole($role);
    }

    /**
     * Remove role from user.
     */
    public function removeRole(User $user, string|Role $role): void
    {
        $user->removeRole($role);
    }

    /**
     * Sync permissions for role.
     */
    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }

    /**
     * Create all default permissions in database.
     */
    public function createDefaultPermissions(): void
    {
        $arabicTranslations = self::getArabicTranslations();
        $kurdishTranslations = self::getKurdishTranslations();

        foreach (self::getModulePermissions() as $module => $permissions) {
            foreach ($permissions as $name => $description) {
                Permission::updateOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    [
                        'name' => $name,
                        'name_ar' => $arabicTranslations[$name] ?? null,
                        'name_ckb' => $kurdishTranslations[$name] ?? null,
                        'guard_name' => 'web',
                        'module' => $module,
                        'description' => $description,
                        'description_ar' => $arabicTranslations[$name] ?? null,
                        'description_ckb' => $kurdishTranslations[$name] ?? null,
                    ]
                );
            }
        }
    }

    /**
     * Create all default roles in database.
     */
    public function createDefaultRoles(): void
    {
        $allPermissions = Permission::all();

        foreach (self::getDefaultRoles() as $name => $config) {
            $role = Role::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                [
                    'name' => $name,
                    'name_ar' => $config['name_ar'],
                    'name_ckb' => $config['name_ckb'],
                    'guard_name' => 'web',
                    'description' => $config['description'],
                    'description_ar' => $config['description_ar'],
                    'description_ckb' => $config['description_ckb'],
                    'is_system' => $config['is_system'],
                ]
            );

            // Assign permissions
            $permissionsToAssign = collect();

            foreach ($config['permissions'] as $permission) {
                if ($permission === '*') {
                    $permissionsToAssign = $allPermissions;
                    break;
                } elseif (str_ends_with($permission, '.*')) {
                    $module = str_replace('.*', '', $permission);
                    $permissionsToAssign = $permissionsToAssign->merge(
                        $allPermissions->where('module', $module)
                    );
                } else {
                    $perm = $allPermissions->firstWhere('name', $permission);
                    if ($perm) {
                        $permissionsToAssign->push($perm);
                    }
                }
            }

            $role->syncPermissions($permissionsToAssign->unique('id')->pluck('id')->toArray());
        }
    }

    /**
     * Get permissions grouped by module for UI.
     */
    public function getPermissionsGroupedByModule(): Collection
    {
        return Permission::all()->groupBy('module');
    }

    /**
     * Expand wildcards in permission list.
     */
    public function expandPermissions(array $permissions): array
    {
        $allPermissions = self::getModulePermissions();
        $expanded = [];

        foreach ($permissions as $permission) {
            if ($permission === '*') {
                foreach ($allPermissions as $modulePermissions) {
                    $expanded = array_merge($expanded, array_keys($modulePermissions));
                }
            } elseif (str_ends_with($permission, '.*')) {
                $module = str_replace('.*', '', $permission);
                if (isset($allPermissions[$module])) {
                    $expanded = array_merge($expanded, array_keys($allPermissions[$module]));
                }
            } else {
                $expanded[] = $permission;
            }
        }

        return array_unique($expanded);
    }

    /**
     * Get localized permission name.
     */
    public static function getLocalizedPermissionName(string $permissionName): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => self::getArabicTranslations()[$permissionName] ?? $permissionName,
            'ckb' => self::getKurdishTranslations()[$permissionName] ?? self::getArabicTranslations()[$permissionName] ?? $permissionName,
            default => self::getModulePermissions()[explode('.', $permissionName)[0]][$permissionName] ?? $permissionName,
        };
    }
}
