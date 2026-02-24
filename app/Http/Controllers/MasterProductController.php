<?php

namespace App\Http\Controllers;

use App\Models\MasterProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MasterProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masterProducts = MasterProduct::orderBy('name', 'asc')->paginate(15);
        return view('admin.master_products.index', compact('masterProducts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.master_products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:master_products,name|max:255',
            'synonyms' => 'nullable|string',
        ]);

        MasterProduct::create([
            'name' => $request->name,
            'synonyms' => $request->synonyms,
            'is_synced' => false,
        ]);

        return redirect()->route('admin.master-products.index')
            ->with('success', 'Master Product created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterProduct $masterProduct)
    {
        return view('admin.master_products.edit', compact('masterProduct'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterProduct $masterProduct)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:master_products,name,' . $masterProduct->id,
            'synonyms' => 'nullable|string',
        ]);

        $masterProduct->update([
            'name' => $request->name,
            'synonyms' => $request->synonyms,
            'is_synced' => false, // Require re-sync after update
        ]);

        return redirect()->route('admin.master-products.index')
            ->with('success', 'Master Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterProduct $masterProduct)
    {
        // Optional: Check if used by products? 
        // For now, allow deletion.
        $masterProduct->delete();

        return redirect()->route('admin.master-products.index')
            ->with('success', 'Master Product deleted successfully.');
    }

    /**
     * Sync synonyms to Meilisearch.
     */
    public function sync()
    {
        try {
            Artisan::call('meilisearch:sync-synonyms', ['--force' => true]);
            $output = Artisan::output();
            
            return redirect()->back()->with('success', 'Synonyms synced to Meilisearch!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error syncing synonyms: ' . $e->getMessage());
        }
    }
}
