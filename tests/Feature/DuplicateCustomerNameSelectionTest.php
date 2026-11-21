<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateCustomerNameSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->actingAs($admin);
    }

    public function test_duplicate_customer_names_are_distinguished_by_id_and_address(): void
    {
        $c1 = Customer::create([
            'name' => 'مةهةندس حةلا',
            'phone' => '07504944378',
            'address' => 'بيناي شيخ احمد',
            'opening_currency' => 'USD',
            'is_active' => true,
        ]);

        $c2 = Customer::create([
            'name' => 'مةهةندس حةلا',
            'phone' => '07504944378',
            'address' => 'كەنجان ستي',
            'opening_currency' => 'USD',
            'is_active' => true,
        ]);

        $oldDebtPage = $this->get(route('debts.old-debt.create', ['customer_id' => $c1->id]));
        $oldDebtPage->assertOk();
        $oldDebtPage->assertSee('بيناي شيخ احمد');
        $oldDebtPage->assertSee('كەنجان ستي');
        $oldDebtPage->assertSee("oldDebtForm({$c1->id},", false);

        $orderPage = $this->get(route('orders.create', ['customer' => $c1->id]));
        $orderPage->assertOk();
        $orderPage->assertSee('بيناي شيخ احمد');
        $orderPage->assertSee('كەنجان ستي');
        $orderPage->assertSee("customerId: '{$c1->id}'", false);

        $profilePage = $this->get(route('customers.show', $c1));
        $profilePage->assertOk();
        $profilePage->assertSee(route('orders.create', ['customer' => $c1->id]), false);

        $statementPage = $this->get(route('statement.index'));
        $statementPage->assertOk();
        $statementPage->assertSee('بيناي شيخ احمد');
        $statementPage->assertSee('كەنجان ستي');
    }
}
