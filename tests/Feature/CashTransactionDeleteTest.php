<?php

namespace Tests\Feature;

use App\Models\CashBox;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashTransactionDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CashBox $box;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->box = CashBox::create([
            'name' => 'قاسەی سەرەکی',
            'currency' => 'IQD',
            'opening_balance' => 100000,
            'is_active' => true,
        ]);
    }

    public function test_can_view_delete_button_and_delete_cash_transaction(): void
    {
        $transaction = CashTransaction::create([
            'cash_box_id' => $this->box->id,
            'direction' => 'in',
            'amount' => 50000,
            'category' => 'other',
            'occurred_at' => now()->toDateString(),
            'note' => 'تێکردنی تاقیکاری',
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('cash.index'));
        $response->assertOk();
        $response->assertSee('تێکردنی تاقیکاری');
        $response->assertSee('showDeleteModal');
        $response->assertSee(route('cash.transaction.destroy', $transaction));

        $deleteResponse = $this->actingAs($this->user)->delete(route('cash.transaction.destroy', $transaction));
        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHas('ok', 'جووڵەی قاسەکە بە سەرکەوتوویی سڕدرایەوە.');

        $this->assertDatabaseMissing('cash_transactions', [
            'id' => $transaction->id,
        ]);
    }
}
