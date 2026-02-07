<?php

namespace Gopos\Services;

use Gopos\Models\Account;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsService
{
    /**
     * Create default chart of accounts
     */
    public function createDefaultAccounts(): void
    {
        DB::transaction(function () {
            $accounts = $this->getDefaultAccountsStructure();

            foreach ($accounts as $accountData) {
                $this->createAccount($accountData);
            }
        });
    }

    /**
     * Create a single account
     */
    protected function createAccount(array $data, ?int $parentId = null): Account
    {
        $account = Account::create([
            'account_type_id' => $data['type_id'],
            'parent_id' => $parentId,
            'code' => $data['code'],
            'name' => $data['name'],
            'name_ar' => $data['name_ar'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
            'is_system' => $data['is_system'] ?? false,
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);

        // Create child accounts if any
        if (! empty($data['children'])) {
            foreach ($data['children'] as $childData) {
                $this->createAccount($childData, $account->id);
            }
        }

        return $account;
    }

    /**
     * Get default accounts structure
     */
    protected function getDefaultAccountsStructure(): array
    {
        return [
            // Assets (1xxx)
            [
                'type_id' => 1, // Asset
                'code' => '1100',
                'name' => 'Cash',
                'name_ar' => 'النقدية',
                'is_system' => true,
                'children' => [
                    [
                        'type_id' => 1,
                        'code' => '1101',
                        'name' => 'Cash in Hand (IQD)',
                        'name_ar' => 'النقدية بالدينار',
                    ],
                    [
                        'type_id' => 1,
                        'code' => '1102',
                        'name' => 'Cash in Hand (USD)',
                        'name_ar' => 'النقدية بالدولار',
                    ],
                ],
            ],
            [
                'type_id' => 1,
                'code' => '1200',
                'name' => 'Bank Accounts',
                'name_ar' => 'الحسابات البنكية',
                'is_system' => true,
            ],
            [
                'type_id' => 1,
                'code' => '1300',
                'name' => 'Accounts Receivable',
                'name_ar' => 'الذمم المدينة',
                'is_system' => true,
            ],
            [
                'type_id' => 1,
                'code' => '1400',
                'name' => 'Inventory',
                'name_ar' => 'المخزون',
                'is_system' => true,
            ],
            [
                'type_id' => 1,
                'code' => '1500',
                'name' => 'Tax Receivable',
                'name_ar' => 'ضرائب مستحقة القبض',
                'is_system' => true,
            ],
            [
                'type_id' => 1,
                'code' => '1600',
                'name' => 'Prepaid Expenses',
                'name_ar' => 'المصروفات المدفوعة مقدماً',
            ],
            [
                'type_id' => 1,
                'code' => '1700',
                'name' => 'Fixed Assets',
                'name_ar' => 'الأصول الثابتة',
                'children' => [
                    [
                        'type_id' => 1,
                        'code' => '1701',
                        'name' => 'Furniture & Equipment',
                        'name_ar' => 'الأثاث والمعدات',
                    ],
                    [
                        'type_id' => 1,
                        'code' => '1702',
                        'name' => 'Vehicles',
                        'name_ar' => 'السيارات',
                    ],
                ],
            ],

            // Liabilities (2xxx)
            [
                'type_id' => 2, // Liability
                'code' => '2100',
                'name' => 'Accounts Payable',
                'name_ar' => 'الذمم الدائنة',
                'is_system' => true,
            ],
            [
                'type_id' => 2,
                'code' => '2200',
                'name' => 'Tax Payable',
                'name_ar' => 'الضرائب المستحقة',
                'is_system' => true,
                'children' => [
                    [
                        'type_id' => 2,
                        'code' => '2201',
                        'name' => 'Sales Tax Payable',
                        'name_ar' => 'ضريبة المبيعات المستحقة',
                    ],
                    [
                        'type_id' => 2,
                        'code' => '2202',
                        'name' => 'Withholding Tax Payable',
                        'name_ar' => 'ضريبة الاستقطاع المستحقة',
                    ],
                ],
            ],
            [
                'type_id' => 2,
                'code' => '2300',
                'name' => 'Accrued Expenses',
                'name_ar' => 'المصروفات المستحقة',
            ],
            [
                'type_id' => 2,
                'code' => '2400',
                'name' => 'Loans Payable',
                'name_ar' => 'القروض المستحقة',
            ],

            // Equity (3xxx)
            [
                'type_id' => 3, // Equity
                'code' => '3100',
                'name' => 'Owner\'s Capital',
                'name_ar' => 'رأس المال',
                'is_system' => true,
            ],
            [
                'type_id' => 3,
                'code' => '3200',
                'name' => 'Retained Earnings',
                'name_ar' => 'الأرباح المحتجزة',
                'is_system' => true,
            ],
            [
                'type_id' => 3,
                'code' => '3300',
                'name' => 'Drawings',
                'name_ar' => 'المسحوبات الشخصية',
            ],

            // Revenue (4xxx)
            [
                'type_id' => 4, // Revenue
                'code' => '4100',
                'name' => 'Sales Revenue',
                'name_ar' => 'إيرادات المبيعات',
                'is_system' => true,
            ],
            [
                'type_id' => 4,
                'code' => '4200',
                'name' => 'Other Income',
                'name_ar' => 'إيرادات أخرى',
                'is_system' => true,
            ],
            [
                'type_id' => 4,
                'code' => '4300',
                'name' => 'Sales Discounts',
                'name_ar' => 'خصومات المبيعات',
            ],
            [
                'type_id' => 4,
                'code' => '4400',
                'name' => 'Sales Returns',
                'name_ar' => 'مردودات المبيعات',
            ],

            // Expenses (5xxx)
            [
                'type_id' => 5, // Expense
                'code' => '5100',
                'name' => 'Cost of Goods Sold',
                'name_ar' => 'تكلفة البضاعة المباعة',
                'is_system' => true,
            ],
            [
                'type_id' => 5,
                'code' => '5200',
                'name' => 'Operating Expenses',
                'name_ar' => 'المصروفات التشغيلية',
                'is_system' => true,
                'children' => [
                    [
                        'type_id' => 5,
                        'code' => '5201',
                        'name' => 'Salaries & Wages',
                        'name_ar' => 'الرواتب والأجور',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5202',
                        'name' => 'Rent Expense',
                        'name_ar' => 'مصروف الإيجار',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5203',
                        'name' => 'Utilities',
                        'name_ar' => 'المرافق',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5204',
                        'name' => 'Transportation',
                        'name_ar' => 'النقل والمواصلات',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5205',
                        'name' => 'Marketing & Advertising',
                        'name_ar' => 'التسويق والإعلان',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5206',
                        'name' => 'Office Supplies',
                        'name_ar' => 'مستلزمات المكتب',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5207',
                        'name' => 'Communication',
                        'name_ar' => 'الاتصالات',
                    ],
                ],
            ],
            [
                'type_id' => 5,
                'code' => '5300',
                'name' => 'Tax Expense',
                'name_ar' => 'مصروف الضرائب',
            ],
            [
                'type_id' => 5,
                'code' => '5400',
                'name' => 'Depreciation Expense',
                'name_ar' => 'مصروف الإهلاك',
            ],
            [
                'type_id' => 5,
                'code' => '5500',
                'name' => 'Bank Charges',
                'name_ar' => 'العمولات البنكية',
            ],
        ];
    }

    /**
     * Get account tree structure
     */
    public function getAccountTree(): array
    {
        $accounts = Account::whereNull('parent_id')
            ->with('children.children')
            ->orderBy('code')
            ->get();

        return $accounts->toArray();
    }

    /**
     * Generate next account code
     */
    public function generateAccountCode(int $typeId, ?int $parentId = null): string
    {
        if ($parentId) {
            $parent = Account::find($parentId);
            $lastChild = Account::where('parent_id', $parentId)
                ->orderBy('code', 'desc')
                ->first();

            if ($lastChild) {
                $lastNumber = (int) substr($lastChild->code, -2);

                return $parent->code.str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            }

            return $parent->code.'01';
        }

        // Root level account
        $prefixMap = [
            1 => '1', // Asset
            2 => '2', // Liability
            3 => '3', // Equity
            4 => '4', // Revenue
            5 => '5', // Expense
        ];

        $prefix = $prefixMap[$typeId] ?? '9';

        $lastAccount = Account::where('account_type_id', $typeId)
            ->whereNull('parent_id')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastAccount) {
            $lastNumber = (int) substr($lastAccount->code, 1);

            return $prefix.str_pad($lastNumber + 100, 3, '0', STR_PAD_LEFT);
        }

        return $prefix.'100';
    }
}
