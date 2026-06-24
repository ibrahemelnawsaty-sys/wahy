<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ShopManagementController extends Controller
{
    public function index()
    {
        $items = ShopItem::orderBy('order')->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total_items' => ShopItem::count(),
            'active_items' => ShopItem::where('status', 'active')->count(),
            'sold_out' => ShopItem::where('status', 'sold_out')->count(),
            'total_purchases' => DB::table('user_purchases')->count(),
        ];
        
        return view('admin.shop.index', compact('items', 'stats'));
    }

    public function create()
    {
        return view('admin.shop.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:avatar,theme,badge,power_up,special',
            'price' => 'required|integer|min:1',
            'icon' => 'required|string|max:10',
            'image' => 'nullable|image|max:2048',
            'stock' => 'nullable|integer|min:0',
            'is_limited' => 'boolean',
            'available_until' => 'nullable|date',
            'rarity' => 'required|in:common,rare,epic,legendary',
            'status' => 'required|in:active,inactive',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('shop', 'public');
        }

        ShopItem::create($validated);

        return redirect()->route('admin.shop.index')->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function edit($id)
    {
        $item = ShopItem::findOrFail($id);
        return view('admin.shop.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ShopItem::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:avatar,theme,badge,power_up,special',
            'price' => 'required|integer|min:1',
            'icon' => 'required|string|max:10',
            'image' => 'nullable|image|max:2048',
            'stock' => 'nullable|integer|min:0',
            'is_limited' => 'boolean',
            'available_until' => 'nullable|date',
            'rarity' => 'required|in:common,rare,epic,legendary',
            'status' => 'required|in:active,inactive,sold_out',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image && \Storage::disk('public')->exists($item->image)) {
                \Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('shop', 'public');
        }

        $item->update($validated);

        return redirect()->route('admin.shop.index')->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function destroy($id)
    {
        $item = ShopItem::findOrFail($id);
        
        if ($item->image && \Storage::disk('public')->exists($item->image)) {
            \Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف المنتج بنجاح']);
    }
}

