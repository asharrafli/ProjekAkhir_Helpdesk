<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = TicketSubcategory::with('category')
            ->withCount('tickets')
            ->ordered()
            ->paginate(10);
        
        return view('admin.subcategories.index', compact('subcategories'));
    }

    public function create()
    {
        $categories = TicketCategory::active()->ordered()->get();
        return view('admin.subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:ticket_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        TicketSubcategory::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory created successfully.');
    }

    public function edit(TicketSubcategory $subcategory)
    {
        $categories = TicketCategory::active()->ordered()->get();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(Request $request, TicketSubcategory $subcategory)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:ticket_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $subcategory->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory updated successfully.');
    }

    public function destroy(TicketSubcategory $subcategory)
    {
        if ($subcategory->tickets()->exists()) {
            return redirect()->route('admin.subcategories.index')
                ->with('error', 'Cannot delete subcategory that has tickets assigned to it.');
        }

        $subcategory->delete();
        
        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory deleted successfully.');
    }

    public function getByCategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategories = TicketSubcategory::byCategory($categoryId)
            ->active()
            ->ordered()
            ->get(['id', 'name']);
        
        return response()->json($subcategories);
    }
}