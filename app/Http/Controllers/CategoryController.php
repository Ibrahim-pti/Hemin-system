<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', [
            'categories' => ItemCategory::withCount('items')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:item_categories,name'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [], ['name' => 'ناوی جۆر']);

        $category = ItemCategory::create($data);

        return back()->with('ok', "جۆری «{$category->name}» زیادکرا.");
    }

    public function update(Request $request, ItemCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:item_categories,name,'.$category->id],
            'note' => ['nullable', 'string', 'max:500'],
        ], [], ['name' => 'ناوی جۆر']);

        $category->update($data);

        return back()->with('ok', 'جۆرەکە نوێکرایەوە.');
    }

    public function destroy(ItemCategory $category)
    {
        if ($category->items()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — بابەتی پەیوەستکراو بەم جۆرە هەیە.');
        }

        $category->delete();

        return back()->with('ok', 'جۆرەکە سڕدرایەوە.');
    }
}
