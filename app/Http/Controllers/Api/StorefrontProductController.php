<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Category;
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

        //dd($request->all());
        $big_query = Product::with(['vendor', 'category', 'images', 'variations.attributeValues']);

        $big_query = $big_query->join('vendors', 'products.vendor_id', '=', 'vendors.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->leftJoin('brands', 'products.brand_id', '=', 'brands.id');

        $where_added = false;
        $variation_where_added = false;

        $similarity_column_entries = [];

        if($request->has('product') || $request->has('item') || $request->has('brand') || $request->has('vendor_name') || $request->has('category') ) {
           
        $big_query->where(function ($query) use ($request, &$where_added, &$similarity_column_entries) {    
        // Only search using fields that represent names
        if ($request->has('product')) {
            $query->wherePGSimilarity('products.name', "{$request->input('product')}");

            $where_added = true;

            $similarity_column_entries["products.name"] = "similarity(products.name, '{$request->input('product')}')";

           
        }


        if($request->has('item')) {

            if($where_added) {
                $query->orWherePGSimilarity('products.name',  "{$request->input('item')}")
                ->orWherePGSimilarity('products.description',  "{$request->input('item')}")
                ->orWherePGSimilarity('categories.name',  "{$request->input('item')}");

                

            } else {
                $query->wherePGSimilarity('products.name',  "{$request->input('item')}")
                ->orWherePGSimilarity('products.description',  "{$request->input('item')}")
                ->orWherePGSimilarity('categories.name',  "{$request->input('item')}");
            }
            
            $where_added = true;

            $similarity_column_entries['products.name'] = `similarity(products.name, '{$request->input('item')}')`;

             $similarity_column_entries['products.description'] = "similarity(products.description, '{$request->input('item')}')";

             $similarity_column_entries['categories.name'] = "similarity(categories.name, '{$request->input('item')}')";

        }


        /*
            TO DOOOOOOO
            add a brand field to the model and refactor the search by brand query
        */

        if($request->has('brand')){

            if($where_added) {
                $query->orWherePGSimilarity('brands.name', "{$request->input('brand')}")
                ->orWherePGSimilarity('brands.description', "{$request->input('brand')}");
            } else {
                $query->wherePGSimilarity('brand.name', "{$request->input('brand')}")
                ->orWherePGSimilarity('brands.description', "{$request->input('brand')}");
            }
            
            $where_added = true;

            $similarity_column_entries['brands.name'] = "similarity(brands.name, '{$request->input('brand')}')";

        }


        if ($request->has('category')) {

            $query->orwhereHas('category', function ($q) use ($request, &$where_added, &$similarity_column_entries) {
                if($where_added)
                  $q->orWherePGSimilarity('categories.name', "{$request->input('category')}");
                else
                  $q->wherePGSimilarity('categories.name', "{$request->input('category')}");

                $where_added  = true;

                
            });

            $similarity_column_entries['categories.name'] = "similarity(categories.name, '{$request->input('category')}')";
        }



        if ($request->has('vendor')) {

             
            $query->whereHas('vendor', function ($q) use ($request, &$where_added, &$similarity_column_entries) {
                if($where_added)
                  $q->orWherePGSimilarity('shop_name', "{$request->input('vendor')}");
                else
                  $q->wherePGSimilarity('shop_name', "{$request->input('vendor')}");

                $where_added  = true;

                $similarity_column_entries['vendors.name'] = "similarity(vendors.shop_name, '{$request->input('vendor')}')";
            });
        }

        }
      );
    
     }
        // Search by variation attribute name and/or value
      
    



        if($request->has('attributes')) {


            $big_query->where(function ($query) use ($request, &$variation_where_added) {

            foreach ($request->input('attributes') as $key => $value) {
                $query->whereHas('variations.attributeValues.attribute', function ($q) use ($key, $value, $variation_where_added, &$similarity_column_entries) {

                    if (is_array($value)) {

                        foreach ($value as $v) {
                            
                            if($variation_where_added) {
                                $q->orWherePGSimilarity('variation_attributes.name', $key)
                                  ->wherePGSimilarity('variation_attribute_values.value', "{$v}");
                            } else {
                                $q->wherePGSimilarity('variation_attributes.name', $key)
                                  ->wherePGSimilarity('variation_attribute_values.value', "{$v}");
                            }


                            $variation_where_added  = true;

                           

                        }
                        
                    } else {


                        if($variation_where_added) {
                            $q->orWherePGSimilarity('variation_attributes.name', $key)
                              ->wherePGSimilarity('variation_attribute_values.value', "{$value}");
                        } else {
                            $q->wherePGSimilarity('variation_attributes.name', $key)
                              ->wherePGSimilarity('variation_attribute_values.value', "{$value}");
                        }
                       

                       

                    }
                   
                });
            }


        }
    );
}


     /*
        create similarity scores by adding each columns similarity score   
    
     */

        $similarity_score_sql = count($similarity_column_entries)>0?implode(' + ', $similarity_column_entries):'';
        
        $similarity_score_sql = $similarity_score_sql != '' ?  '( 0 '.$similarity_score_sql.') as similarity_score' : '0 as similarity_score';


        //dd($similarity_column_entries);

       //dd($big_query->select('products.*,'.$similarity_score_sql)->toSql(), $big_query->getBindings());

       //dd($big_query->get());

        $products = $big_query->select(DB::raw('products.*,'.$similarity_score_sql))->orderBy('similarity_score', 'desc')->paginate($request->input('per_page', 15))->appends($request->query());

        //dd($products);

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
                'similarity_score' => $product->similarity_score,
            ];
        });

        return response()->json($products);
    }


    /**
     * Return a list of all product categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $categories = Category::query()
            ->has('products')
            ->select('id', 'name', 'description', 'parent_id')
            ->get();

        return response()->json($categories);
    }


    public function vendors()
    {
        $vendors = \App\Models\Vendor::select('id', 'shop_name', 'description', 'logo', 'address')->get();

        return response()->json($vendors);
    }

    public function products(Request $request)
    {

        $products = Product::with(['vendor', 'category', 'images', 'variations.attributeValues']);

         if($request->has('field')){

              $field = $request->input('field');
              $value = $request->input('value');

               $products = $products->whereHas($field, function($q) use ($field, $value) {
                   $q->where('id', '=', "{$value}");
               });
         }

          $products = $products->paginate($request->input('per_page', 15))->appends($request->query());

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

       // return response()->json($products);
    }
}
