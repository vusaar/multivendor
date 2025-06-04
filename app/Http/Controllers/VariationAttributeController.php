<?php
namespace App\Http\Controllers;

use App\Models\VariationAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariationAttributeController extends Controller
{
    public function index()
    {
        $attributes = VariationAttribute::with('values')->paginate(15);
        return view('admin.variation_attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.variation_attributes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string|max:255',
        ]);
        
        DB::transaction(function () use ($request) {
            $attribute = VariationAttribute::create($request->only('name'));
            foreach ($request->values as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        });
        return redirect()->route('admin.variation-attributes.index')->with('success', 'Attribute and values created.');
    }

    public function edit(VariationAttribute $variationAttribute)
    {
        return view('admin.variation_attributes.edit', compact('variationAttribute'));
    }

    public function update(Request $request, VariationAttribute $variationAttribute)
    {

        //dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|array|min:1',
            'values.*' => 'nullable|string|max:255', // allow new to be array
            'values.new' => 'array', // allow new values to be an array
            'values.new.*' => 'nullable|string|max:255',
        ]);
        DB::transaction(function () use ($request, $variationAttribute) {
            $variationAttribute->update($request->only('name'));
            $existingIds = $variationAttribute->values->pluck('id')->toArray();
            $submittedIds = array_filter(array_keys($request->values), 'is_numeric');

            // Update or delete existing values
            foreach ($variationAttribute->values as $val) {
                if (isset($request->values[$val->id])) {
                    $val->update(['value' => $request->values[$val->id]]);
                } else {
                    $val->delete();
                }
            }
            // Add new values
            if (isset($request->values['new']) && is_array($request->values['new'])) {
                foreach ($request->values['new'] as $newValue) {
                    if ($newValue) {
                        $variationAttribute->values()->create(['value' => $newValue]);
                    }
                }
            }
        });
        return redirect()->route('admin.variation-attributes.index')->with('success', 'Attribute and values updated.');
    }

    public function destroy(VariationAttribute $variationAttribute)
    {
        $variationAttribute->delete();
        return redirect()->route('admin.variation-attributes.index')->with('success', 'Attribute deleted.');
    }
}
