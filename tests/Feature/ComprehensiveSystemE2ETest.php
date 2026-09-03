<?php

namespace Tests\Feature;

use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\StockCount;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveSystemE2ETest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $workshopUser;
    protected Warehouse $warehouse;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::firstWhere('email', 'admin@hemin.krd');
        $this->workshopUser = User::firstWhere('email', 'kogha@hemin.krd');
        $this->warehouse = Warehouse::firstWhere('is_default', true) ?? Warehouse::first();
        $this->unit = Unit::first() ?? Unit::create(['name' => 'دانە']);

        // Setup default settings
        Setting::put('company_name', 'کارگەی ئاسنگەری هێمن');
        Setting::put('default_currency', 'IQD');
    }

    /**
     * ١. پشکنینی ڕێڕەوەکانی بەڕێوەبەر (Admin Routes Access)
     */
    public function test_admin_can_access_all_core_routes()
    {
        $this->actingAs($this->admin);

        $routes = [
            '/',
            '/orders',
            '/orders/create',
            '/customers',
            '/purchases',
            '/purchases/create',
            '/suppliers',
            '/cash',
            '/debts',
            '/statement',
            '/counts',
            '/warehouses',
            '/employees',
            '/attendance',
            '/settings',
            '/reports',
            '/reports/sales',
            '/reports/purchases',
            '/reports/profit',
            '/reports/stock',
            '/reports/cash',
            '/reports/workshop_production',
            '/reports/workshop_materials',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 302]),
                "Admin failed to access route: {$route} (Status: {$response->getStatusCode()})"
            );
        }
    }

    /**
     * ٢. پشکنینی ڕێڕەوەکانی وەستا (Workshop User Access)
     */
    public function test_workshop_user_access_permissions()
    {
        $this->actingAs($this->workshopUser);

        // دەبێت مۆڵەتی بەشی کارگە، دروستکردن، ئامادەباشی و کۆگای هەبێت
        $workshopRoutes = [
            '/workshop/orders',
            '/workshop/materials',
            '/warehouses',
        ];

        foreach ($workshopRoutes as $route) {
            $response = $this->get($route);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 302]),
                "Workshop user failed to access: {$route} (Status: {$response->getStatusCode()})"
            );
        }

        // بەڵام نابێت ڕێگەی پێبدرێت دەستی بگاتە ڕێکخستنی سیستەم و قاسەی پارە
        $forbiddenRoutes = [
            '/settings',
            '/cash',
        ];

        foreach ($forbiddenRoutes as $route) {
            $response = $this->get($route);
            $this->assertEquals(
                403,
                $response->getStatusCode(),
                "Workshop user should be forbidden from accessing: {$route}"
            );
        }
    }

    /**
     * ٣. سووڕی تەواوی فرۆشتن لە کڕیارەوە تا قەرز و قاسە و کەشف حیساب
     * Order -> Partial Payment -> Debt -> Cash -> Statement
     */
    public function test_full_sales_debt_cash_statement_integration()
    {
        $this->actingAs($this->admin);

        // دروستکردنی کڕیار
        $customer = Customer::create([
            'name' => 'ئارام مەحموود',
            'phone' => '07501112233',
            'opening_debt' => 50000, // قەرزی سەرەتایی 50,000 د.ع
        ]);

        // کڕینی وەسڵێک بە بڕی 150,000 د.ع
        // دراو: 100,000 د.ع، ماوە: 50,000 د.ع
        $orderData = [
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'prepaid_amount' => 100000,
            'currency' => 'IQD',
            'confirm' => '1',
            'lines' => [
                [
                    'description' => 'دەرگای حەوشە',
                    'calc_mode' => 'meter',
                    'width' => 2.0,
                    'height' => 2.5,
                    'meter' => 5.0,
                    'meter_price' => 30000,
                    'unit_price' => 150000,
                    'line_total' => 150000,
                ]
            ]
        ];

        $response = $this->post(route('orders.store'), $orderData);
        $response->assertRedirect();

        $order = Order::where('customer_id', $customer->id)->first();
        $this->assertNotNull($order, 'وەسڵ دروست نەکرا.');
        $this->assertEquals(150000, (float) $order->total_iqd, 'کۆی وەسڵ هەڵەیە.');
        $this->assertEquals(100000, (float) $order->paidAmount(), 'بڕی دراو لە وەسڵ هەڵەیە.');
        $this->assertEquals(50000, (float) $order->remaining(), 'بڕی ماوە لە وەسڵ هەڵەیە.');

        // پشکنینی پەیوەندی بە قەرز: قەرزی کۆن (50,000) + قەرزی ئەم وەسڵە (50,000) = 100,000 د.ع
        $customer->refresh();
        $expectedTotalDebt = 100000.0;
        $this->assertEquals($expectedTotalDebt, (float) $customer->totalDebtIqd(), 'کۆی قەرزی کڕیار بە دروستی هەژمار نەکراوە.');

        // پشکنینی تۆماربوونی پارەکە لە حەقدی و قاسە (Payment & Cash Ledger)
        $orderPayment = Payment::where('direction', 'in')
            ->where('order_id', $order->id)
            ->first();
        $this->assertNotNull($orderPayment, 'پێشەکی وەسڵ تۆمار نەکراوە.');
        $this->assertEquals(100000, (float) $orderPayment->amount, 'بڕی پێشەکی هەڵەیە.');

        $cashPayment = CashTransaction::where('direction', 'in')
            ->where('reference_type', Payment::class)
            ->where('reference_id', $orderPayment->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($cashPayment, 'پارەی حەقدی لە قاسە تۆمار نەکراوە.');
        $this->assertEquals(100000, (float) $cashPayment->amount, 'بڕی پارەی چووەتە قاسە هەڵەیە.');

        // پشکنینی کەشف حیسابی کڕیار
        $stmtResponse = $this->get(route('customers.statement', $customer));
        $stmtResponse->assertStatus(200);
        $stmtResponse->assertSee('ئارام مەحموود');

        // کڕیار قەرزەکەی دەداتەوە: 50,000 د.ع
        $repayResponse = $this->post(route('payments.store'), [
            'customer_id' => $customer->id,
            'direction' => 'in',
            'amount' => 50000,
            'currency' => 'IQD',
            'paid_at' => now()->toDateString(),
            'note' => 'دانەوەی بەشێک لە قەرز',
        ]);
        $repayResponse->assertRedirect();

        // ئێستا دەبێت کۆی قەرزی کڕیار 50,000 د.ع بێت
        $customer->refresh();
        $this->assertEquals(50000, (float) $customer->totalDebtIqd(), 'پاش دانەوەی قەرز ماوە ڕاست نییە.');
    }

    /**
     * ٤. سووڕی کڕین لە دابینکەر و قەرز و قاسە
     * Purchase -> Cash Out -> Supplier Debt
     */
    public function test_purchase_supplier_cash_integration()
    {
        $this->actingAs($this->admin);

        // دروستکردنی دابینکەر
        $supplier = Supplier::create([
            'name' => 'کۆمپانیای شیش و ئاسن',
            'phone' => '07509998877',
        ]);

        // کڕینی مەواد بە 200,000 د.ع، دراو: 80,000 د.ع، قەرز: 120,000 د.ع
        $purchaseData = [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'paid_amount' => 80000,
            'currency' => 'IQD',
            'quick_total' => 200000,
        ];

        $response = $this->post(route('purchases.store'), $purchaseData);
        $response->assertRedirect();

        $purchase = Purchase::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($purchase, 'کڕین تۆمار نەکرا.');
        $this->assertEquals(200000, (float) $purchase->total_iqd, 'کۆی کڕین هەڵەیە.');
        $this->assertEquals(80000, (float) $purchase->paidTotal(), 'بڕی دراوی کڕین هەڵەیە.');
        $this->assertEquals(120000, (float) $purchase->remaining(), 'قەرزی ماوەی کڕین هەڵەیە.');

        // پشکنینی دەرچوونی پارە لە حەقدی و قاسە (Payment & Cash Out)
        $purchasePayment = Payment::where('direction', 'out')
            ->where('purchase_id', $purchase->id)
            ->first();
        $this->assertNotNull($purchasePayment, 'حەقدی دەرچوو بۆ کڕین تۆمار نەکراوە.');
        $this->assertEquals(80000, (float) $purchasePayment->amount, 'بڕی پارەی دراوی کڕین هەڵەیە.');

        $cashOut = CashTransaction::where('direction', 'out')
            ->where('reference_type', Payment::class)
            ->where('reference_id', $purchasePayment->id)
            ->first();
        $this->assertNotNull($cashOut, 'پارەی دەرچوو لە قاسە بۆ کڕین تۆمار نەکراوە.');
        $this->assertEquals(80000, (float) $cashOut->amount, 'بڕی دەرچووی قاسە هەڵەیە.');
    }

    /**
     * ٥. پشکنینی جەردی کۆگا و ڕێکخستنەوەی کاڵاکان
     */
    public function test_stock_count_and_warehouse_adjustment()
    {
        $this->actingAs($this->admin);

        // دروستکردنی کاڵایەک
        $item = Item::create([
            'name' => 'بۆری ئاسن ۲ ئینج',
            'unit_id' => $this->unit->id,
            'is_active' => true,
            'is_for_sale' => true,
            'sale_price' => 15000,
            'last_cost' => 10000,
            'min_qty' => 5,
        ]);

        // دەستپێکردنی جەردی نوێ
        $countResponse = $this->post(route('counts.store'), [
            'warehouse_id' => $this->warehouse->id,
            'count_date' => now()->toDateString(),
            'note' => 'جەردی تێست',
        ]);
        $countResponse->assertRedirect();

        $count = StockCount::latest('id')->first();
        $this->assertNotNull($count, 'جەرد دروست نەکرا.');
        $countItem = $count->items->firstWhere('item_id', $item->id);
        $this->assertNotNull($countItem, 'کاڵا لە جەردەکەدا نییە.');

        // ژماردن: کاڵاکە 12 دانەی لێیە بە نرخی 10,000 د.ع
        $updateResponse = $this->put(route('counts.update', $count), [
            'counted' => [$countItem->id => 12],
            'unit_price' => [$countItem->id => 10000],
        ]);
        $updateResponse->assertRedirect();

        $countItem->refresh();
        $this->assertEquals(12, (float) $countItem->counted_qty);
        $this->assertEquals(12, (float) $countItem->difference); // 12 - 0 = 12

        // پەسەندکردنی جەردەکە
        $postResponse = $this->post(route('counts.post', $count));
        $postResponse->assertRedirect();

        $count->refresh();
        $this->assertEquals('posted', $count->status, 'دۆخی جەرد نەکرا بە posted.');

        // پشکنینی ئەوەی کۆگا ڕاستکراوەتەوە بۆ 12 دانە
        $this->assertEquals(12, (float) $item->stockQty($this->warehouse->id), 'کۆگا پاش پەسەندکردن ڕاست نەکراوەتەوە.');
    }

    /**
     * ٦. پشکنینی باکەپ و سڕینەوە و خزمەتگوزاری
     */
    public function test_backup_service_listing_and_deletion()
    {
        $this->actingAs($this->admin);

        $backupService = app(BackupService::class);
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $dummyFile = 'auto-hemin-' . now()->format('Y-m-d_His') . '.sql';

        $disk->put('backups/' . $dummyFile, '-- dummy backup test content');

        $this->assertFileExists($backupService->path($dummyFile));
        $this->assertStringStartsWith('auto-hemin-', $dummyFile);

        $list = $backupService->list();
        $this->assertNotEmpty($list);

        // سڕینەوە
        $deleted = $backupService->delete($dummyFile);
        $this->assertTrue($deleted);
    }
}
