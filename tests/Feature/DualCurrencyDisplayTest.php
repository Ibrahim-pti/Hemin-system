<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DualCurrencyDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_purchases_page_displays_dual_currency_switcher_and_metrics()
    {
        $supplier = Supplier::first() ?? Supplier::create(['name' => 'Supplier Test', 'is_active' => true]);
        $wh = Warehouse::first() ?? Warehouse::create(['name' => 'Main WH', 'is_default' => true, 'is_active' => true]);
        \App\Models\Purchase::create([
            'invoice_no' => \App\Models\Purchase::nextInvoiceNo(),
            'supplier_id' => $supplier->id,
            'warehouse_id' => $wh->id,
            'purchase_date' => now(),
            'currency' => 'IQD',
            'exchange_rate' => 1500,
            'status' => 'confirmed',
            'subtotal' => 200000,
            'total' => 200000,
        ]);
        \App\Models\Purchase::create([
            'invoice_no' => 'P-99999',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $wh->id,
            'purchase_date' => now(),
            'currency' => 'USD',
            'exchange_rate' => 1500,
            'status' => 'confirmed',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $response = $this->get(route('purchases.index'));
        $response->assertStatus(200);
        $response->assertSee('پیشاندان بە دراو:');
        $response->assertSee('هەمووی (دۆلار و دینار)');
        $response->assertSee('تەنها دۆلار ($)');
        $response->assertSee('تەنها دینار (د.ع)');

        $usdResponse = $this->get(route('purchases.index', ['currency' => 'USD']));
        $usdResponse->assertStatus(200);

        $iqdResponse = $this->get(route('purchases.index', ['currency' => 'IQD']));
        $iqdResponse->assertStatus(200);
    }

    public function test_debts_page_displays_dual_currency_switcher_and_metrics()
    {
        $response = $this->get(route('debts.index'));
        $response->assertStatus(200);
        $response->assertSee('پیشاندان بە دراو:');
        $response->assertSee('هەمووی (دۆلار و دینار)');
        $response->assertSee('تەنها دۆلار ($)');
        $response->assertSee('تەنها دینار (د.ع)');
        $response->assertSee('کۆی قەرزی ماوە');

        $usdResponse = $this->get(route('debts.index', ['currency' => 'USD']));
        $usdResponse->assertStatus(200);
        $usdResponse->assertSee('کۆی قەرزی ماوە بە دۆلار');

        $iqdResponse = $this->get(route('debts.index', ['currency' => 'IQD']));
        $iqdResponse->assertStatus(200);
        $iqdResponse->assertSee('کۆی قەرزی ماوە بە دینار');
    }

    public function test_customers_page_displays_dual_currency_switcher_and_metrics()
    {
        $response = $this->get(route('customers.index'));
        $response->assertStatus(200);
        $response->assertSee('پیشاندان بە دراو:');
        $response->assertSee('هەمووی (دۆلار و دینار)');
        $response->assertSee('تەنها دۆلار ($)');
        $response->assertSee('تەنها دینار (د.ع)');
        $response->assertSee('کۆی فرۆشتن');

        $usdResponse = $this->get(route('customers.index', ['currency' => 'USD']));
        $usdResponse->assertStatus(200);
        $usdResponse->assertSee('بە دۆلار');

        $iqdResponse = $this->get(route('customers.index', ['currency' => 'IQD']));
        $iqdResponse->assertStatus(200);
        $iqdResponse->assertSee('بە دینار');
    }

    public function test_suppliers_page_displays_dual_currency_switcher_and_metrics()
    {
        $response = $this->get(route('suppliers.index'));
        $response->assertStatus(200);
        $response->assertSee('پیشاندان بە دراو:');
        $response->assertSee('هەمووی (دۆلار و دینار)');
        $response->assertSee('تەنها دۆلار ($)');
        $response->assertSee('تەنها دینار (د.ع)');
        $response->assertSee('کۆی گشتی کڕینەکان');

        $usdResponse = $this->get(route('suppliers.index', ['currency' => 'USD']));
        $usdResponse->assertStatus(200);
        $usdResponse->assertSee('کۆی کڕین بە دۆلار');

        $iqdResponse = $this->get(route('suppliers.index', ['currency' => 'IQD']));
        $iqdResponse->assertStatus(200);
        $iqdResponse->assertSee('کۆی کڕین بە دینار');
    }

    public function test_customer_balances_remain_strictly_separated_by_currency()
    {
        $customer = Customer::create([
            'name' => 'Separation Customer Test',
            'phone' => '07500000000',
            'is_active' => true,
        ]);

        // Order in USD: $100
        \App\Models\Order::create([
            'invoice_no' => 'ORD-USD-1',
            'customer_id' => $customer->id,
            'currency' => 'USD',
            'exchange_rate' => 1500,
            'status' => 'confirmed',
            'subtotal' => 100,
            'total' => 100,
            'order_date' => now(),
        ]);

        // Order in IQD: 150,000 IQD
        \App\Models\Order::create([
            'invoice_no' => 'ORD-IQD-1',
            'customer_id' => $customer->id,
            'currency' => 'IQD',
            'exchange_rate' => 1,
            'status' => 'confirmed',
            'subtotal' => 150000,
            'total' => 150000,
            'order_date' => now(),
        ]);

        // Assert initial separate balances
        $balances = $customer->balances();
        $this->assertEquals(100.0, $balances['USD']);
        $this->assertEquals(150000.0, $balances['IQD']);

        // Payment in USD: $40
        \App\Models\Payment::create([
            'voucher_no' => 'PAY-USD-1',
            'party_type' => Customer::class,
            'party_id' => $customer->id,
            'currency' => 'USD',
            'amount' => 40,
            'amount_iqd' => 60000,
            'direction' => 'in',
            'paid_at' => now(),
            'user_id' => $this->admin->id,
        ]);

        $balancesAfterUsdPayment = $customer->fresh()->balances();
        $this->assertEquals(60.0, $balancesAfterUsdPayment['USD']);
        $this->assertEquals(150000.0, $balancesAfterUsdPayment['IQD']); // IQD is untouched

        // Payment in IQD: 50,000 IQD
        \App\Models\Payment::create([
            'voucher_no' => 'PAY-IQD-1',
            'party_type' => Customer::class,
            'party_id' => $customer->id,
            'currency' => 'IQD',
            'amount' => 50000,
            'amount_iqd' => 50000,
            'direction' => 'in',
            'paid_at' => now(),
            'user_id' => $this->admin->id,
        ]);

        $balancesAfterIqdPayment = $customer->fresh()->balances();
        $this->assertEquals(60.0, $balancesAfterIqdPayment['USD']); // USD is untouched
        $this->assertEquals(100000.0, $balancesAfterIqdPayment['IQD']);
    }
}
