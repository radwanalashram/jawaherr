<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::paginate(25);
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'nullable|string|unique:items,sku',
            'name' => 'required|string',
            'barcode' => 'nullable|string',
            'sale_price' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'stock_total' => 'nullable|numeric'
        ]);

        $item = Item::create($data);
        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);
        return response()->json($item);
    }
}