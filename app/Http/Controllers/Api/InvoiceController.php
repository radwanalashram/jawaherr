<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->validate([
            'number' => 'required|string|unique:invoices,number',
            'type' => 'required|string',
            'party_id' => 'nullable|string',
            'date' => 'nullable|date',
            'currency_id' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'nullable|string',
            'lines.*.qty' => 'required|numeric',
            'lines.*.unit_price' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'id' => (string) Str::uuid(),
                'number' => $payload['number'],
                'type' => $payload['type'],
                'party_id' => $payload['party_id'] ?? null,
                'date' => $payload['date'] ?? now(),
                'currency_id' => $payload['currency_id'] ?? null,
                'status' => 'posted',
                'payment_type' => $request->get('payment_type','cash'),
                'created_by' => $request->user() ? $request->user()->id : null
            ]);

            $subtotal = 0;
            foreach ($payload['lines'] as $ln) {
                $lineTotal = $ln['qty'] * $ln['unit_price'];
                $subtotal += $lineTotal;
                InvoiceLine::create([
                    'id' => (string) Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'item_id' => $ln['item_id'] ?? null,
                    'description' => $ln['description'] ?? null,
                    'unit_id' => $ln['unit_id'] ?? null,
                    'qty' => $ln['qty'],
                    'unit_price' => $ln['unit_price'],
                    'discount' => $ln['discount'] ?? 0,
                    'line_total' => $lineTotal
                ]);

                // update stock_total (basic) and create stock_movement omitted for brevity
                if (!empty($ln['item_id'])) {
                    $item = Item::find($ln['item_id']);
                    if ($item && $item->track_stock) {
                        $item->stock_total = max(0, $item->stock_total - $ln['qty']);
                        $item->save();
                    }
                }
            }

            $invoice->subtotal = $subtotal;
            $invoice->total = $subtotal; // taxes/discounts handled later
            $invoice->save();

            DB::commit();
            return response()->json($invoice->load('lines'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message'=>'خطأ عند إنشاء الفاتورة','error'=>$e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $inv = Invoice::with('lines')->findOrFail($id);
        return response()->json($inv);
    }
}