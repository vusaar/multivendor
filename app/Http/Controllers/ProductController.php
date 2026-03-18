<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariationImage;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariation;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    // Display a listing of the products
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'vendor_id', 'category_id', 'status', 'price_min', 'price_max']);

        if (auth()->user()->hasRole('vendor.admin')) {
            $vendors = \App\Models\Vendor::where('user_id', auth()->id())->get();
            $filters['vendor_ids'] = $vendors->pluck('id');
        } elseif (auth()->user()->hasRole('super.admin')) {
            $vendors = \App\Models\Vendor::all();
        } else {
            abort(403, 'Unauthorized user.');
        }

        $products = $this->productService->getFilteredProducts($filters);
        $products->appends($request->query());

        $categories = \App\Models\Category::all();

        return view('admin.products.index', compact('products', 'vendors', 'categories'));
    }

    // Show the form for creating a new product
    public function create()
    {
        $vendors = Vendor::all();

        $brands = Brand::all();

      
        $categories = Category::with('children')->whereNull('parent_id')->get();
        $categories = Category::with('children')->whereNull('parent_id')->get();
        $categories = Category::with('children')->whereNull('parent_id')->get();

        return view('admin.products.create', compact('vendors', 'categories','brands'));
    }

    // Store a newly created product in storage
    public function store(Request $request)
    {

        // dd($request->all());
        $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|max:2048',
        ]);

        try {
            $this->productService->createProduct(
                $request->all(),
                $request->file('images', []),
                $request->input('variations', [])
            );
        } catch(\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }

        

        return redirect()->back()->with('success', 'Product created successfully.');
    }

    // Display the specified product
    public function show(Product $product)
    {
        $product->load(['vendor','brand', 'category', 'images']);
        return view('admin.products.show', compact('product'));
    }

    // Show the form for editing the specified product
    public function edit(Product $product)
    {
    $vendors = Vendor::all();
    $categories = Category::with('children')->whereNull('parent_id')->get();
    $brands = Brand::all();
   
    $product->load(['images', 'variations.attributeValues', 'variations.variationImages']);
    $variationAttributes = \App\Models\VariationAttribute::with('values')->get();
    
    return view('admin.products.edit', compact('product', 'vendors', 'categories', 'brands', 'variationAttributes'));
    }

    // Update the specified product in storage
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|max:2048',
        ]);


        //dd($request->all());

        try {
            $this->productService->updateProduct(
                $product,
                $request->all(),
                $request->input('existing_images', []),
                $request->file('images', []),
                $request->input('variations', [])
            );
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    // Remove the specified product from storage
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    // Remove a specific image from a product
    public function destroyImage(ProductImage $image)
    {
        $image->delete();
        return back()->with('success', 'Product image deleted successfully.');
    }


}
