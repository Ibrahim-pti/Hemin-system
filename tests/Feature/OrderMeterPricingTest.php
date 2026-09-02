<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderMeterPricingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->customer = Customer::create([
            'name' => 'کاک ئەحمەد',
            'phone' => '07501112233',
            'address' => 'هەولێر - بەختیاری',
            'is_active' => true,
        ]);
    }

    public function test_can_create_order_with_meter_and_meter_price_and_auto_compute_total()
    {
        $this->actingAs($this->admin);

        $payload = [
            'invoice_no' => 'INV-METER-01',
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'currency' => 'IQD',
            'discount_amount' => 0,
            'prepaid_amount' => 100000,
            'confirm' => 1,
            'lines' => [
                [
                    'description' => 'دەرگای ئاسنی سەرەکی',
                    'meter' => '5.5',
                    'meter_price' => '40000',
                    'line_total' => '220000', // 5.5 * 40,000 = 220,000
                ],
                [
                    'description' => 'مەحەجەرەی بالکۆن',
                    'meter' => '10',
                    'meter_price' => '25000',
                    'line_total' => '250000', // 10 * 25,000 = 250,000
                ],
            ],
        ];

        $response = $this->post('/orders', $payload);
        $response->assertRedirect();

        $order = Order::where('invoice_no', 'INV-METER-01')->firstOrFail();
        $this->assertEquals(470000, (float) $order->subtotal);
        $this->assertEquals(470000, (float) $order->total);
        $this->assertEquals(100000, (float) $order->prepaid_amount);

        $this->assertCount(2, $order->items);

        $item1 = $order->items[0];
        $this->assertEquals('دەرگای ئاسنی سەرەکی', $item1->description);
        $this->assertEquals(5.5, (float) $item1->meter);
        $this->assertEquals(40000, (float) $item1->meter_price);
        $this->assertEquals(220000, (float) $item1->line_total);
        $this->assertTrue($item1->has_meter);

        $item2 = $order->items[1];
        $this->assertEquals('مەحەجەرەی بالکۆن', $item2->description);
        $this->assertEquals(10, (float) $item2->meter);
        $this->assertEquals(25000, (float) $item2->meter_price);
        $this->assertEquals(250000, (float) $item2->line_total);
        $this->assertTrue($item2->has_meter);

        // بینینی وەسڵەکە (Show view)
        $showRes = $this->get("/orders/{$order->id}");
        $showRes->assertStatus(200);
        $showRes->assertSee('5.5 مەتر');
        $showRes->assertSee('دەرگای ئاسنی سەرەکی');

        // چاپی وەسڵەکە (Print view)
        $printRes = $this->get("/orders/{$order->id}/print");
        $printRes->assertStatus(200);
        $printRes->assertSee('دەرگای ئاسنی سەرەکی');
        $printRes->assertSee('5.5 م');
        $printRes->assertSee('40,000');
        $printRes->assertSee('220,000');
    }

    public function test_order_form_displays_meter_and_meter_price_inputs()
    {
        $this->actingAs($this->admin);

        $res = $this->get('/orders/create');
        $res->assertStatus(200);
        $res->assertSee('مەتر');
        $res->assertSee('نرخی مەتر');
        $res->assertSee('کۆی گشتی');
        $res->assertSee('calculateLineTotal');
    }
}
