<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->search($request->string('q')->toString())
            ->withCount('purchases')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.form', ['supplier' => new Supplier(['opening_currency' => 'IQD', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($this->validated($request));

        return redirect()->route('suppliers.show', $supplier)->with('ok', 'فرۆشیار زیادکرا.');
    }

    /** پرۆفایلی فرۆشیار — مێژووی مامەڵە و باڵانس. */
    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', [
            'supplier' => $supplier,
            'purchases' => $supplier->purchases()->latest('purchase_date')->latest('id')->limit(50)->get(),
            'payments' => $supplier->payments()->where('direction', 'out')->latest('paid_at')->limit(50)->get(),
            'jobs' => $supplier->externalJobs()->latest('id')->limit(20)->get(),
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request));

        return redirect()->route('suppliers.show', $supplier)->with('ok', 'نوێکرایەوە.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — ئەم فرۆشیارە پسوولەی کڕینی هەیە.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('ok', 'سڕدرایەوە.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'phone2' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric'],
            'opening_currency' => ['required', 'in:IQD,USD'],
            'note' => ['nullable', 'string'],
        ], [], ['name' => 'ناو']);

        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
