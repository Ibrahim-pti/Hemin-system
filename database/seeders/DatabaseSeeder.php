<?php

namespace Database\Seeders;

use App\Models\CashBox;
use App\Models\ExchangeRate;
use App\Models\ItemCategory;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /** هەموو مۆڵەتەکانی سیستەم لەگەڵ ناوی کوردییان. */
    public const PERMISSIONS = [
        'view_stock' => 'بینینی مەخزەن',
        'manage_stock' => 'زیادکردن و کەمکردنی مەخزەن',
        'manage_items' => 'بەڕێوەبردنی کاڵا و کۆگا',
        'manage_stock_counts' => 'جەردی کۆگا',
        'manage_suppliers' => 'بەڕێوەبردنی فرۆشیار',
        'manage_purchases' => 'بەڕێوەبردنی کڕین',
        'manage_customers' => 'بەڕێوەبردنی کڕیار',
        'manage_orders' => 'وەسڵ و داواکاری',
        'manage_payments' => 'حەقدی و پارە',
        'manage_cash' => 'قاسە',
        'manage_employees' => 'کارمەند و ئامادەبوون',
        'manage_external_jobs' => 'ئیشی خاریجی',
        'view_reports' => 'راپۆرتەکان',
        'manage_users' => 'بەکارهێنەران',
        'manage_settings' => 'ڕێکخستن و باکەپ',
    ];

    /** بەرپرسی کۆگا تەنها ئەمانەی هەیە — هیچ نرخێک نابینێت. */
    public const STOREKEEPER_PERMISSIONS = [
        'view_stock',
        'manage_stock',
        'manage_items',
        'manage_stock_counts',
    ];

    public function run(): void
    {
        $this->seedRoles();
        $this->seedUsers();
        $this->seedSettings();
        $this->seedBaseData();
    }

    private function seedRoles(): void
    {
        foreach (array_keys(self::PERMISSIONS) as $name) {
            Permission::findOrCreate($name);
        }

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions(array_keys(self::PERMISSIONS));

        $storekeeper = Role::findOrCreate('storekeeper');
        $storekeeper->syncPermissions(self::STOREKEEPER_PERMISSIONS);
    }

    private function seedUsers(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hemin.krd'],
            ['name' => 'بەڕێوەبەر', 'password' => 'hemin1234', 'is_active' => true],
        );
        $admin->syncRoles(['admin']);

        $storekeeper = User::firstOrCreate(
            ['email' => 'kogha@hemin.krd'],
            ['name' => 'بەرپرسی کۆگا', 'password' => 'kogha1234', 'is_active' => true],
        );
        $storekeeper->syncRoles(['storekeeper']);
    }

    private function seedSettings(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedBaseData(): void
    {
        // یەکەکانی باو لە کارگەی ئاسنگەری.
        $units = [
            ['name' => 'دانە', 'code' => 'pc', 'type' => 'count'],
            ['name' => 'مەتر', 'code' => 'm', 'type' => 'length'],
            ['name' => 'مەتر دووجا', 'code' => 'm2', 'type' => 'area'],
            ['name' => 'کیلۆگرام', 'code' => 'kg', 'type' => 'weight'],
            ['name' => 'تەن', 'code' => 't', 'type' => 'weight'],
            ['name' => 'لوولە', 'code' => 'pipe', 'type' => 'count'],
            ['name' => 'سەفیحە', 'code' => 'sheet', 'type' => 'count'],
            ['name' => 'قوتوو', 'code' => 'box', 'type' => 'count'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit['name']], $unit);
        }

        // جۆرەکانی مەوادی ئاسنگەری.
        foreach ([
            'ئاسن و لوولە',
            'سەفیحە',
            'ئێکسسوار و قوفڵ',
            'بۆیاخ و ماددەی کیمیاوی',
            'ئامێر و ئامرازی کار',
            'هیتر',
        ] as $category) {
            ItemCategory::firstOrCreate(['name' => $category]);
        }

        Warehouse::firstOrCreate(
            ['name' => 'کۆگای سەرەکی'],
            ['is_default' => true, 'is_active' => true],
        );

        CashBox::firstOrCreate(['currency' => 'IQD'], ['name' => 'قاسەی دینار', 'is_active' => true]);
        CashBox::firstOrCreate(['currency' => 'USD'], ['name' => 'قاسەی دۆلار', 'is_active' => true]);

        // نرخی دەستپێک — دواتر لە ڕێکخستنەوە دەگۆڕدرێت.
        ExchangeRate::firstOrCreate(
            ['effective_date' => now()->toDateString()],
            ['usd_to_iqd' => 1450],
        );
    }
}
