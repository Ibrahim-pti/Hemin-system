<?php

namespace Tests\Feature;

use App\Models\Attendance;
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
    private User $storekeeper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@hemin.krd')->firstOrFail();
        $this->storekeeper = User::where('email', 'kogha@hemin.krd')->firstOrFail();
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

        // 2. Toggle to half_day (نیو دان)
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

        // 4. Toggle back to present
        $this->postJson('/workshop/employees/toggle-cell', [
            'employee_id' => $employee->id,
            'work_date' => $today,
        ]); // deletes
        $res4 = $this->postJson('/workshop/employees/toggle-cell', [
            'employee_id' => $employee->id,
            'work_date' => $today,
        ]);
        $res4->assertStatus(200);
        $res4->assertJson(['ok' => true, 'status' => 'present']);

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

    public function test_workshop_employee_can_be_deleted()
    {
        $this->actingAs($this->admin);

        $employee = Employee::create([
            'name' => 'وەستا هەڵکەوت',
            'job_title' => 'master',
            'salary_type' => 'daily',
            'daily_wage' => 30000,
            'wage_currency' => 'IQD',
            'is_active' => true,
        ]);

        $res = $this->deleteJson("/workshop/employees/{$employee->id}");
        $res->assertStatus(200);
        $res->assertJson(['ok' => true]);

        $this->assertSoftDeleted($employee);
    }

    public function test_half_day_deduction_and_absent_penalty_calculations()
    {
        $this->actingAs($this->admin);

        // ڕێکخستنی بڕینی نیو دەوام (10,000) و سزای غیاب (15,000)
        $this->postJson('/workshop/settings', [
            'workshop_work_hours' => 8,
            'workshop_weekly_holiday' => 'friday',
            'workshop_half_day_deduction_type' => 'fixed_amount',
            'workshop_half_day_deduction_rate' => 10000,
            'workshop_absent_deduction_type' => 'fixed_amount',
            'workshop_absent_deduction_rate' => 15000,
        ])->assertStatus(200);

        $employee = Employee::create([
            'name' => 'وەستا کاروان',
            'job_title' => 'master',
            'salary_type' => 'daily',
            'daily_wage' => 40000,
            'wage_currency' => 'IQD',
            'is_active' => true,
        ]);

        $monthStr = now()->format('Y-m');
        $date1 = now()->startOfMonth()->toDateString();
        $date2 = now()->startOfMonth()->addDay()->toDateString();
        $date3 = now()->startOfMonth()->addDays(2)->toDateString();

        // ڕۆژی ١: ئامادە (٤٠،٠٠٠)
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => $date1,
            'status' => 'present',
            'user_id' => $this->admin->id,
        ]);

        // ڕۆژی ٢: نیو ڕۆژ (٤٠،٠٠٠ - ١٠،٠٠٠ = ٣٠،٠٠٠)
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => $date2,
            'status' => 'half_day',
            'user_id' => $this->admin->id,
        ]);

        // ڕۆژی ٣: غیاب (سزای ١٥،٠٠٠)
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => $date3,
            'status' => 'absent',
            'user_id' => $this->admin->id,
        ]);

        // پشکنینی دێتەلی مانگ
        $res = $this->getJson("/workshop/employees/{$employee->id}/month-details?month={$monthStr}");
        $res->assertStatus(200);
        $res->assertJson([
            'ok' => true,
            'stats' => [
                'present_count' => 1,
                'half_day_count' => 1,
                'absent_count' => 1,
                'base_earned' => 70000, // 40000 + 30000
                'absent_penalty_deduction' => 15000,
                'total_deductions' => 15000,
                'total_earned' => 55000, // 70000 - 15000
            ],
        ]);
    }

    public function test_home_visit_overtime_and_named_expenses()
    {
        $this->actingAs($this->admin);

        // ڕێکخستنی کاتی زیادەی کارگە (5,000) و کاتی زیادەی ماڵان (8,000)
        $this->postJson('/workshop/settings', [
            'workshop_work_hours' => 8,
            'workshop_weekly_holiday' => 'friday',
            'workshop_overtime_hourly_rate' => 5000,
            'workshop_home_visit_hourly_rate' => 8000,
        ])->assertStatus(200);

        $employee = Employee::create([
            'name' => 'وەستا هەردی',
            'job_title' => 'master',
            'salary_type' => 'daily',
            'daily_wage' => 30000,
            'wage_currency' => 'IQD',
            'is_active' => true,
        ]);

        $today = now()->toDateString();

        // پاشەکەوتکردنی دێتەلی خانەی ڕۆژ بە چوونە ماڵان و ناوی سەرفیات (بەنزین)
        $res = $this->postJson('/workshop/employees/save-cell-detail', [
            'employee_id' => $employee->id,
            'work_date' => $today,
            'status' => 'present',
            'overtime_hours' => 3,
            'trip_destination' => 'ماڵی کاک ئاراس',
            'exit_reason' => 'بەنزینی ئۆتۆمبێل',
            'fuel_expense' => 15000,
            'note' => 'دانانی دەرگای سەرەکی',
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'ok' => true,
            'attendance' => [
                'status' => 'present',
                'overtime_hours' => 3,
                'trip_destination' => 'ماڵی کاک ئاراس',
                'exit_reason' => 'بەنزینی ئۆتۆمبێل',
                'fuel_expense' => 15000,
            ],
        ]);

        // پشکنینی ڕاپۆرتی مانگانە:
        // حەقدەستی ڕۆژ = ٣٠،٠٠٠
        // زیادەی ماڵان = ٣ * ٨،٠٠٠ = ٢٤،٠٠٠ (لەبری ٥،٠٠٠ی کارگە)
        // سەرفیاتی بەنزین = ١٥،٠٠٠
        // کۆی گشتی شایستە = ٣٠،٠٠٠ + ٢٤،٠٠٠ + ١٥،٠٠٠ = ٦٩،٠٠٠ د.ع
        $monthStr = now()->format('Y-m');
        $monthRes = $this->getJson("/workshop/employees/{$employee->id}/month-details?month={$monthStr}");
        $monthRes->assertStatus(200);
        $monthRes->assertJson([
            'ok' => true,
            'stats' => [
                'base_earned' => 30000,
                'overtime_earned' => 24000,
                'total_fuel' => 15000,
                'total_earned' => 69000,
            ],
        ]);
        
        $this->assertEquals('ماڵی کاک ئاراس', $monthRes->json('attendances.0.trip_destination'));
        $this->assertEquals('بەنزینی ئۆتۆمبێل', $monthRes->json('attendances.0.exit_reason'));
    }

    public function test_custom_overtime_job_rates_and_calculations()
    {
        $this->actingAs($this->admin);

        // پاشەکەوتکردنی جۆرەکانی کاری دەرەوە لە سێتینگ
        $settingsRes = $this->postJson('/workshop/settings', [
            'workshop_work_hours' => 8,
            'workshop_weekly_holiday' => 'friday',
            'workshop_overtime_hourly_rate' => 5000,
            'workshop_custom_overtime_rates' => [
                ['name' => 'چوونە ماڵان / دانان', 'rate' => 7000, 'unit' => 'hourly'],
                ['name' => 'ئیشی دەرەوەی شار', 'rate' => 10000, 'unit' => 'hourly'],
                ['name' => 'دانانی قوفڵ و بەستن', 'rate' => 15000, 'unit' => 'fixed'],
            ],
        ]);
        $settingsRes->assertStatus(200);

        // پشکنینی پاشەکەوتکردنی سێتینگ
        $savedRates = json_decode(Setting::get('workshop_custom_overtime_rates'), true);
        $this->assertCount(3, $savedRates);
        $this->assertEquals('ئیشی دەرەوەی شار', $savedRates[1]['name']);
        $this->assertEquals(10000, $savedRates[1]['rate']);

        // دروستکردنی وەستایەک بە کرێی ٤٠،٠٠٠ د.ع
        $employee = Employee::create([
            'name' => 'وەستا هەردی',
            'job_title' => 'master',
            'salary_type' => 'daily',
            'daily_wage' => 40000,
            'wage_currency' => 'IQD',
            'is_active' => true,
        ]);

        $today = now()->isFriday() ? now()->subDay()->toDateString() : now()->toDateString();

        // تۆمارکردنی دەوام و دیاریکردنی کاری دەرەوە (ئیشی دەرەوەی شار، ٢.٥ کاتژمێر = ٢٥،٠٠٠ د.ع)
        $cellRes = $this->postJson('/workshop/employees/save-cell-detail', [
            'employee_id' => $employee->id,
            'work_date' => $today,
            'status' => 'present',
            'custom_task_name' => 'ئیشی دەرەوەی شار',
            'custom_task_rate' => 10000,
            'custom_task_unit' => 'hourly',
            'custom_task_hours' => 2.5,
            'custom_task_amount' => 25000,
            'trip_destination' => 'کۆیە - باخی کاک فاروق',
            'exit_reason' => 'کڕینی مەواد',
            'fuel_expense' => 20000,
            'note' => 'تەواوکردنی کاری دەرەوە',
        ]);

        $cellRes->assertStatus(200);
        $cellRes->assertJson([
            'ok' => true,
            'attendance' => [
                'status' => 'present',
                'custom_task_name' => 'ئیشی دەرەوەی شار',
                'custom_task_rate' => 10000,
                'custom_task_hours' => 2.5,
                'custom_task_amount' => 25000,
                'trip_destination' => 'کۆیە - باخی کاک فاروق',
                'exit_reason' => 'کڕینی مەواد',
                'fuel_expense' => 20000,
            ],
        ]);

        // پشکنینی حیساباتی مانگانە بە وردی:
        // حەقدەستی ڕۆژ = ٤٠،٠٠٠
        // حەقدەستی کاری دەرەوە = ٢٥،٠٠٠
        // سەرفیاتی مەواد/بەنزین = ٢٠،٠٠٠
        // کۆی گشتی شایستەی دارایی = ٤٠،٠٠٠ + ٢٥،٠٠٠ + ٢٠،٠٠٠ = ٨٥،٠٠٠ د.ع مەزبوت
        $monthStr = now()->format('Y-m');
        $monthRes = $this->getJson("/workshop/employees/{$employee->id}/month-details?month={$monthStr}");
        $monthRes->assertStatus(200);
        $monthRes->assertJson([
            'ok' => true,
            'stats' => [
                'base_earned' => 40000,
                'overtime_earned' => 25000,
                'total_fuel' => 20000,
                'total_earned' => 85000,
            ],
        ]);

        $this->assertEquals('ئیشی دەرەوەی شار', $monthRes->json('attendances.0.custom_task_name'));
        $this->assertEquals(25000, $monthRes->json('attendances.0.custom_task_amount'));
    }

    public function test_wasta_role_cannot_see_money_or_admin_actions()
    {
        $employee = Employee::create([
            'name' => 'وەستا سامان',
            'job_title' => 'master',
            'salary_type' => 'daily',
            'daily_wage' => 50000,
            'wage_currency' => 'IQD',
            'is_active' => true,
        ]);

        // 1. ڕۆڵی وەستا (Storekeeper / Wasta):
        $this->actingAs($this->storekeeper);

        $viewRes = $this->get('/workshop/employees');
        $viewRes->assertStatus(200);
        $this->assertFalse($viewRes->viewData('canSeeMoney'));
        $viewRes->assertSee('وەستا سامان');
        $viewRes->assertSee('جەدوەلی ئامادەبوونی ڕۆژانەی کارمەندان');
        // نابێت دوگمە و دەسەڵاتە داراییەکانی لای بەڕێوەبەر ببینێت
        $viewRes->assertDontSee('سێتینگی تاخیربوون و دەوام');
        $viewRes->assertDontSee('ڕێکخستنی یاسا و مەرجەکانی دەوام');
        $viewRes->assertDontSee('کردارەکان');

        // لە داتای matrix دا مووچە و حیسابات دەبێت سفر کرابێت
        $matrix = $viewRes->viewData('employeesMatrix');
        $this->assertNotEmpty($matrix);
        $this->assertEquals(0, $matrix[0]['daily_wage']);
        $this->assertEquals(0, $matrix[0]['total_earned']);
        $this->assertEquals(0, $matrix[0]['remaining_balance']);

        // وەستا تەنها دەتوانێت دەوام تۆمار بکات (سەح / ئامادەبوون / غیاب / نیو ڕۆژ)
        $toggleRes = $this->postJson('/workshop/employees/toggle-cell', [
            'employee_id' => $employee->id,
            'work_date' => now()->toDateString(),
            'status' => 'half_day',
        ]);
        $toggleRes->assertStatus(200);
        $toggleRes->assertJson(['ok' => true, 'status' => 'half_day']);

        // ئەگەر وەستا ڕاستەوخۆ هەوڵی دەستکاریکردنی مووچە یان سێتینگ یان پارەدان بدات ڕەتدەکرێتەوە (403)
        $this->postJson('/workshop/settings', [
            'workshop_work_hours' => 8,
            'workshop_weekly_holiday' => 'friday',
        ])->assertStatus(403);

        $this->postJson('/workshop/employees/quick-store', [
            'name' => 'وەستای قەدەغەکراو',
            'job_title' => 'master',
        ])->assertStatus(403);

        $this->postJson("/workshop/employees/{$employee->id}/update-wage", [
            'name' => $employee->name,
            'job_title' => 'master',
            'daily_wage' => 60000,
        ])->assertStatus(403);

        $this->getJson("/workshop/employees/{$employee->id}/month-details")->assertStatus(403);

        $this->deleteJson("/workshop/employees/{$employee->id}")->assertStatus(403);
    }

    public function test_admin_role_can_see_money_and_all_actions()
    {
        $employee = Employee::create([
            'name' => 'وەستا کامەران',
            'job_title' => 'master',
            'salary_type' => 'daily',
            'daily_wage' => 45000,
            'wage_currency' => 'IQD',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);

        $adminViewRes = $this->get('/workshop/employees');
        $adminViewRes->assertStatus(200);
        $this->assertTrue($adminViewRes->viewData('canSeeMoney'));
        $adminViewRes->assertSee('سێتینگی تاخیربوون و دەوام');
        $adminViewRes->assertSee('ڕێکخستنی یاسا و مەرجەکانی دەوام');
        $adminViewRes->assertSee('کردارەکان');

        $adminMatrix = $adminViewRes->viewData('employeesMatrix');
        $this->assertEquals(45000, $adminMatrix[0]['daily_wage']);
    }
}

