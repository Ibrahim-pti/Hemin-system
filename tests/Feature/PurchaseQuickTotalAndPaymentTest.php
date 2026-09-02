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
}
