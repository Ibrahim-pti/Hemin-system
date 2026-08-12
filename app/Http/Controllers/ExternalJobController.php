<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\ExternalJob;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExternalJobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = ExternalJob::query()
            ->with(['order.customer', 'supplier'])
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(
                fn ($w) => $w->where('title', 'like', "%{$term}%")
                    ->orWhere('job_no', 'like', "%{$term}%")
                    ->orWhere('contractor_name', 'like', "%{$term}%")
            ))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('external-jobs.index', [
            'jobs' => $jobs,
            'totalCost' => (float) ExternalJob::where('status', '!=', 'cancelled')->sum(ExternalJob::costIqdExpression()),
        ]);
    }

    public function create(Request $request): View
    {
        return view('external-jobs.form', [
            'job' => new ExternalJob([
                'currency' => 'IQD',
                'status' => 'open',
                'started_at' => now()->toDateString(),
                'order_id' => $request->integer('order') ?: null,
            ]),
            'orders' => Order::with('customer')->latest('id')->limit(200)->get(),
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'rate' => ExchangeRate::current(),
        ]);
    }

    public function store(Request $request)
    {
        $job = ExternalJob::create($this->validated($request) + [
            'job_no' => ExternalJob::nextJobNo(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('external-jobs.index')->with('ok', "ئیشی خاریجی {$job->job_no} زیادکرا.");
    }

    public function show(ExternalJob $job): View
    {
        return $this->edit($job);
    }

    public function edit(ExternalJob $job): View
    {
        return view('external-jobs.form', [
            'job' => $job,
            'orders' => Order::with('customer')->latest('id')->limit(200)->get(),
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'rate' => ExchangeRate::current(),
        ]);
    }

    public function update(Request $request, ExternalJob $job)
    {
        $job->update($this->validated($request));

        return redirect()->route('external-jobs.index')->with('ok', 'نوێکرایەوە.');
    }

    public function destroy(ExternalJob $job)
    {
        $job->delete();

        return redirect()->route('external-jobs.index')->with('ok', 'سڕدرایەوە.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'contractor_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cost' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_keys(ExternalJob::STATUSES))],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'note' => ['nullable', 'string'],
        ], [
            'finished_at.after_or_equal' => 'بەرواری تەواوبوون ناتوانێت پێش دەستپێکردن بێت.',
        ], ['title' => 'ناونیشان', 'cost' => 'تێچوو']);

        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        if ($data['currency'] === 'USD' && empty($data['exchange_rate'])) {
            $data['exchange_rate'] = ExchangeRate::current();
        }

        return $data;
    }
}
