<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $jobTitle = $request->input('job_title', 'all');

        $query = Employee::query()
            ->withCount('attendances')
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                        ->orWhere('job_title', 'like', "%{$s}%");
                });
            })
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('is_active', $status === 'active');
            })
            ->when($jobTitle !== 'all', function ($q) use ($jobTitle) {
                $q->where('job_title', $jobTitle);
            })
            ->orderByRaw("FIELD(job_title, 'master', 'porter', 'helper', 'driver', 'other')")
            ->orderBy('name');

        $employees = $query->paginate(30)->withQueryString();

        $allEmployees = Employee::all();
        $totalCount = $allEmployees->count();
        $activeCount = $allEmployees->where('is_active', true)->count();
        $mastersCount = $allEmployees->where('is_active', true)->where('job_title', 'master')->count();
        $portersCount = $allEmployees->where('is_active', true)->whereIn('job_title', ['porter', 'helper'])->count();

        $jobTitles = Employee::select('job_title')->distinct()->pluck('job_title')->all();

        return view('employees.index', compact(
            'employees',
            'totalCount',
            'activeCount',
            'mastersCount',
            'portersCount',
            'jobTitles',
            'search',
            'status',
            'jobTitle'
        ));
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
        $jobTitle = $request->input('job_title');
        if ($jobTitle === '__NEW__' || !empty($request->input('custom_job_title'))) {
            $jobTitle = trim($request->input('custom_job_title') ?: $jobTitle);
        }
        $request->merge(['job_title' => $jobTitle ?: 'other']);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['required', 'string', 'max:100'],
            'daily_wage' => ['nullable', 'numeric', 'min:0'],
            'wage_currency' => ['required', 'in:IQD,USD'],
            'hire_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ], [], ['name' => 'ناو', 'job_title' => 'پیشە']);

        $data['daily_wage'] = $data['daily_wage'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
