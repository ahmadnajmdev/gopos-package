<?php

namespace Gopos\Services;

use Gopos\Database\Seeders\CurrencySeeder;
use Gopos\Database\Seeders\DemoSeeder;
use Gopos\Database\Seeders\RolesAndPermissionsSeeder;
use Gopos\Models\Role;
use Gopos\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class InstallationService
{
    /**
     * Check system requirements.
     */
    public function checkRequirements(): array
    {
        return [
            'php_version' => [
                'name' => 'PHP Version',
                'required' => '8.2.0',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '8.2.0', '>='),
            ],
            'extensions' => [
                'pdo' => [
                    'name' => 'PDO',
                    'passed' => extension_loaded('pdo'),
                ],
                'pdo_mysql' => [
                    'name' => 'PDO MySQL',
                    'passed' => extension_loaded('pdo_mysql'),
                ],
                'mbstring' => [
                    'name' => 'Mbstring',
                    'passed' => extension_loaded('mbstring'),
                ],
                'openssl' => [
                    'name' => 'OpenSSL',
                    'passed' => extension_loaded('openssl'),
                ],
                'tokenizer' => [
                    'name' => 'Tokenizer',
                    'passed' => extension_loaded('tokenizer'),
                ],
                'xml' => [
                    'name' => 'XML',
                    'passed' => extension_loaded('xml'),
                ],
                'ctype' => [
                    'name' => 'Ctype',
                    'passed' => extension_loaded('ctype'),
                ],
                'json' => [
                    'name' => 'JSON',
                    'passed' => extension_loaded('json'),
                ],
                'bcmath' => [
                    'name' => 'BCMath',
                    'passed' => extension_loaded('bcmath'),
                ],
                'fileinfo' => [
                    'name' => 'Fileinfo',
                    'passed' => extension_loaded('fileinfo'),
                ],
                'gd' => [
                    'name' => 'GD',
                    'passed' => extension_loaded('gd'),
                ],
            ],
            'directories' => [
                'storage/app' => [
                    'path' => 'storage/app',
                    'passed' => is_writable(storage_path('app')),
                ],
                'storage/framework' => [
                    'path' => 'storage/framework',
                    'passed' => is_writable(storage_path('framework')),
                ],
                'storage/logs' => [
                    'path' => 'storage/logs',
                    'passed' => is_writable(storage_path('logs')),
                ],
                'bootstrap/cache' => [
                    'path' => 'bootstrap/cache',
                    'passed' => is_writable(base_path('bootstrap/cache')),
                ],
            ],
        ];
    }

    /**
     * Check if all requirements pass.
     */
    public function allRequirementsPassed(): bool
    {
        $requirements = $this->checkRequirements();

        if (! $requirements['php_version']['passed']) {
            return false;
        }

        foreach ($requirements['extensions'] as $ext) {
            if (! $ext['passed']) {
                return false;
            }
        }

        foreach ($requirements['directories'] as $dir) {
            if (! $dir['passed']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Test database connection.
     */
    public function testDatabaseConnection(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'success' => true,
                'message' => 'Database connection successful',
                'driver' => DB::connection()->getDriverName(),
                'database' => DB::connection()->getDatabaseName(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if migrations have been run.
     */
    public function hasMigrations(): bool
    {
        try {
            return Schema::hasTable('migrations');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get available modules.
     */
    public function getAvailableModules(): array
    {
        return [
            'pos' => [
                'name' => 'POS Module',
                'name_ar' => 'نقطة البيع',
                'name_ckb' => 'سندوقی فرۆشتن',
                'description' => 'Point of Sale terminal and operations',
                'description_ar' => 'نقطة البيع والعمليات',
                'description_ckb' => 'سندوقی فرۆشتن و کارەکانی',
                'icon' => 'heroicon-o-shopping-cart',
                'default' => true,
            ],
            'inventory' => [
                'name' => 'Inventory Module',
                'name_ar' => 'المخزون',
                'name_ckb' => 'کۆگا',
                'description' => 'Stock management, warehouses, transfers',
                'description_ar' => 'إدارة المخزون والمستودعات والتحويلات',
                'description_ckb' => 'بەڕێوەبردنی کۆگا، مەخزەنەکان، گواستنەوەکان',
                'icon' => 'heroicon-o-cube',
                'default' => true,
            ],
            'sales' => [
                'name' => 'Sales Module',
                'name_ar' => 'المبيعات',
                'name_ckb' => 'فرۆشتن',
                'description' => 'Sales orders and customer management',
                'description_ar' => 'طلبات المبيعات وإدارة العملاء',
                'description_ckb' => 'داواکارییەکانی فرۆشتن و بەڕێوەبردنی کڕیار',
                'icon' => 'heroicon-o-banknotes',
                'default' => true,
            ],
            'purchases' => [
                'name' => 'Purchases Module',
                'name_ar' => 'المشتريات',
                'name_ckb' => 'کڕین',
                'description' => 'Purchase orders and supplier management',
                'description_ar' => 'طلبات الشراء وإدارة الموردين',
                'description_ckb' => 'داواکارییەکانی کڕین و بەڕێوەبردنی دابینکەر',
                'icon' => 'heroicon-o-truck',
                'default' => true,
            ],
            'customers' => [
                'name' => 'Customers Module',
                'name_ar' => 'العملاء',
                'name_ckb' => 'کڕیارەکان',
                'description' => 'Customer management and relationships',
                'description_ar' => 'إدارة العملاء والعلاقات',
                'description_ckb' => 'بەڕێوەبردنی کڕیار و پەیوەندییەکان',
                'icon' => 'heroicon-o-users',
                'default' => true,
            ],
            'suppliers' => [
                'name' => 'Suppliers Module',
                'name_ar' => 'الموردين',
                'name_ckb' => 'دابینکەرەکان',
                'description' => 'Supplier management',
                'description_ar' => 'إدارة الموردين',
                'description_ckb' => 'بەڕێوەبردنی دابینکەر',
                'icon' => 'heroicon-o-building-storefront',
                'default' => true,
            ],
            'accounting' => [
                'name' => 'Accounting Module',
                'name_ar' => 'المحاسبة',
                'name_ckb' => 'ژمێریاری',
                'description' => 'General ledger, journal entries, financial reports',
                'description_ar' => 'دفتر الأستاذ العام والقيود اليومية والتقارير المالية',
                'description_ckb' => 'دەفتەری گشتی، تۆمارەکانی ڕۆژانە، ڕاپۆرتە داراییەکان',
                'icon' => 'heroicon-o-calculator',
                'default' => false,
            ],
            'hr' => [
                'name' => 'HR Module',
                'name_ar' => 'الموارد البشرية',
                'name_ckb' => 'کەسایەتی',
                'description' => 'Employees, payroll, attendance, leave management',
                'description_ar' => 'الموظفين والرواتب والحضور وإدارة الإجازات',
                'description_ckb' => 'کارمەندان، مووچە، ئامادەبوون، بەڕێوەبردنی مۆڵەت',
                'icon' => 'heroicon-o-user-group',
                'default' => false,
            ],
            'reports' => [
                'name' => 'Reports Module',
                'name_ar' => 'التقارير',
                'name_ckb' => 'ڕاپۆرتەکان',
                'description' => 'Business analytics and reporting',
                'description_ar' => 'تحليلات الأعمال والتقارير',
                'description_ckb' => 'شیکاری بازرگانی و ڕاپۆرتەکان',
                'icon' => 'heroicon-o-chart-bar',
                'default' => true,
            ],
        ];
    }

    /**
     * Run database migrations.
     */
    public function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            return ['success' => true, 'output' => Artisan::output()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Seed required data.
     */
    public function seedData(bool $includeDemoData = false): array
    {
        try {
            // Always seed currencies
            Artisan::call('db:seed', [
                '--class' => CurrencySeeder::class,
                '--force' => true,
            ]);

            // Always seed roles and permissions
            Artisan::call('db:seed', [
                '--class' => RolesAndPermissionsSeeder::class,
                '--force' => true,
            ]);

            // Optionally seed demo data
            if ($includeDemoData) {
                Artisan::call('db:seed', [
                    '--class' => DemoSeeder::class,
                    '--force' => true,
                ]);
            }

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create admin user with super_admin role.
     */
    public function createAdminUser(array $data): array
    {
        try {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );

            // Assign super_admin role
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                $user->syncRoles([$superAdminRole]);
            }

            return ['success' => true, 'user' => $user];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Save business settings.
     */
    public function saveBusinessSettings(array $data): void
    {
        setting(['business.name' => $data['business_name'] ?? '']);
        setting(['business.currency_id' => $data['currency_id'] ?? 1]);
        setting(['business.address' => $data['address'] ?? '']);
        setting(['business.phone' => $data['phone'] ?? '']);
        setting(['business.email' => $data['email'] ?? '']);
    }

    /**
     * Save enabled modules.
     */
    public function saveEnabledModules(array $modules): void
    {
        setting(['modules.enabled' => $modules]);
    }

    /**
     * Get enabled modules.
     */
    public static function getEnabledModules(): array
    {
        return setting('modules.enabled', [
            'pos', 'inventory', 'sales', 'purchases',
            'customers', 'suppliers', 'reports',
        ]);
    }

    /**
     * Check if a module is enabled.
     */
    public static function isModuleEnabled(string $module): bool
    {
        $enabledModules = self::getEnabledModules();

        return in_array($module, $enabledModules);
    }

    /**
     * Mark installation as complete.
     */
    public function markAsInstalled(): void
    {
        $installedFile = storage_path('app/.installed');
        file_put_contents($installedFile, json_encode([
            'installed_at' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ]));
    }

    /**
     * Check if application is installed.
     */
    public static function isInstalled(): bool
    {
        return file_exists(storage_path('app/.installed'));
    }

    /**
     * Clear application caches.
     */
    public function clearCaches(): void
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
    }
}
