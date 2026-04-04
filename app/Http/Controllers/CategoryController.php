<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // List all categories
    public function index()
    {
        $query = Category::query();
        
        $vendor = null;
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            $query->where(function($q) use ($vendor) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendor?->id);
            });
        }

        $categories = $query->paginate(50);
        return view('admin.categories.index', compact('categories', 'vendor'));
    }

    // Show form to create a new category
    public function create()
    {
        $query = Category::query();
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            $query->where(function($q) use ($vendor) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendor->id);
            });
        }
        $categories = $query->get(); // For parent selection
        return view('admin.categories.create', compact('categories'));
    }

    // Store a new category
    public function store(Request $request)
    {
        if ($request->parent_id === '' || $request->parent_id === 'null') {
            $request->merge(['parent_id' => null]);
        }
         
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'synonyms' => 'nullable|string',
        ]);

        $synonyms = isset($validated['synonyms']) 
            ? array_map('trim', explode(',', $validated['synonyms'])) 
            : [];
            
        unset($validated['synonyms']);

        // Check for duplicates in same leaf
        if (Category::where('name', $validated['name'])->where('parent_id', $validated['parent_id'])->exists()) {
             return redirect()->back()->withInput()->with('error', 'This category already exists or is pending approval in this branch.');
        }

        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            $validated['vendor_id'] = $vendor->id;
            $validated['status'] = 'pending';
        } else {
            $validated['status'] = 'approved';
        }

        $validated['synonyms'] = $synonyms;
        $category = Category::create($validated);
        
        // Dispatch background job to sync with Node.js embedding
        \App\Jobs\SyncCategoryData::dispatch($category);

        return redirect()->route('admin.categories.index')->with('success', 'Category suggestion created successfully.');
    }

    public function approve(Category $category)
    {
        if (!auth()->user()->hasRole('super.admin')) {
            abort(403);
        }
        $category->update(['status' => 'approved']);
        
        // Dispatch background job to sync with Node.js embedding
        \App\Jobs\SyncCategoryData::dispatch($category);

        return redirect()->route('admin.categories.index')->with('success', 'Category approved successfully.');
    }

    // Show form to edit a category
    public function edit(Category $category)
    {
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            if ($category->vendor_id !== $vendor->id) {
                abort(403, 'Unauthorized to edit this category.');
            }
        }
        
        $query = Category::where('id', '!=', $category->id);
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            $query->where(function($q) use ($vendor) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendor->id);
            });
        }
        $categories = $query->get();
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    // Update a category
    public function update(Request $request, Category $category)
    {

       if ($request->parent_id === '' || $request->parent_id === 'null') {
            $request->merge(['parent_id' => null]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'synonyms' => 'nullable|string',
        ]);

        $synonyms = $request->synonyms 
            ? array_map('trim', explode(',', $request->synonyms)) 
            : [];

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'synonyms' => $synonyms,
        ]);

        // Lazy Sync: Mark ALL products in this category branch for re-indexing
        $allIds = $category->getAllDescendantIds();
        \App\Models\Product::whereIn('category_id', $allIds)->update(['needs_reindex' => true]);

        // Dispatch background job to sync with Node.js embedding
        \App\Jobs\SyncCategoryData::dispatch($category);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully. Related products will be re-indexed in the background.');
    }

    // Delete a category
    public function destroy(Category $category)
    {
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            if ($category->vendor_id !== $vendor->id) {
                abort(403, 'Unauthorized to delete this category.');
            }
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
