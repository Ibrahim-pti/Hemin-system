<?php

namespace Tests\Feature;

use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderUpdatePrepaidTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->actingAs($admin);
    }

    public function test_updating_order_lines_and_prepaid_payment_does_not_fail(): void
    {
        $customer = Customer::create([
            'name' => 'کڕیاری تاقی',
            'opening_currency' => 'USD',
            'is_active' => true,
        ]);

        $createResponse = $this->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'order_date' => '2026-11-22',
            'currency' => 'USD',
            'exchange_rate' => '150000',
            'discount_amount' => '0',
            'prepaid_amount' => '50',
            'confirm' => '1',
            'lines' => [
                ['description' => 'دەرگا', 'line_total' => '100', 'unit_price' => '100'],
            ],
        ]);
        $createResponse->assertRedirect();

        $order = Order::latest('id')->firstOrFail();

        // Add a second line and update prepaid amount
        $updateResponse = $this->put(route('orders.update', $order), [
            'customer_id' => $customer->id,
            'order_date' => '2026-11-22',
            'currency' => 'USD',
            'exchange_rate' => '150000',
            'discount_amount' => '0',
            'prepaid_amount' => '75',
            'lines' => [
                ['description' => 'دەرگا', 'line_total' => '100', 'unit_price' => '100'],
                ['description' => 'پەنجەرە', 'line_total' => '50', 'unit_price' => '50'],
            ],
        ]);
        $updateResponse->assertRedirect(route('orders.print', $order));

        $order->refresh();
        $this->assertCount(2, $order->items);
        $this->assertEquals(150.0, (float) $order->total);

        $payment = Payment::where('order_id', $order->id)->firstOrFail();
        $this->assertEquals(75.0, (float) $payment->amount);
        $this->assertEquals(
            75.0,
            (float) CashTransaction::where('reference_type', Payment::class)->where('reference_id', $payment->id)->value('amount')
        );

        // Remove a line and clear prepaid amount
        $removeResponse = $this->put(route('orders.update', $order), [
            'customer_id' => $customer->id,
            'order_date' => '2026-11-22',
            'currency' => 'USD',
            'exchange_rate' => '150000',
            'discount_amount' => '0',
            'prepaid_amount' => '0',
            'lines' => [
                ['description' => 'دەرگا', 'line_total' => '100', 'unit_price' => '100'],
            ],
        ]);
        $removeResponse->assertRedirect(route('orders.print', $order));

        $order->refresh();
        $this->assertCount(1, $order->items);
        $this->assertEquals(0, Payment::where('order_id', $order->id)->count());
        $this->assertEquals(0, CashTransaction::where('reference_type', Payment::class)->where('reference_id', $payment->id)->count());
    }
}
