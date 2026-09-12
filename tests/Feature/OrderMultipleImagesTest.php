<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderMultipleImagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->customer = Customer::factory()->create(['name' => 'کاک هێمن']);
    }

    public function test_can_create_order_with_multiple_images_for_a_line_item(): void
    {
        Storage::fake('public');

        $img1 = UploadedFile::fake()->image('door1.jpg', 600, 600);
        $img2 = UploadedFile::fake()->image('door2.jpg', 800, 800);
        $img3 = UploadedFile::fake()->image('door3.jpg', 1000, 1000);

        $payload = [
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'currency' => 'USD',
            'lines' => [
                [
                    'description' => 'دەرگای حەوشە و بەشەکانی',
                    'meter' => '4.5',
                    'meter_price' => '120',
                    'images' => [$img1, $img2, $img3],
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $payload);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.print', $order));

        $item = $order->items()->first();
        $this->assertNotNull($item);
        $this->assertEquals('دەرگای حەوشە و بەشەکانی', $item->description);

        // Verify multiple images
        $this->assertNotNull($item->image);
        $this->assertIsArray($item->images);
        $this->assertCount(3, $item->images);
        $this->assertCount(3, $item->allImages());
        $this->assertCount(3, $item->allImageUrls());

        foreach ($item->images as $path) {
            Storage::disk('public')->assertExists($path);
        }

        // Print page should contain images
        $printResponse = $this->get(route('orders.print', $order));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('وێنەکانی دیزاین و داواکاری');
        $printResponse->assertSee('3 وێنە');
    }

    public function test_can_create_order_with_base64_images(): void
    {
        Storage::fake('public');

        // Small 1x1 transparent PNG data url
        $b64_1 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $b64_2 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $payload = [
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'currency' => 'USD',
            'lines' => [
                [
                    'description' => 'مەحەجەرەی پلیتانە',
                    'meter' => '10',
                    'meter_price' => '85',
                    'images_base64' => [$b64_1, $b64_2],
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $payload);
        $order = Order::latest('id')->first();
        $this->assertNotNull($order);

        $item = $order->items()->first();
        $this->assertNotNull($item);
        $this->assertCount(2, $item->images);

        foreach ($item->images as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_can_update_order_and_retain_existing_images_plus_add_new(): void
    {
        Storage::fake('public');

        $existingFile = UploadedFile::fake()->image('old_photo.jpg');
        $existingPath = $existingFile->store('orders', 'public');

        $order = Order::create([
            'customer_id' => $this->customer->id,
            'invoice_no' => 'ORD-100',
            'order_date' => now()->toDateString(),
            'currency' => 'USD',
            'status' => 'draft',
            'user_id' => $this->user->id,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'description' => 'پەنجەرە',
            'image' => $existingPath,
            'images' => [$existingPath],
            'meter' => 2,
            'meter_price' => 50,
            'line_total' => 100,
        ]);

        $newImg = UploadedFile::fake()->image('new_photo.jpg');

        $updatePayload = [
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'currency' => 'USD',
            'lines' => [
                [
                    'description' => 'پەنجەرەی ئەلەمنیۆم نوێکراوە',
                    'meter' => '2',
                    'meter_price' => '50',
                    'existing_images' => [$existingPath],
                    'images' => [$newImg],
                ],
            ],
        ];

        $res = $this->put(route('orders.update', $order), $updatePayload);
        $res->assertRedirect(route('orders.print', $order));

        $freshItem = $order->fresh()->items()->first();
        $this->assertNotNull($freshItem);
        $this->assertEquals('پەنجەرەی ئەلەمنیۆم نوێکراوە', $freshItem->description);
        $this->assertCount(2, $freshItem->images);
        $this->assertContains($existingPath, $freshItem->images);

        foreach ($freshItem->images as $p) {
            Storage::disk('public')->assertExists($p);
        }
    }
}
