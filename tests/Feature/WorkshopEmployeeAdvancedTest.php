<?php

namespace Tests\Feature;

use App\Models\CashBox;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopEmployeeAdvancedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
    }

    public function test_workshop_manager_can_update_shift_and_late_penalty_settings()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/workshop/settings', [
            'workshop_work_start' => '08:00',
            'workshop_work_end' => '17:00',
            'workshop_work_hours' => 8,
            'workshop_weekly_holiday' => 'friday',
            'workshop_overtime_multiplier' => 1.5,
            'workshop_late_grace_minutes' => 15,
            'workshop_late_deduction_type' => 'weekly_threshold',
            'workshop_late_weekly_threshold_days' => 2,
            'workshop_late_weekly_penalty_amount' => 25000,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertEquals('08:00', Setting::get('workshop_work_start'));
        $this->assertEquals('weekly_threshold', Setting::get('workshop_late_deduction_type'));
        $this->assertEquals('2', Setting::get('workshop_late_weekly_threshold_days'));
        $this->assertEquals('25000', Setting::get('workshop_late_weekly_penalty_amount'));
    }

    public function test_weekly_matrix_and_cell_toggle_cycle()
    {
        $this->actingAs($this->admin);

        $employee = Employee::create([
            'name' => 'وەستا ئاراس',
            'job_title' => 'master',
            'salary_type' => 'daily',
            'daily_wage' => 40000,
            'wage_currency' => 'IQD',
            'is_active' => true,
        ]);

        $today = now()->toDateString();

        // 1. Toggle to present
        $res1 = $this->postJson('/workshop/employees/toggle-cell', [
            'employee_id' => $employee->id,
            'work_date' => $today,
        ]);
        $res1->assertStatus(200);
        $res1->assertJson(['ok' => true, 'status' => 'present']);

        // 2. Toggle to half_day
        $res2 = $this->postJson('/workshop/employees/toggle-cell', [
            'employee_id' => $employee->id,
            'work_date' => $today,
        ]);
        $res2->assertStatus(200);
        $res2->assertJson(['ok' => true, 'status' => 'half_day']);

        // 3. Toggle to absent
        $res3 = $this->postJson('/workshop/employees/toggle-cell', [
            'employee_id' => $employee->id,
            'work_date' => $today,
        ]);
        $res3->assertStatus(200);
        $res3->assertJson(['ok' => true, 'status' => 'absent']);

        // 4. Save detailed cell
        $resSave = $this->postJson('/workshop/employees/save-cell-detail', [
            'employee_id' => $employee->id,
            'work_date' => $today,
            'status' => 'present',
            'check_in' => '08:30',
            'check_out' => '18:30',
            'hours' => 10,
            'overtime_hours' => 2,
            'late_minutes' => 30,
            'fuel_expense' => 12000,
            'note' => 'دەوامی زیادە بۆ تەواوکردنی دەرگا',
        ]);
        $resSave->assertStatus(200);
        $resSave->assertJson([
            'ok' => true,
            'status' => 'present',
            'attendance' => [
                'check_in' => '08:30',
                'overtime_hours' => 2,
                'late_minutes' => 30,
                'fuel_expense' => 12000,
            ],
        ]);
    }

    public function test_employee_month_details_endpoint_returns_accurate_ledger()
    {
        $this->actingAs($this->admin);

        $employee = Employee::create([
            'name' => 'حەمەڵ دیار',
            'job_title' => 'porter',
            'salary_type' => 'daily',
            'daily_wage' => 25000,
            'wage_currency' => 'IQD',
            'is_active' => true,
        ]);

        $cashBox = CashBox::first();
        $this->postJson('/workshop/employees/record-payment', [
            'employee_id' => $employee->id,
            'amount' => 50000,
            'currency' => 'IQD',
            'cash_box_id' => $cashBox->id,
            'paid_at' => now()->toDateString(),
            'note' => 'پێشەکی مانگ',
        ])->assertStatus(200);

        $response = $this->getJson("/workshop/employees/{$employee->id}/month-details?month=" . now()->format('Y-m'));
        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'employee' => [
                'id' => $employee->id,
                'name' => 'حەمەڵ دیار',
            ],
            'stats' => [
                'total_paid' => 50000,
            ],
        ]);
    }
}
