<?php

namespace Tests\Feature;

use App\Models\CashBox;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPaymentDiscountTest extends TestCase
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

    public function test_payment_creation_page_renders_discount_input_and_summary_section()
    {
        $customer = Customer::create([
            'name' => 'Discount UI Customer',
            'phone' => '07501112233',
            'is_active' => true,
        ]);

        $response = $this->get(route('payments.create', ['customer' => $customer->id]));
        $response->assertStatus(200);
        $response->assertSee('داشکان (داشکاندن)');
        $response->assertSee('بڕی پارەی وەرگیراو');
        $response->assertSee('کۆی داشکێنراو لە قەرز');
        $response->assertSee('داشکاندنی ماوەکە');
    }

    public function test_can_record_payment_with_discount_in_iqd_and_clears_customer_debt()
    {
        $customer = Customer::create([
            'name' => 'Ali Ahmed',
            'phone' => '07501234567',
            'is_active' => true,
        ]);

        // Order of 100,000 IQD
        $order = Order::create([
            'invoice_no' => 'ORD-DISC-1',
            'customer_id' => $customer->id,
            'currency' => 'IQD',
            'exchange_rate' => 1,
            'status' => 'confirmed',
            'subtotal' => 100000,
            'total' => 100000,
            'order_date' => now(),
        ]);

        $this->assertEquals(100000.0, $customer->balances()['IQD']);
        $this->assertEquals(100000.0, $order->remaining());

        $iqdBox = CashBox::where('currency', 'IQD')->first();
        $initialBoxTxCount = CashTransaction::where('cash_box_id', $iqdBox->id)->count();

        // Customer pays 90,000 IQD cash + 10,000 IQD discount
        $postData = [
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => '90,000',
            'discount' => '10,000',
            'currency' => 'IQD',
            'cash_box_id' => $iqdBox->id,
            'paid_at' => now()->toDateString(),
            'note' => 'قەرز واسڵکرا بە داشکانی ١٠ هەزار',
        ];

        $response = $this->post(route('payments.store'), $postData);

        $payment = Payment::latest('id')->firstOrFail();
        $response->assertRedirect(route('payments.print', $payment));

        // 1. Verify Payment model values
        $this->assertEquals(90000.0, (float) $payment->amount);
        $this->assertEquals(10000.0, (float) $payment->discount);
        $this->assertEquals(100000.0, (float) $payment->total_settled);

        // 2. Verify CashTransaction only recorded physical cash (90,000)
        $tx = CashTransaction::where('reference_type', Payment::class)
            ->where('reference_id', $payment->id)
            ->firstOrFail();
        $this->assertEquals(90000.0, (float) $tx->amount);

        // 3. Verify Customer balance is fully cleared (0 IQD)
        $customerFresh = $customer->fresh();
        $this->assertEquals(0.0, $customerFresh->balances()['IQD']);

        // 4. Verify Order remaining is fully cleared (0 IQD)
        $orderFresh = $order->fresh();
        $this->assertEquals(0.0, $orderFresh->remaining());
        $this->assertEquals(100000.0, $orderFresh->paidTotal());
    }

    public function test_can_record_payment_with_discount_in_usd_with_exchange_rate()
    {
        ExchangeRate::whereDate('effective_date', now()->toDateString())->update(['usd_to_iqd' => 1500]);

        $customer = Customer::create([
            'name' => 'Kak Karwan',
            'phone' => '07507654321',
            'is_active' => true,
        ]);

        // Order of $100 USD
        $order = Order::create([
            'invoice_no' => 'ORD-DISC-USD-1',
            'customer_id' => $customer->id,
            'currency' => 'USD',
            'exchange_rate' => 1500,
            'status' => 'confirmed',
            'subtotal' => 100,
            'total' => 100,
            'order_date' => now(),
        ]);

        $this->assertEquals(100.0, $customer->balances()['USD']);

        $usdBox = CashBox::where('currency', 'USD')->first();

        // Customer pays $85 cash + $15 discount with rate 150,000 IQD per 100$
        $postData = [
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'amount' => 85,
            'discount' => 15,
            'currency' => 'USD',
            'exchange_rate' => '150,000',
            'cash_box_id' => $usdBox->id,
            'paid_at' => now()->toDateString(),
            'note' => 'حەقدی دۆلار بە داشکانەوە',
        ];

        $response = $this->post(route('payments.store'), $postData);

        $payment = Payment::latest('id')->firstOrFail();
        $response->assertRedirect(route('payments.print', $payment));

        $this->assertEquals(85.0, (float) $payment->amount);
        $this->assertEquals(15.0, (float) $payment->discount);
        $this->assertEquals(100.0, (float) $payment->total_settled);
        $this->assertEquals(85 * 1500, (float) $payment->amount_iqd);
        $this->assertEquals(15 * 1500, (float) $payment->discount_iqd);

        // USD cash drawer receives only $85
        $tx = CashTransaction::where('reference_type', Payment::class)
            ->where('reference_id', $payment->id)
            ->firstOrFail();
        $this->assertEquals(85.0, (float) $tx->amount);

        // Customer USD balance is fully settled
        $this->assertEquals(0.0, $customer->fresh()->balances()['USD']);
        $this->assertEquals(0.0, $order->fresh()->remaining());
    }

    public function test_payment_print_page_shows_discount_and_total_settled()
    {
        $customer = Customer::create([
            'name' => 'Soran Print Test',
            'phone' => '07503334455',
            'is_active' => true,
        ]);

        $payment = Payment::create([
            'voucher_no' => 'H-99881',
            'party_type' => Customer::class,
            'party_id' => $customer->id,
            'party_name' => $customer->name,
            'direction' => 'in',
            'amount' => 45000,
            'discount' => 5000,
            'currency' => 'IQD',
            'amount_iqd' => 45000,
            'discount_iqd' => 5000,
            'paid_at' => now(),
            'user_id' => $this->admin->id,
            'note' => 'سەنەدی تێست بۆ چاپ',
        ]);

        $response = $this->get(route('payments.print', $payment));
        $response->assertStatus(200);
        $response->assertSee('45,000');
        $response->assertSee('5,000');
        $response->assertSee('داشکان');
        $response->assertSee('کۆی گشتی حیسابکراو بۆ قەرز');
        $response->assertSee('50,000');
    }

    public function test_payments_index_displays_discount_tag_for_discounted_payment()
    {
        $customer = Customer::create([
            'name' => 'Listing Test Customer',
            'phone' => '07508889900',
            'is_active' => true,
        ]);

        Payment::create([
            'voucher_no' => 'H-99882',
            'party_type' => Customer::class,
            'party_id' => $customer->id,
            'party_name' => $customer->name,
            'direction' => 'in',
            'amount' => 20000,
            'discount' => 5000,
            'currency' => 'IQD',
            'amount_iqd' => 20000,
            'discount_iqd' => 5000,
            'paid_at' => now(),
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('payments.index'));
        $response->assertStatus(200);
        $response->assertSee('+20,000');
        $response->assertSee('داشکان: 5,000');
    }

    public function test_validation_fails_if_both_amount_and_discount_are_zero()
    {
        $customer = Customer::create([
            'name' => 'Validation Customer',
            'is_active' => true,
        ]);

        $response = $this->post(route('payments.store'), [
            'customer_id' => $customer->id,
            'amount' => 0,
            'discount' => 0,
            'currency' => 'IQD',
            'paid_at' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['amount']);
    }
}
