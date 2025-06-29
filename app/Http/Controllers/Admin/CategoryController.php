<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = TicketCategory::withCount(['tickets', 'subcategories'])
            ->ordered()
            ->paginate(10);
        
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        // Debug
        Log::info('Category store method called');
        Log::info($request->all());

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ticket_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            Log::info('Validation failed', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $category = TicketCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'sort_order' => $request->sort_order ?? 0
            ]);
            
            Log::info('Category created successfully', ['id' => $category->id]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create category: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(TicketCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, TicketCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ticket_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(TicketCategory $category)
    {
        if ($category->tickets()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category that has tickets assigned to it.');
        }

        if ($category->subcategories()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category that has subcategories assigned to it.');
        }

        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}