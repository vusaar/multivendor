<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductIntegrityController extends Controller
{
    public function index()
    {
        // 1. Unlinked Products
        $unlinkedProducts = Product::whereNull('master_product_id')->with('vendor')->paginate(20, ['*'], 'unlinked_page');

        // 2. Potential Desyncs (Linked but names don't match)
        // We need to join to check name mismatch efficiently
        $desyncedProducts = Product::whereNotNull('master_product_id')
            ->join('master_products', 'products.master_product_id', '=', 'master_products.id')
            ->whereColumn('products.name', '!=', 'master_products.name')
            ->select('products.*', 'master_products.name as master_name')
            ->with(['masterProduct', 'vendor'])
            ->paginate(20, ['*'], 'desync_page');

        return view('admin.product_integrity.index', compact('unlinkedProducts', 'desyncedProducts'));
    }

    public function autoFix(Request $request)
    {
        $products = Product::whereNull('master_product_id')->get();
        $count = 0;

        foreach ($products as $product) {
            // Logic similar to relink script, but perhaps safer?
            // For now, simple name matching + creation
            $master = MasterProduct::firstOrCreate(['name' => $product->name]);
            $product->update(['master_product_id' => $master->id]);
            $count++;
        }

        return redirect()->back()->with('success', "Auto-fixed $count products by creating/linking to Master Products.");
    }
    
    public function detach(Product $product)
    {
        $product->update(['master_product_id' => null]);
        return redirect()->back()->with('success', 'Product detached from Master Product.');
    }
}
