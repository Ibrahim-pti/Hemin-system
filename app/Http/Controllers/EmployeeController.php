<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        return view('employees.index', [
            'employees' => Employee::withCount('attendances')->orderBy('name')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('employees.form', [
            'employee' => new Employee(['wage_currency' => 'IQD', 'job_title' => 'master', 'is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $employee = Employee::create($this->validated($request));

        return redirect()->route('employees.show', $employee)->with('ok', 'کارمەند زیادکرا.');
    }

    public function show(Employee $employee, Request $request): View
    {
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->endOfMonth()->toDateString();

        return view('employees.show', [
            'employee' => $employee,
            'attendances' => $employee->attendances()
                ->whereBetween('work_date', [$from, $to])
                ->orderByDesc('work_date')
                ->get(),
            'earned' => $employee->earnedBetween($from, $to),
            'paid' => $employee->paidBetween($from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function edit(Employee $employee): View
    {
        return view('employees.form', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $employee->update($this->validated($request));

        return redirect()->route('employees.show', $employee)->with('ok', 'نوێکرایەوە.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->attendances()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — تۆماری ئامادەبوونی هەیە. ناچالاکی بکە.');
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('ok', 'سڕدرایەوە.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['required', 'in:'.implode(',', array_keys(Employee::JOB_TITLES))],
            'daily_wage' => ['nullable', 'numeric', 'min:0'],
            'wage_currency' => ['required', 'in:IQD,USD'],
            'hire_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ], [], ['name' => 'ناو']);

        $data['daily_wage'] = $data['daily_wage'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
