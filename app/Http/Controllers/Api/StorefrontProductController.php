<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class StorefrontProductController extends Controller
{
    // Search and filter products for the storefront, return JSON
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
        // Add more filters as needed (e.g., stock, featured, etc.)

        $products = $query->paginate($request->get('per_page', 15))->appends($request->query());

        // Format the response for API consumers
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'status' => $product->status,
                'vendor' => $product->vendor ? [
                    'id' => $product->vendor->id,
                    'shop_name' => $product->vendor->shop_name,
                ] : null,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'images' => $product->images->map(function ($img) {
                    return asset('storage/' . ($img->image ?? $img->image_path));
                }),
                'variations' => $product->variations ? $product->variations->map(function ($variation) {
                    return [
                        'id' => $variation->id,
                        'sku' => $variation->sku,
                        'price' => $variation->price,
                        'stock' => $variation->stock,
                        'attribute_values' => $variation->attributeValues->map(function ($attrValue) {
                            return [
                                'id' => $attrValue->id,
                                'value' => $attrValue->value,
                                'attribute_id' => $attrValue->variation_attribute_id,
                            ];
                        }),
                    ];
                }) : [],
            ];
        });

        return response()->json($products);
    }

    // Show a single product by ID (for API)
    public function show($id)
    {
        $product = \App\Models\Product::with(['vendor', 'category', 'images'])->findOrFail($id);
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'vendor' => $product->vendor ? [
                'id' => $product->vendor->id,
                'shop_name' => $product->vendor->shop_name,
            ] : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'images' => $product->images->map(function ($img) {
                return asset('storage/' . ($img->image ?? $img->image_path));
            }),
        ]);
    }

    // Flexible search for products using only provided parameters
    public function search(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'images', 'variations.attributeValues']);

        // Only search using fields that represent names
        if ($request->filled('product_name')) {
            $query->where('name', 'like', "%{$request->product_name}%");
        }
        if ($request->filled('vendor_name')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('shop_name', 'like', "%{$request->vendor_name}%");
            });
        }
        if ($request->filled('category_name')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->category_name}%");
            });
        }
        // Search by variation attribute name and/or value
        if ($request->filled('attribute_name')) {
            $query->whereHas('variations.attributeValues.attribute', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->attribute_name}%");
            });
        }
        if ($request->filled('attribute_value')) {
            $query->whereHas('variations.attributeValues', function ($q) use ($request) {
                $q->where('value', 'like', "%{$request->attribute_value}%");
            });
        }

        $products = $query->paginate($request->get('per_page', 15))->appends($request->query());

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'status' => $product->status,
                'vendor' => $product->vendor ? [
                    'id' => $product->vendor->id,
                    'shop_name' => $product->vendor->shop_name,
                ] : null,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'images' => $product->images->map(function ($img) {
                    return asset('storage/' . ($img->image ?? $img->image_path));
                }),
                'variations' => $product->variations->map(function ($variation) {
                    return [
                        'id' => $variation->id,
                        'sku' => $variation->sku,
                        'price' => $variation->price,
                        'stock' => $variation->stock,
                        'attribute_values' => $variation->attributeValues->map(function ($attrValue) {
                            return [
                                'id' => $attrValue->id,
                                'value' => $attrValue->value,
                                'attribute_id' => $attrValue->variation_attribute_id,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json($products);
    }
}
