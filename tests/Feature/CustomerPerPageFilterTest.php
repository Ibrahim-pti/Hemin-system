<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPerPageFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->actingAs($admin);
    }

    public function test_customers_index_supports_per_page_filter_and_all_option(): void
    {
        $existingCount = Customer::count();
        $needed = max(0, 35 - $existingCount);
        for ($i = 1; $i <= $needed; $i++) {
            Customer::create([
                'name' => sprintf('کڕیاری تاقیکردنەوە %03d', $i),
                'opening_currency' => 'IQD',
                'is_active' => true,
            ]);
        }

        $defaultResponse = $this->get(route('customers.index'));
        $defaultResponse->assertStatus(200);
        $defaultResponse->assertSee('نیشاندان:');
        $defaultResponse->assertSee('15 کڕیار');
        $defaultResponse->assertSee('30 کڕیار');
        $defaultResponse->assertSee('50 کڕیار');
        $defaultResponse->assertSee('100 کڕیار');
        $defaultResponse->assertSee('هەمووی');
        $this->assertCount(30, $defaultResponse->viewData('customers')->items());

        $per15Response = $this->get(route('customers.index', ['per_page' => '15']));
        $per15Response->assertStatus(200);
        $this->assertCount(15, $per15Response->viewData('customers')->items());

        $allResponse = $this->get(route('customers.index', ['per_page' => 'all']));
        $allResponse->assertStatus(200);
        $this->assertCount(Customer::count(), $allResponse->viewData('customers')->items());
        $this->assertFalse($allResponse->viewData('customers')->hasPages());

        $invalidResponse = $this->get(route('customers.index', ['per_page' => '999']));
        $invalidResponse->assertStatus(200);
        $this->assertCount(30, $invalidResponse->viewData('customers')->items());
    }
}
