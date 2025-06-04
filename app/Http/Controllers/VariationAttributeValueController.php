<?php
namespace App\Http\Controllers;

use App\Models\VariationAttributeValue;
use App\Models\VariationAttribute;
use Illuminate\Http\Request;

class VariationAttributeValueController extends Controller
{
    public function index()
    {
        $values = VariationAttributeValue::with('attribute')->paginate(15);
        return view('admin.variation_attribute_values.index', compact('values'));
    }

    public function create()
    {
        $attributes = VariationAttribute::all();
        return view('admin.variation_attribute_values.create', compact('attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'variation_attribute_id' => 'required|exists:variation_attributes,id',
            'value' => 'required|string|max:255',
        ]);
        VariationAttributeValue::create($request->only('variation_attribute_id', 'value'));
        return redirect()->route('admin.variation-attribute-values.index')->with('success', 'Value created.');
    }

    public function edit(VariationAttributeValue $variationAttributeValue)
    {
        $attributes = VariationAttribute::all();
        return view('admin.variation_attribute_values.edit', compact('variationAttributeValue', 'attributes'));
    }

    public function update(Request $request, VariationAttributeValue $variationAttributeValue)
    {
        $request->validate([
            'variation_attribute_id' => 'required|exists:variation_attributes,id',
            'value' => 'required|string|max:255',
        ]);
        $variationAttributeValue->update($request->only('variation_attribute_id', 'value'));
        return redirect()->route('admin.variation-attribute-values.index')->with('success', 'Value updated.');
    }

    public function destroy(VariationAttributeValue $variationAttributeValue)
    {
        $variationAttributeValue->delete();
        return redirect()->route('admin.variation-attribute-values.index')->with('success', 'Value deleted.');
    }
}
