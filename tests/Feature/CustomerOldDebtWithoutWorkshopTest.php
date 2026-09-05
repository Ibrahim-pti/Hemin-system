<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOldDebtWithoutWorkshopTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->user = User::firstWhere('email', 'admin@hemin.krd');
    }

    public function test_customer_can_be_created_with_opening_debt_without_creating_workshop_orders(): void
    {
        $response = $this->actingAs($this->user)->post(route('customers.store'), [
            'name' => 'کاک ئەحمەد',
            'phone' => '07501234567',
            'address' => 'هەولێر',
            'opening_balance' => '250000',
            'opening_currency' => 'IQD',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $customer = Customer::where('name', 'کاک ئەحمەد')->firstOrFail();
        $this->assertEquals(250000, (float) $customer->opening_balance);
        $this->assertEquals('IQD', $customer->opening_currency);
        $this->assertEquals(250000, (float) $customer->balance());

        // Zero orders created — workshop queue untouched!
        $this->assertEquals(0, Order::count());
    }

    public function test_old_debt_can_be_added_to_existing_customer_without_creating_workshop_orders(): void
    {
        $customer = Customer::create([
            'name' => 'کاک هێمن',
            'phone' => '07701234567',
            'opening_balance' => 0,
            'opening_currency' => 'IQD',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('debts.old-debt'), [
            'customer_id' => $customer->id,
            'amount' => '150000',
            'currency' => 'IQD',
            'note' => 'حیسابی پێشوو لەسەر دەفتەر',
        ]);

        $response->assertSessionHas('ok');
        $customer->refresh();
        $this->assertEquals(150000, (float) $customer->opening_balance);
        $this->assertEquals(150000, (float) $customer->balance());

        // Zero orders created!
        $this->assertEquals(0, Order::count());
    }

    public function test_old_debt_can_create_new_customer_without_creating_workshop_orders(): void
    {
        $response = $this->actingAs($this->user)->post(route('debts.old-debt'), [
            'customer_id' => '__NEW__',
            'new_customer_name' => 'وەستا کاروان',
            'new_customer_phone' => '07509876543',
            'amount' => '500',
            'currency' => 'USD',
            'note' => 'قەرزی کۆنی ساڵی پار',
        ]);

        $response->assertSessionHas('ok');
        $customer = Customer::where('name', 'وەستا کاروان')->firstOrFail();
        $this->assertEquals(500, (float) $customer->opening_balance);
        $this->assertEquals('USD', $customer->opening_currency);

        // Zero orders created!
        $this->assertEquals(0, Order::count());
    }
}
