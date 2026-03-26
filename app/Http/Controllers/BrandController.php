<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $query = Brand::query();
        
        $vendor = null;
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            $query->where(function($q) use ($vendor) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendor?->id);
            });
        }

        $brands = $query->get();
        return view('admin.brands.index', compact('brands', 'vendor'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
        ]);

        // Check for duplicates (existing approved or pending)
        if (Brand::where('name', $validated['name'])->exists()) {
            return redirect()->back()->withInput()->with('error', 'This brand already exists or is pending approval.');
        }

        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            $validated['vendor_id'] = $vendor->id;
            $validated['status'] = 'pending';
        } else {
            $validated['status'] = 'approved';
        }

        Brand::create($validated);
        return redirect()->route('admin.brands.index')->with('success', 'Brand suggestion created successfully.');
    }

    public function approve(Brand $brand)
    {
        if (!auth()->user()->hasRole('super.admin')) {
            abort(403);
        }
        $brand->update(['status' => 'approved']);
        return redirect()->route('admin.brands.index')->with('success', 'Brand approved successfully.');
    }

    public function edit(Brand $brand)
    {
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            if ($brand->vendor_id !== $vendor->id) {
                abort(403, 'Unauthorized to edit this brand.');
            }
        }
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
        ]);
        $brand->update($validated);
        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = \App\Models\Vendor::where('user_id', auth()->id())->first();
            if ($brand->vendor_id !== $vendor->id) {
                abort(403, 'Unauthorized to delete this brand.');
            }
        }
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
