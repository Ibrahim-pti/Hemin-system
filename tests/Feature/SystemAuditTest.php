<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\ExternalJob;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\StockCount;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $storekeeper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->storekeeper = User::where('email', 'kogha@hemin.krd')->firstOrFail();
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $response = $this->get('/workshop');
        $response->assertRedirect('/login');

        $response = $this->get('/orders');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_all_pages()
    {
        $this->actingAs($this->admin);

        // Core pages
        $routes = [
            '/',
            '/workshop',
            '/workshop/orders',
            '/workshop/materials',
            '/workshop/employees',
            '/items',
            '/items-new',
            '/stock',
            '/stock/new',
            '/counts',
            '/warehouses',
            '/warehouses/create',
            '/suppliers',
            '/suppliers/create',
            '/purchases',
            '/purchases/create',
            '/customers',
            '/customers/create',
            '/statement',
            '/orders',
            '/orders/create',
            '/payments',
            '/payments/create',
            '/debts',
            '/cash',
            '/employees',
            '/employees/create',
            '/attendance',
            '/attendance/wages',
            '/external-jobs',
            '/external-jobs/create',
            '/reports',
            '/activity',
            '/settings',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Admin failed to access {$url} - got status {$response->status()}"
            );
        }

        // Test dynamic model routes if records exist
        if ($item = Item::first()) {
            $this->get("/items/{$item->id}")->assertStatus(200);
            $this->get("/items/{$item->id}/edit")->assertStatus(200);
        }

        if ($customer = Customer::first()) {
            $this->get("/customers/{$customer->id}")->assertStatus(200);
            $this->get("/customers/{$customer->id}/edit")->assertStatus(200);
            $this->get("/customers/{$customer->id}/statement")->assertStatus(200);
        }

        if ($order = Order::first()) {
            $this->get("/orders/{$order->id}")->assertStatus(200);
            $this->get("/orders/{$order->id}/edit")->assertStatus(200);
            $this->get("/orders/{$order->id}/print")->assertStatus(200);
        }

        if ($purchase = Purchase::first()) {
            $this->get("/purchases/{$purchase->id}")->assertStatus(200);
            $this->get("/purchases/{$purchase->id}/edit")->assertStatus(200);
            $this->get("/purchases/{$purchase->id}/print")->assertStatus(200);
        }

        if ($payment = Payment::first()) {
            $this->get("/payments/{$payment->id}")->assertStatus(200);
            $this->get("/payments/{$payment->id}/print")->assertStatus(200);
        }

        if ($employee = Employee::first()) {
            $this->get("/employees/{$employee->id}")->assertStatus(200);
            $this->get("/employees/{$employee->id}/edit")->assertStatus(200);
        }

        if ($supplier = Supplier::first()) {
            $this->get("/suppliers/{$supplier->id}")->assertStatus(200);
            $this->get("/suppliers/{$supplier->id}/edit")->assertStatus(200);
        }

        if ($warehouse = Warehouse::first()) {
            $this->get("/warehouses/{$warehouse->id}/edit")->assertStatus(200);
        }

        if ($job = ExternalJob::first()) {
            $this->get("/external-jobs/{$job->id}")->assertStatus(200);
            $this->get("/external-jobs/{$job->id}/edit")->assertStatus(200);
        }

        if ($count = StockCount::first()) {
            $this->get("/counts/{$count->id}")->assertStatus(200);
        }
    }

    public function test_storekeeper_permissions_and_restrictions()
    {
        $this->actingAs($this->storekeeper);

        // Storekeeper CAN access workshop and items/stock viewing
        $allowed = [
            '/workshop',
            '/workshop/orders',
            '/workshop/materials',
            '/workshop/employees',
            '/items',
            '/stock',
            '/stock/new',
        ];

        foreach ($allowed as $url) {
            $response = $this->get($url);
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Storekeeper was wrongly blocked from allowed route {$url} - got {$response->status()}"
            );
        }

        // Storekeeper CANNOT access sensitive financial, report, order creation, purchase, cash pages
        $forbidden = [
            '/orders/create',
            '/purchases/create',
            '/payments/create',
            '/cash',
            '/reports',
            '/settings',
            '/activity',
            '/debts',
        ];

        foreach ($forbidden as $url) {
            $response = $this->get($url);
            $this->assertTrue(
                $response->status() === 403 || $response->status() === 302,
                "Storekeeper should be forbidden from {$url}, but got status {$response->status()}"
            );
        }
    }
}
