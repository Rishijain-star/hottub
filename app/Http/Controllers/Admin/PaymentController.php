<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index()
    {
        $revenue = Invoice::where('status','paid')->sum('amount');
        $pending = Invoice::where('status','pending')->sum('amount');
        $completed = Invoice::where('status','paid')->count();
        $failed = Invoice::where('status','failed')->count();
        $items = Invoice::orderBy('created_at','desc')->paginate(6);
        return view('admin.payments', compact('revenue','pending','completed','failed','items'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = 'INV-'.now()->format('YmdHis').'-'.Str::random(5);
        }
        Invoice::create($data);
        return redirect()->route('admin.payments')->with('success', 'Invoice created.');
    }

    public function edit(Invoice $invoice)
    {
        $items = Invoice::orderBy('created_at','desc')->paginate(6);
        return view('admin.payments-edit', ['item'=>$invoice, 'items'=>$items]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validateData($request);
        if ($request->boolean('regen_number') || empty($invoice->invoice_number)) {
            $data['invoice_number'] = 'INV-'.now()->format('YmdHis').'-'.Str::random(5);
        }
        $invoice->update($data);
        return redirect()->route('admin.payments')->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success', 'Invoice deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'invoice_number' => ['nullable','string','max:255'],
            'dealer_id' => ['nullable','integer'],
            'credits' => ['required','integer','min:0'],
            'amount' => ['required','numeric','min:0'],
            'status' => ['required','in:paid,pending,failed'],
            'payment_id' => ['nullable','string','max:255'],
        ]);
    }
}
