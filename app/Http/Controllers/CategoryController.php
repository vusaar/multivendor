<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // List all categories
    public function index()
    {
        $categories = Category::paginate(50);
        return view('admin.categories.index', compact('categories'));
    }

    // Show form to create a new category
    public function create()
    {
        $categories = Category::all(); // For parent selection
        return view('admin.categories.create', compact('categories'));
    }

    // Store a new category
    public function store(Request $request)
    {
        if ($request->parent_id === '' || $request->parent_id === 'null') {
            $request->merge(['parent_id' => null]);
        }
         
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    // Show form to edit a category
    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get(); // Exclude self
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
        ]);
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    // Delete a category
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
