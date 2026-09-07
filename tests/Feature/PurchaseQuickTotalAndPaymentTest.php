<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchaseQuickTotalAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Warehouse $warehouse;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->actingAs($this->admin);

        $this->warehouse = Warehouse::first() ?? Warehouse::create([
            'name' => 'کۆگای سەرەکی',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->supplier = Supplier::first() ?? Supplier::create([
            'name' => 'کۆمپانیای سەردەم بۆ ئاسن و مەواد',
            'is_active' => true,
        ]);
    }

    public function test_purchase_can_be_created_with_quick_total_mode_and_cash()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('receipt.jpg');

        $payload = [
            'entry_mode' => 'quick',
            'quick_title' => 'مەوادی هەمەجۆری کارگە و پەرژین',
            'quick_total' => '250,000',
            'payment_type' => 'cash',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'currency' => 'IQD',
            'image' => $file,
            'note' => 'وەسڵی کڕینی فرۆشیار ژمارە 1045',
        ];

        $res = $this->post('/purchases', $payload);
        $res->assertRedirect();

        $purchase = Purchase::latest('id')->firstOrFail();

        $this->assertEquals(250000, (float) $purchase->total);
        $this->assertEquals(250000, (float) $purchase->paid_amount);
        $this->assertEquals(0, (float) $purchase->remaining());
        $this->assertNotNull($purchase->image);
        Storage::disk('public')->assertExists($purchase->image);

        // کاڵای دروستکراو
        $this->assertCount(1, $purchase->items);
        $this->assertEquals('مەوادی هەمەجۆری کارگە و پەرژین', $purchase->items[0]->item->name);
        $this->assertEquals(1, (float) $purchase->items[0]->qty);
        $this->assertEquals(250000, (float) $purchase->items[0]->unit_price);
    }

    public function test_purchase_can_be_created_on_debt()
    {
        $payload = [
            'entry_mode' => 'quick',
            'quick_title' => 'بۆیاخ و براغی',
            'quick_total' => '180,000',
            'payment_type' => 'debt',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'currency' => 'IQD',
        ];

        $res = $this->post('/purchases', $payload);
        $res->assertRedirect();

        $purchase = Purchase::latest('id')->firstOrFail();

        $this->assertEquals(180000, (float) $purchase->total);
        $this->assertEquals(0, (float) $purchase->paid_amount);
        $this->assertEquals(180000, (float) $purchase->remaining());
    }

    public function test_purchase_can_be_created_with_partial_payment()
    {
        $payload = [
            'entry_mode' => 'quick',
            'quick_title' => 'مەوادی هەمەجۆر',
            'quick_total' => '500,000',
            'payment_type' => 'partial',
            'paid_amount' => '200,000',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'currency' => 'IQD',
        ];

        $res = $this->post('/purchases', $payload);
        $res->assertRedirect();

        $purchase = Purchase::latest('id')->firstOrFail();

        $this->assertEquals(500000, (float) $purchase->total);
        $this->assertEquals(200000, (float) $purchase->paid_amount);
        $this->assertEquals(300000, (float) $purchase->remaining());
    }

    public function test_purchase_can_be_created_in_usd_with_exchange_rate()
    {
        $payload = [
            'entry_mode' => 'quick',
            'quick_title' => 'مەوادی هەمەجۆر بە دۆلار',
            'quick_total' => '1,200',
            'payment_type' => 'cash',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'currency' => 'USD',
            'exchange_rate' => '150,000',
        ];

        $res = $this->post('/purchases', $payload);
        $res->assertRedirect();

        $purchase = Purchase::latest('id')->firstOrFail();

        $this->assertEquals('USD', $purchase->currency);
        $this->assertEquals(1500, (float) $purchase->exchange_rate);
        $this->assertEquals(1200, (float) $purchase->total);
        $this->assertEquals(1200, (float) $purchase->paid_amount);
        $this->assertEquals(0, (float) $purchase->remaining());
        $this->assertEquals(1800000, (float) $purchase->total_iqd);

        // Payment check
        $payment = $purchase->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals('USD', $payment->currency);
        $this->assertEquals(1200, (float) $payment->amount);
        $this->assertEquals(1500, (float) $payment->exchange_rate);
        $this->assertEquals(1800000, (float) $payment->amount_iqd);
    }

    public function test_purchase_create_page_contains_currency_selector()
    {
        $res = $this->get('/purchases/create');
        $res->assertOk();
        $res->assertSee('دراوی پسوولە');
        $res->assertSee('دینار (IQD)');
        $res->assertSee('دۆلار ($ USD)');
        $res->assertDontSee('نرخی ١٠٠$ دۆلار');
    }

    public function test_purchase_debt_can_be_paid_via_store_payment()
    {
        // 1. Create a purchase with debt (200,000 IQD total, 100,000 paid, 100,000 remaining)
        $purchase = Purchase::create([
            'invoice_no' => Purchase::nextInvoiceNo(),
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'currency' => 'IQD',
            'exchange_rate' => 1,
            'subtotal' => 200000,
            'discount_amount' => 0,
            'total' => 200000,
            'paid_amount' => 100000,
            'status' => 'confirmed',
            'user_id' => $this->admin->id,
        ]);

        app(\App\Services\PaymentService::class)->record([
            'direction' => 'out',
            'amount' => 100000,
            'currency' => 'IQD',
            'paid_at' => now()->toDateString(),
            'party' => $this->supplier,
            'purchase_id' => $purchase->id,
            'category' => 'supplier_payment',
            'note' => 'پێشەکی پسوولەی کڕین',
        ]);

        $this->assertEquals(100000, (float) $purchase->remaining());

        // 2. Pay remaining 100,000
        $res = $this->post("/purchases/{$purchase->id}/payments", [
            'amount' => '100,000',
            'paid_at' => now()->toDateString(),
            'note' => 'دانەوەی قەرز بە کاش',
        ]);

        $res->assertSessionHas('ok');
        $res->assertRedirect();

        // 3. Assert purchase is fully paid
        $purchase->refresh();
        $this->assertEquals(0, (float) $purchase->remaining());
        $this->assertEquals(200000, (float) $purchase->paid_amount);

        // 4. Assert payment record created
        $lastPayment = $purchase->payments()->latest('id')->first();
        $this->assertNotNull($lastPayment);
        $this->assertEquals(100000, (float) $lastPayment->amount);
        $this->assertEquals('out', $lastPayment->direction);
        $this->assertEquals($this->supplier->id, $lastPayment->party_id);
    }
}
