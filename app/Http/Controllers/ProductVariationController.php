<?php
namespace App\Http\Controllers;

use App\Models\ProductVariation;
use App\Models\Product;
use App\Models\VariationAttributeValue;
use Illuminate\Http\Request;

class ProductVariationController extends Controller
{
    public function index(Product $product)
    {
        $variations = $product->variations()->with('attributeValues')->paginate(15);
        return view('admin.product_variations.index', compact('product', 'variations'));
    }

    public function create(Product $product)
    {
        $attributeValues = VariationAttributeValue::with('attribute')->get();
        return view('admin.product_variations.create', compact('product', 'attributeValues'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'attribute_value_ids' => 'required|array',
            'attribute_value_ids.*' => 'exists:variation_attribute_values,id',
        ]);
        $variation = $product->variations()->create($request->only('sku', 'price', 'stock'));
        $variation->attributeValues()->sync($request->attribute_value_ids);
        return redirect()->route('admin.products.variations.index', $product)->with('success', 'Variation created.');
    }

    public function edit(Product $product, ProductVariation $variation)
    {
        $attributeValues = VariationAttributeValue::with('attribute')->get();
        $variation->load('attributeValues');
        return view('admin.product_variations.edit', compact('product', 'variation', 'attributeValues'));
    }

    public function update(Request $request, Product $product, ProductVariation $variation)
    {
        $request->validate([
            'sku' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'attribute_value_ids' => 'required|array',
            'attribute_value_ids.*' => 'exists:variation_attribute_values,id',
        ]);
        $variation->update($request->only('sku', 'price', 'stock'));
        $variation->attributeValues()->sync($request->attribute_value_ids);
        return redirect()->route('admin.products.variations.index', $product)->with('success', 'Variation updated.');
    }

    public function destroy(Product $product, ProductVariation $variation)
    {
        $variation->delete();
        return redirect()->route('admin.products.variations.index', $product)->with('success', 'Variation deleted.');
    }
}
