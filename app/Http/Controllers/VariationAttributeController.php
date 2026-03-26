<?php
namespace App\Http\Controllers;

use App\Models\VariationAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariationAttributeController extends Controller
{
    public function index()
    {
        $query = VariationAttribute::with('values');
        
        $vendor = null;
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            $query->where(function($q) use ($vendor) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendor?->id);
            });
        }

        $attributes = $query->paginate(15);
        return view('admin.variation_attributes.index', compact('attributes', 'vendor'));
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

        // Check for duplicates
        if (VariationAttribute::where('name', $request->name)->exists()) {
            return redirect()->back()->withInput()->with('error', 'This attribute already exists or is pending approval.');
        }
        
        DB::transaction(function () use ($request) {
            $data = ['name' => $request->name];
            if (auth()->user()->hasRole('vendor.admin')) {
                $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
                $data['vendor_id'] = $vendor->id;
                $data['status'] = 'pending';
            } else {
                $data['status'] = 'approved';
            }

            $attribute = VariationAttribute::create($data);
            foreach ($request->values as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        });
        return redirect()->route('admin.variation-attributes.index')->with('success', 'Attribute suggestion created.');
    }

    public function approve(VariationAttribute $variationAttribute)
    {
        if (!auth()->user()->hasRole('super.admin')) {
            abort(403);
        }
        $variationAttribute->update(['status' => 'approved']);
        return redirect()->route('admin.variation-attributes.index')->with('success', 'Attribute approved successfully.');
    }

    public function edit(VariationAttribute $variationAttribute)
    {
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            if ($variationAttribute->vendor_id !== $vendor->id) {
                abort(403, 'Unauthorized to edit global or other vendor attributes.');
            }
        }
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
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            if ($variationAttribute->vendor_id !== $vendor->id) {
                abort(403, 'Unauthorized to delete global attributes.');
            }
        }
        $variationAttribute->delete();
        return redirect()->route('admin.variation-attributes.index')->with('success', 'Attribute deleted.');
    }
}
