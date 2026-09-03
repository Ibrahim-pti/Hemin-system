<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $storekeeper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        \App\Models\Setting::set('workshop_weekly_holiday', 'none');
        $this->admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->storekeeper = User::where('email', 'kogha@hemin.krd')->firstOrFail();
    }

    public function test_admin_can_create_order_and_change_status()
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'name' => 'کڕیاری تێست',
            'phone' => '07501234567',
        ]);

        $postData = [
            'invoice_no' => 'INV-9999',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'currency' => 'IQD',
            'discount_percent' => 0,
            'prepaid_amount' => 50000,
            'lines' => [
                [
                    'description' => 'دەرگای ئاسن 2x1',
                    'unit_price' => 150000,
                ],
            ],
            'confirm' => 1,
        ];

        $response = $this->post('/orders', $postData);
        $response->assertRedirect();

        $order = Order::where('invoice_no', 'INV-9999')->firstOrFail();
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals(150000, (float) $order->total);
        $this->assertEquals(50000, (float) $order->prepaid_amount);

        // Storekeeper / Wasta changes status to in_production
        $this->actingAs($this->storekeeper);
        $statusResponse = $this->post("/workshop/orders/{$order->id}/status", [
            'status' => 'in_production',
        ]);
        $statusResponse->assertStatus(302);
        $this->assertEquals('in_production', $order->fresh()->status);

        // Change status to ready
        $this->post("/workshop/orders/{$order->id}/status", [
            'status' => 'ready',
        ]);
        $this->assertEquals('ready', $order->fresh()->status);
    }

    public function test_admin_can_create_purchase_and_update_stock()
    {
        $this->actingAs($this->admin);

        $supplier = Supplier::create([
            'name' => 'کۆمپانیای ئاسن',
            'phone' => '07701234567',
        ]);

        $warehouse = Warehouse::firstOrFail();

        $postData = [
            'supplier_name' => $supplier->name,
            'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'currency' => 'IQD',
            'discount_amount' => 0,
            'paid_amount' => 100000,
            'lines' => [
                [
                    'item_name' => 'بۆری ئاسن 3x3',
                    'qty' => 10,
                    'unit_price' => 20000,
                ],
            ],
            'confirm' => 1,
        ];

        $response = $this->post('/purchases', $postData);
        $response->assertRedirect();

        $purchase = Purchase::where('supplier_id', $supplier->id)->firstOrFail();
        $this->assertEquals('confirmed', $purchase->status);
        $this->assertEquals(200000, (float) $purchase->total);

        $item = Item::where('name', 'بۆری ئاسن 3x3')->firstOrFail();
        $this->assertEquals(10, (float) $item->stockQty($warehouse->id));
    }

    public function test_workshop_materials_stock_in_and_out()
    {
        $this->actingAs($this->storekeeper);

        $warehouse = Warehouse::firstOrFail();

        // Store new raw material
        $response = $this->post('/workshop/materials', [
            'name' => 'لوولەی 2 اینچ',
            'warehouse_id' => $warehouse->id,
            'new_unit_name' => 'لوولە',
            'initial_qty' => 25,
            'date' => now()->toDateString(),
        ]);
        $response->assertRedirect();

        $item = Item::where('name', 'لوولەی 2 اینچ')->firstOrFail();
        $this->assertEquals(25, (float) $item->stockQty($warehouse->id));

        // Stock in additional 10
        $resIn = $this->post('/workshop/stock-in', [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'qty' => 10,
            'date' => now()->toDateString(),
        ]);
        $resIn->assertStatus(302);
        $resIn->assertSessionHasNoErrors();
        $this->assertEquals(35, (float) Item::find($item->id)->stockQty($warehouse->id));

        // Stock out (use in production) 5
        $resOut = $this->post('/workshop/stock-out', [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'qty' => 5,
            'date' => now()->toDateString(),
        ]);
        $resOut->assertSessionHasNoErrors();
        $this->assertEquals(30, (float) $item->fresh()->stockQty($warehouse->id));
    }

    public function test_workshop_employee_quick_store_and_attendance()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/workshop/employees/quick-store', [
            'name' => 'وەستا کامەران',
            'phone' => '07509876543',
            'job_title' => 'master',
            'salary_type' => 'daily',
            'daily_wage' => 35000,
            'wage_currency' => 'IQD',
        ]);
        $response->assertRedirect();

        $employee = Employee::where('name', 'وەستا کامەران')->firstOrFail();
        $this->assertEquals('master', $employee->job_title);
        $this->assertEquals(35000, (float) $employee->daily_wage);

        // Quick check-in
        $this->actingAs($this->storekeeper);
        $checkInRes = $this->postJson('/attendance/quick-check-in', [
            'employee_id' => $employee->id,
            'work_date' => now()->toDateString(),
        ]);
        $checkInRes->assertStatus(200);
        $checkInRes->assertJson(['ok' => true]);

        // Record single with details
        $recordRes = $this->postJson('/attendance/record-single', [
            'employee_id' => $employee->id,
            'work_date' => now()->toDateString(),
            'status' => 'present',
            'check_in' => '08:00',
            'check_out' => '18:00',
            'fuel_expense' => 15000,
            'trip_destination' => 'ماڵی حاجی ئەحمەد',
        ]);
        $recordRes->assertStatus(200);
        $recordRes->assertJson(['ok' => true]);

        // Test new matrix ledger endpoints
        $matrixViewRes = $this->get('/workshop/employees?range_type=this_week');
        $matrixViewRes->assertStatus(200);
        $matrixViewRes->assertSee('وەستا کامەران');
        $matrixViewRes->assertSee('جەدوەلی ئامادەبوونی ڕۆژانە');

        // Toggle cell endpoint
        $toggleRes = $this->postJson('/workshop/employees/toggle-cell', [
            'employee_id' => $employee->id,
            'work_date' => now()->toDateString(),
            'status' => 'present',
        ]);
        $toggleRes->assertStatus(200);
        $toggleRes->assertJson(['ok' => true, 'status' => 'present']);

        // Batch mark day
        $batchRes = $this->postJson('/workshop/employees/batch-mark-day', [
            'work_date' => now()->toDateString(),
            'status' => 'present',
        ]);
        $batchRes->assertStatus(200);
        $batchRes->assertJson(['ok' => true]);

        // Storekeeper should be forbidden from recording payments
        $storekeeperPaymentRes = $this->postJson('/workshop/employees/record-payment', [
            'employee_id' => $employee->id,
            'amount' => 20000,
            'paid_at' => now()->toDateString(),
            'note' => 'پێشەکی هەفتانە',
        ]);
        $storekeeperPaymentRes->assertStatus(403);

        // Admin can record employee advance/payment
        $this->actingAs($this->admin);
        $paymentRes = $this->postJson('/workshop/employees/record-payment', [
            'employee_id' => $employee->id,
            'amount' => 20000,
            'paid_at' => now()->toDateString(),
            'note' => 'پێشەکی هەفتانە',
        ]);
        $paymentRes->assertStatus(200);
        $paymentRes->assertJson(['ok' => true]);
    }

    public function test_admin_can_record_payments_and_manage_cash_and_reports()
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'name' => 'ئارام کەریم',
            'phone' => '07504443322',
            'opening_balance' => 100000,
            'opening_currency' => 'IQD',
        ]);

        // Customer balance should initially be 100,000
        $this->assertEquals(100000, (float) $customer->balance());

        // Record customer payment (in)
        $paymentRes = $this->post('/payments', [
            'customer_id' => $customer->id,
            'amount' => 40000,
            'currency' => 'IQD',
            'paid_at' => now()->toDateString(),
            'note' => 'بەشێک لە قەرز',
        ]);
        $paymentRes->assertRedirect();

        // Customer balance should now be 60,000
        $this->assertEquals(60000, (float) $customer->fresh()->balance());

        $cashBox = \App\Models\CashBox::firstOrFail();

        // Cash transaction (expense)
        $cashRes = $this->post('/cash/transaction', [
            'cash_box_id' => $cashBox->id,
            'direction' => 'out',
            'category' => 'expense',
            'amount' => 15000,
            'occurred_at' => now()->toDateString(),
            'note' => 'کڕینی چا و قاوە بۆ کارگە',
        ]);
        $cashRes->assertRedirect();

        // Test all reports are rendered with 200 OK
        $reports = ['sales', 'purchases', 'profit', 'stock', 'cash', 'workshop_production', 'workshop_materials'];
        foreach ($reports as $reportKey) {
            $repRes = $this->get("/reports/{$reportKey}");
            $repRes->assertStatus(200);
        }
    }
}
