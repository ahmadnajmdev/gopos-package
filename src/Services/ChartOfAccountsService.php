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
            'name_ckb' => $data['name_ckb'] ?? null,
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
                'name_ckb' => 'نەقد',
                'is_system' => true,
                'children' => [
                    [
                        'type_id' => 1,
                        'code' => '1101',
                        'name' => 'Cash in Hand (IQD)',
                        'name_ar' => 'النقدية بالدينار',
                        'name_ckb' => 'نەقدی دینار',
                    ],
                    [
                        'type_id' => 1,
                        'code' => '1102',
                        'name' => 'Cash in Hand (USD)',
                        'name_ar' => 'النقدية بالدولار',
                        'name_ckb' => 'نەقدی دۆلار',
                    ],
                ],
            ],
            [
                'type_id' => 1,
                'code' => '1200',
                'name' => 'Bank Accounts',
                'name_ar' => 'الحسابات البنكية',
                'name_ckb' => 'هەژمارە بانکیەکان',
                'is_system' => true,
            ],
            [
                'type_id' => 1,
                'code' => '1300',
                'name' => 'Accounts Receivable',
                'name_ar' => 'الذمم المدينة',
                'name_ckb' => 'قەرزی وەرگرتن',
                'is_system' => true,
            ],
            [
                'type_id' => 1,
                'code' => '1400',
                'name' => 'Inventory',
                'name_ar' => 'المخزون',
                'name_ckb' => 'بڕ',
                'is_system' => true,
            ],
            [
                'type_id' => 1,
                'code' => '1500',
                'name' => 'Tax Receivable',
                'name_ar' => 'ضرائب مستحقة القبض',
                'name_ckb' => 'باجی وەرگرتن',
                'is_system' => true,
            ],
            [
                'type_id' => 1,
                'code' => '1600',
                'name' => 'Prepaid Expenses',
                'name_ar' => 'المصروفات المدفوعة مقدماً',
                'name_ckb' => 'خەرجی پێشوەخت',
            ],
            [
                'type_id' => 1,
                'code' => '1700',
                'name' => 'Fixed Assets',
                'name_ar' => 'الأصول الثابتة',
                'name_ckb' => 'دارایی جێگیر',
                'children' => [
                    [
                        'type_id' => 1,
                        'code' => '1701',
                        'name' => 'Furniture & Equipment',
                        'name_ar' => 'الأثاث والمعدات',
                        'name_ckb' => 'کەلوپەل و ئامێرەکان',
                    ],
                    [
                        'type_id' => 1,
                        'code' => '1702',
                        'name' => 'Vehicles',
                        'name_ar' => 'السيارات',
                        'name_ckb' => 'ئۆتۆمبێلەکان',
                    ],
                ],
            ],

            // Liabilities (2xxx)
            [
                'type_id' => 2, // Liability
                'code' => '2100',
                'name' => 'Accounts Payable',
                'name_ar' => 'الذمم الدائنة',
                'name_ckb' => 'قەرزی دانەوە',
                'is_system' => true,
            ],
            [
                'type_id' => 2,
                'code' => '2200',
                'name' => 'Tax Payable',
                'name_ar' => 'الضرائب المستحقة',
                'name_ckb' => 'باجی دانەوە',
                'is_system' => true,
                'children' => [
                    [
                        'type_id' => 2,
                        'code' => '2201',
                        'name' => 'Sales Tax Payable',
                        'name_ar' => 'ضريبة المبيعات المستحقة',
                        'name_ckb' => 'باجی فرۆشتنی دانەوە',
                    ],
                    [
                        'type_id' => 2,
                        'code' => '2202',
                        'name' => 'Withholding Tax Payable',
                        'name_ar' => 'ضريبة الاستقطاع المستحقة',
                        'name_ckb' => 'باجی گرتنەوەی دانەوە',
                    ],
                ],
            ],
            [
                'type_id' => 2,
                'code' => '2300',
                'name' => 'Accrued Expenses',
                'name_ar' => 'المصروفات المستحقة',
                'name_ckb' => 'خەرجی کۆبووەوە',
            ],
            [
                'type_id' => 2,
                'code' => '2400',
                'name' => 'Loans Payable',
                'name_ar' => 'القروض المستحقة',
                'name_ckb' => 'قەرزەکانی دانەوە',
            ],

            // Equity (3xxx)
            [
                'type_id' => 3, // Equity
                'code' => '3100',
                'name' => 'Owner\'s Capital',
                'name_ar' => 'رأس المال',
                'name_ckb' => 'سەرمایەی خاوەن',
                'is_system' => true,
            ],
            [
                'type_id' => 3,
                'code' => '3200',
                'name' => 'Retained Earnings',
                'name_ar' => 'الأرباح المحتجزة',
                'name_ckb' => 'قازانجی پاشەکەوتکراو',
                'is_system' => true,
            ],
            [
                'type_id' => 3,
                'code' => '3300',
                'name' => 'Drawings',
                'name_ar' => 'المسحوبات الشخصية',
                'name_ckb' => 'دەرهێنانی تایبەتی',
            ],

            // Revenue (4xxx)
            [
                'type_id' => 4, // Revenue
                'code' => '4100',
                'name' => 'Sales Revenue',
                'name_ar' => 'إيرادات المبيعات',
                'name_ckb' => 'داهاتی فرۆشتن',
                'is_system' => true,
            ],
            [
                'type_id' => 4,
                'code' => '4200',
                'name' => 'Other Income',
                'name_ar' => 'إيرادات أخرى',
                'name_ckb' => 'داهاتی تر',
                'is_system' => true,
            ],
            [
                'type_id' => 4,
                'code' => '4300',
                'name' => 'Sales Discounts',
                'name_ar' => 'خصومات المبيعات',
                'name_ckb' => 'داشکاندنی فرۆشتن',
            ],
            [
                'type_id' => 4,
                'code' => '4400',
                'name' => 'Sales Returns',
                'name_ar' => 'مردودات المبيعات',
                'name_ckb' => 'گەڕاندنەوەی فرۆشتن',
            ],

            // Expenses (5xxx)
            [
                'type_id' => 5, // Expense
                'code' => '5100',
                'name' => 'Cost of Goods Sold',
                'name_ar' => 'تكلفة البضاعة المباعة',
                'name_ckb' => 'تێچووی کاڵای فرۆشراو',
                'is_system' => true,
            ],
            [
                'type_id' => 5,
                'code' => '5200',
                'name' => 'Operating Expenses',
                'name_ar' => 'المصروفات التشغيلية',
                'name_ckb' => 'خەرجی کارپێکردن',
                'is_system' => true,
                'children' => [
                    [
                        'type_id' => 5,
                        'code' => '5201',
                        'name' => 'Salaries & Wages',
                        'name_ar' => 'الرواتب والأجور',
                        'name_ckb' => 'مووچە و کرێ',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5202',
                        'name' => 'Rent Expense',
                        'name_ar' => 'مصروف الإيجار',
                        'name_ckb' => 'خەرجی کرێ',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5203',
                        'name' => 'Utilities',
                        'name_ar' => 'المرافق',
                        'name_ckb' => 'خزمەتگوزاریەکان',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5204',
                        'name' => 'Transportation',
                        'name_ar' => 'النقل والمواصلات',
                        'name_ckb' => 'گواستنەوە',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5205',
                        'name' => 'Marketing & Advertising',
                        'name_ar' => 'التسويق والإعلان',
                        'name_ckb' => 'بازاڕکردن و ڕیکلام',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5206',
                        'name' => 'Office Supplies',
                        'name_ar' => 'مستلزمات المكتب',
                        'name_ckb' => 'پێداویستی نووسینگە',
                    ],
                    [
                        'type_id' => 5,
                        'code' => '5207',
                        'name' => 'Communication',
                        'name_ar' => 'الاتصالات',
                        'name_ckb' => 'پەیوەندیەکان',
                    ],
                ],
            ],
            [
                'type_id' => 5,
                'code' => '5300',
                'name' => 'Tax Expense',
                'name_ar' => 'مصروف الضرائب',
                'name_ckb' => 'خەرجی باج',
            ],
            [
                'type_id' => 5,
                'code' => '5400',
                'name' => 'Depreciation Expense',
                'name_ar' => 'مصروف الإهلاك',
                'name_ckb' => 'خەرجی کەمبوونەوە',
            ],
            [
                'type_id' => 5,
                'code' => '5500',
                'name' => 'Bank Charges',
                'name_ar' => 'العمولات البنكية',
                'name_ckb' => 'کرێی بانک',
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
