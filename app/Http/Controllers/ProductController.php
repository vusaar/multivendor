<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariationImage;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariation;
use App\Models\Vendor;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // Display a listing of the products
    public function index(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'images']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        /*
           if user has vendor.admin role, get vendors that belong to that user
           This is useful for vendor admins to only see their own products 
        */
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendors = Vendor::where('user_id', auth()->id())->get();
            $query->whereIn('vendor_id', $vendors->pluck('id'));
        } elseif (auth()->user()->hasRole('super.admin')) {
            $vendors = Vendor::all();
        }else{
            abort(403, 'Unauthorized user.');
        }
        
        $categories = Category::all();

        $products = $query->paginate(15)->appends($request->query());
        return view('admin.products.index', compact('products', 'vendors', 'categories'));
    }

    // Show the form for creating a new product
    public function create()
    {
        $vendors = Vendor::all();

      
        $categories = Category::with('children')->whereNull('parent_id')->get();

        return view('admin.products.create', compact('vendors', 'categories'));
    }

    // Store a newly created product in storage
    public function store(Request $request)
    {

        // dd($request->all());
        $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|max:2048',
        ]);

        try{

        DB::beginTransaction(); 
            $product = Product::create($request->only([
                'vendor_id', 'category_id', 'name', 'description', 'price', 'stock', 'status'
            ]));

            // Handle product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_images', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                    ]);
                }
            }

            // Handle product variations (matrix)
            if ($request->has('variation_matrix')) {
                foreach ($request->input('variation_matrix') as $matrix) {
                    
                    

                    $variation = ProductVariation::create([
                        'product_id' => $product->id,
                        'sku' => $matrix['sku'] ?? null,
                        'price' => $matrix['price'] ?? null,
                        'stock' => $matrix['stock'] ?? 0,
                    ]);

                   // dd($request->file('variation_matrix'));

                    foreach($request->file('variation_matrix') as $variation_image){

                    if ($variation_image['image']) {

                        //dd($variation_image['image']);
                        $variation_image_path = $variation_image['image']->store('variation_images', 'public');
                        ProductVariationImage::create([
                            'product_variation_id' => $variation->id,
                            'image_path' => $variation_image_path,
                            'alt_text' => $matrix['sku'] ?? null,
                        ]);
                    }
                    }

                   // dd($request->file('variation_matrix'));
                    // Save variation image if present
                   


                    /* 
                      Save attribute values for this variation
                    */

                    $attrValueIds = [];
                    if (isset($matrix['attributes']) && is_array($matrix['attributes'])) {
                        foreach ($matrix['attributes'] as $pair) {
                            if (empty($pair['attribute_id']) || empty($pair['value_id']) || !is_array($pair['value_id'])) continue;
                            foreach ($pair['value_id'] as $valId) {
                                if (!$valId) continue;
                                $attrValueIds[] = $valId;
                            }
                        }
                    }

                   // dd($attrValueIds);
                    $variation->attributeValues()->sync($attrValueIds);


                    



                }
            }

            DB::commit();

            }catch(\Exception $e){
                // Handle any exceptions that occur during the transaction
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
            }

        

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    // Display the specified product
    public function show(Product $product)
    {
        $product->load(['vendor', 'category', 'images']);
        return view('admin.products.show', compact('product'));
    }

    // Show the form for editing the specified product
    public function edit(Product $product)
    {
        $vendors = Vendor::all();
        $categories = Category::with('children')->whereNull('parent_id')->get();
        $product->load('images');
        return view('admin.products.edit', compact('product', 'vendors', 'categories'));
    }

    // Update the specified product in storage
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|max:2048',
        ]);


        //dd($request->all());

        DB::transaction(function () use ($request, $product) {
            $product->update($request->only([
                'vendor_id', 'category_id', 'name', 'description', 'price', 'stock', 'status'
            ]));

            // Handle product images: delete removed images
            $keepImageIds = $request->input('existing_images', []);
            $imagesToDelete = $product->images()->whereNotIn('id', $keepImageIds)->get();
            foreach ($imagesToDelete as $img) {
                if (Storage::disk('public')->exists($img->image)) {
                    Storage::disk('public')->delete($img->image);
                }
                $img->delete();
            }
            // Re-fetch images to update the order if needed
            $remainingImages = $product->images()->orderBy('id')->get();
            // Optionally, you could re-sequence or update any other fields here if your table has an 'order' or similar column

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_images', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                    ]);
                }
            }

            // Handle product variations update (multiple values per variation)
            $existingVariations = $product->variations()->get();
            $submitted = $request->input('variations', []);

           

            $keepVariationIds = [];
            $variation_count = 0;
            foreach ($submitted as $variation) {

                // Collect all attribute value IDs for this variation (Blade sends value IDs directly)
                $attrValueIds = [];
                if (isset($variation['attributes']) && is_array($variation['attributes'])) {
                    foreach ($variation['attributes'] as $pair) {
                        if (empty($pair['attribute_id']) || empty($pair['value_id']) || !is_array($pair['value_id'])) continue;
                        foreach ($pair['value_id'] as $valId) {
                            if (!$valId) continue;
                            $attrValueIds[] = $valId;
                        }
                    }
                }
                if (count($attrValueIds) === 0) continue;

                // Use variation ID if present for update, else create new
                if (!empty($variation['id'])) {
                    $productVariation = ProductVariation::find($variation['id']);
                    if ($productVariation) {
                        $productVariation->update([
                            'sku' => $variation['sku'] ?? null,
                            'price' => $variation['price'] ?? $request->price,
                            'stock' => $variation['stock'] ?? $request->stock,
                        ]);
                    } else {
                        $productVariation = ProductVariation::create([
                            'product_id' => $product->id,
                            'sku' => $variation['sku'] ?? null,
                            'price' => $variation['price'] ?? $request->price,
                            'stock' => $variation['stock'] ?? $request->stock,
                        ]);
                    }
                } else {
                    $productVariation = ProductVariation::create([
                        'product_id' => $product->id,
                        'sku' => $variation['sku'] ?? null,
                        'price' => $variation['price'] ?? $request->price,
                        'stock' => $variation['stock'] ?? $request->stock,
                    ]);
                }
                $productVariation->attributeValues()->sync($attrValueIds);
                $keepVariationIds[] = $productVariation->id;

                //  dd($productVariation->variationImages);
                //  dd($request->file('variations')[$variation_count]);
                // Save or update variation image
                if (isset($request->file('variations')[$variation_count]) ) {
                    // Delete old image if exists
                    $oldImages = $productVariation->variationImages;

                    //dd($oldImage);
                    if ($oldImages) {
                        foreach ($oldImages as $oldImage) {
                        if (Storage::disk('public')->exists($oldImage->image_path)) {
                            Storage::disk('public')->delete($oldImage->image_path);
                        }
                        $oldImage->delete();
                      }
                    }
                    try {

                        $variationImagePath = $request->file('variations')[$variation_count]['image']->store('variation_images', 'public');

                        ProductVariationImage::create([
                            'product_variation_id' => $productVariation->id,
                            'image_path' => $variationImagePath,
                            'alt_text' => $variation['sku'] ?? null,
                        ]);

                    } catch (\Exception $e) {
                        // Optionally log error or handle as needed
                    }
                }

                $variation_count++;
            }
            // Delete removed variations
            $product->variations()->whereNotIn('id', $keepVariationIds)->delete();
        });

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
