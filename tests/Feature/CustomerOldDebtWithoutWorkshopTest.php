<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerOldDebt;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_old_debt_can_be_added_with_status_and_image_without_creating_orders(): void
    {
        Storage::fake('public');

        $customer = Customer::create([
            'name' => 'کاک هێمن',
            'phone' => '07701234567',
            'opening_balance' => 0,
            'opening_currency' => 'IQD',
            'is_active' => true,
        ]);

        $image = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->actingAs($this->user)->post(route('debts.old-debt'), [
            'customer_id' => $customer->id,
            'amount' => '150000',
            'currency' => 'IQD',
            'status' => 'debt',
            'date' => '2026-09-01',
            'note' => 'حیسابی پێشوو لەسەر دەفتەر',
            'image' => $image,
        ]);

        $response->assertSessionHas('ok');
        $this->assertDatabaseHas('customer_old_debts', [
            'customer_id' => $customer->id,
            'amount' => 150000,
            'status' => 'debt',
            'currency' => 'IQD',
            'note' => 'حیسابی پێشوو لەسەر دەفتەر',
        ]);

        $oldDebt = CustomerOldDebt::where('customer_id', $customer->id)->firstOrFail();
        $this->assertNotNull($oldDebt->image);
        Storage::disk('public')->assertExists($oldDebt->image);

        // Customer balance reflects debt
        $this->assertEquals(150000, (float) $customer->balance());

        // Zero orders created!
        $this->assertEquals(0, Order::count());
    }

    public function test_old_debt_marked_as_paid_does_not_increase_customer_debt(): void
    {
        $customer = Customer::create([
            'name' => 'کاک ئاسۆ',
            'phone' => '07507778899',
            'opening_balance' => 0,
            'opening_currency' => 'IQD',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('debts.old-debt'), [
            'customer_id' => $customer->id,
            'amount' => '300000',
            'currency' => 'IQD',
            'status' => 'paid',
            'date' => '2026-08-20',
            'note' => 'پارەدانی ساڵی پار تەواو بووە',
        ]);

        $response->assertSessionHas('ok');
        $this->assertDatabaseHas('customer_old_debts', [
            'customer_id' => $customer->id,
            'amount' => 300000,
            'paid_amount' => 300000,
            'status' => 'paid',
        ]);

        // Customer balance should be 0 because it was fully paid!
        $this->assertEquals(0, (float) $customer->balance());

        // Zero orders created!
        $this->assertEquals(0, Order::count());
    }

    public function test_old_debt_can_be_deleted(): void
    {
        Storage::fake('public');

        $customer = Customer::create([
            'name' => 'کاک شوان',
            'phone' => '07501112233',
            'opening_balance' => 0,
            'opening_currency' => 'IQD',
            'is_active' => true,
        ]);

        $oldDebt = CustomerOldDebt::create([
            'customer_id' => $customer->id,
            'amount' => 50000,
            'paid_amount' => 0,
            'currency' => 'IQD',
            'status' => 'debt',
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(50000, (float) $customer->balance());

        $response = $this->actingAs($this->user)->delete(route('debts.old-debt.destroy', $oldDebt));
        $response->assertSessionHas('ok');

        $this->assertDatabaseMissing('customer_old_debts', ['id' => $oldDebt->id]);
        $this->assertEquals(0, (float) $customer->balance());
    }

    public function test_old_debt_statement_view_is_accurate(): void
    {
        $customer = Customer::create([
            'name' => 'کاک دانا',
            'phone' => '07503334455',
            'opening_balance' => 0,
            'opening_currency' => 'IQD',
            'is_active' => true,
        ]);

        CustomerOldDebt::create([
            'customer_id' => $customer->id,
            'amount' => 200000,
            'paid_amount' => 0,
            'currency' => 'IQD',
            'status' => 'debt',
            'date' => now()->toDateString(),
            'note' => 'قەرزی دەفتەر',
        ]);

        $response = $this->actingAs($this->user)->get(route('customers.statement', $customer));
        $response->assertOk();
        $response->assertSee('قەرزی دەفتەر');
        $response->assertSee('200,000');
    }
}
