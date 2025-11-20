<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\VariationAttributeValue;
use App\Models\VariationAttribute;
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

    private static function cleanInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'cleanInput'], $input);
        }
        // For SQL, escape single quotes by doubling them
        return is_string($input) ? str_replace("'", "''", trim($input)) : $input;
    }
    
    public function search(Request $request)
    {

        // Clean and escape all request input in-place
        // function cleanInput($input) {
        //     if (is_array($input)) {
        //         return array_map('cleanInput', $input);
        //     }
        //     return is_string($input) ? addslashes(trim($input)) : $input;
        // }
        
        $request->merge(self::cleanInput($request->all()));

        $big_query = Product::with(['vendor', 'brand','category', 'images', 'variations.attributeValues']);

        $big_query = $big_query->join('vendors', 'products.vendor_id', '=', 'vendors.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->leftJoin('categories as parent_categories', 'categories.parent_id', '=', 'parent_categories.id')
                    ->leftJoin('categories as grandparent_categories', 'parent_categories.parent_id', '=', 'grandparent_categories.id')
                    ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                    ->leftJoin('product_variations', 'products.id', '=', 'product_variations.product_id')
                    ->leftJoin('product_variation_attribute_value', 'product_variations.id', '=', 'product_variation_attribute_value.product_variation_id')
                    ->leftJoin('variation_attribute_values', 'product_variation_attribute_value.variation_attribute_value_id', '=', 'variation_attribute_values.id')
                    ->leftJoin('variation_attributes', 'variation_attribute_values.variation_attribute_id', '=', 'variation_attributes.id');

        $where_added = false;
        $variation_where_added = false;

        $similarity_column_entries = [];

        $intermediate_queries = [] ;
        

        if($request->has('product') || $request->has('item') || $request->has('brand') || $request->has('vendor_name') || $request->has('category') ) {
           

            /*
               if searching product name field
            */

           if($request->has('product') && trim($request->input('product'))!='') {
           
             $big_query->where(function ($query) use ($request, &$similarity_column_entries) {    
             // Only search using fields that represent names
               
     
                 $query->wherePGSimilarity('products.name', "{$request->input('product')}")
                 ->orWherePGSimilarity('products.description',  "{$request->input('product')}");;
                 
                 $similarity_column_entries["products.name"] = "similarity(products.name, '{$request->input('product')}')";
                
                 $similarity_column_entries["products.description"] = "similarity(products.description, '{$request->input('product')}')";
     
                 
               });

             $where_added = true;  

             $intermediate_queries['query_products'] = ['q'=>$big_query,'s'=>$similarity_column_entries];

           }


           /*
                if searching item description field
           */
            if($request->has('item') && trim($request->input('item'))!='') {


                $fn = function ($query) use ($request, &$where_added,&$similarity_column_entries) {    
                            
                 if($where_added) {
     
                     $query->orWherePGSimilarity('products.name',  "{$request->input('item')}")
                     ->orWherePGSimilarity('products.description',  "{$request->input('item')}");
                     //->orWherePGSimilarity('categories.name',  "{$request->input('item')}");            
     
                  } else {

                    $query->wherePGSimilarity('products.name',  "{$request->input('item')}")
                    ->orWherePGSimilarity('products.description',  "{$request->input('item')}");
                    //->orWherePGSimilarity('categories.name',  "{$request->input('item')}");
                 }
            
                $where_added = true;

                $similarity_column_entries['products.name'] = `similarity(products.name, '{$request->input('item')}')`;

                $similarity_column_entries['products.description'] = "similarity(products.description, '{$request->input('item')}')";

               // $similarity_column_entries['categories.name.item'] = "similarity(categories.name, '{$request->input('item')}')";
               
                };

                if($where_added){
                  $big_query->orWhere($fn);
                } else {

                   $big_query->where($fn);
                }

               $intermediate_queries['query_products'] = ['q'=>$big_query,'s'=>$similarity_column_entries];
            
           }
                  

           /*
            If searching using category name
           */
             
         if ($request->has('category') && trim($request->input('category'))!='') {


                $fn  = function ($query) use ($request, &$where_added, &$similarity_column_entries) {    
             // Only search using fields that represent names
               
     
                if($where_added){
                  $query->orWherePGSimilarity('categories.name', "{$request->input('category')}");
                  $query->orWherePGSimilarity('parent_categories.name', "{$request->input('category')}");
                   $query->orWherePGSimilarity('grandparent_categories.name', "{$request->input('category')}");

                   $query->orWherePGSimilarity('categories.description', "{$request->input('category')}");
                  $query->orWherePGSimilarity('parent_categories.description', "{$request->input('category')}");
                   $query->orWherePGSimilarity('grandparent_categories.description', "{$request->input('category')}");


                } else {
                  $query->wherePGSimilarity('categories.name', "{$request->input('category')}");
                  $query->orWherePGSimilarity('parent_categories.name', "{$request->input('category')}");
                  $query->orWherePGSimilarity('grandparent_categories.name', "{$request->input('category')}");


                   $query->orWherePGSimilarity('categories.description', "{$request->input('category')}");
                  $query->orWherePGSimilarity('parent_categories.description', "{$request->input('category')}");
                   $query->orWherePGSimilarity('grandparent_categories.description', "{$request->input('category')}");
                
                }

                $where_added  = true;
          
                $similarity_column_entries['categories.name'] = "similarity(categories.name, '{$request->input('category')}')";

                $similarity_column_entries['parent_categories.name'] = "similarity(parent_categories.name, '{$request->input('category')}')";

                $similarity_column_entries['grandparent_categories.name'] = "similarity(grandparent_categories.name, '{$request->input('category')}')";


                $similarity_column_entries['categories.description'] = "similarity(categories.description, '{$request->input('category')}')";

                $similarity_column_entries['parent_categories.description'] = "similarity(parent_categories.description, '{$request->input('category')}')";

                $similarity_column_entries['grandparent_categories.description'] = "similarity(grandparent_categories.description, '{$request->input('category')}')";
                      
               };
                 

                 /*
                    use the and clause instead of the or clause because the the catergory search query is exactly the same as the database field being searched
                 */
                if($where_added){
                    $big_query->where($fn);
                 } else {
                   $big_query->where($fn);
                 }
    

            $intermediate_queries['query_products'] = ['q'=>$big_query,'s'=>$similarity_column_entries];
       
       
         }


            /*
               if searching using brand name
            */
            

        if($request->has('brand') && trim($request->input('brand'))!='') {



               $big_query->where(function ($query) use ($request, &$where_added, &$similarity_column_entries) {  

                 $query->where(function($q) use ($request) {
                      $q->wherePGSimilarity('brands.name', "{$request->input('brand')}")
                   ->orWherePGSimilarity('brands.description', "{$request->input('brand')}");
                  });
                        
                 $where_added = true;

                 $similarity_column_entries['brands.name'] = "similarity(brands.name, '{$request->input('brand')}')";
            
               });
    
            
              $intermediate_queries['query_product_brand'] = ['q'=>$big_query,'s'=>$similarity_column_entries];

        }


        /*
           if searching using vendor or shop name

        */

        if ($request->has('vendor') && trim($request->input('vendor'))!='') {


                $big_query->where(function ($query) use ($request, &$where_added, &$similarity_column_entries) {    
             // Only search using fields that represent names
               
     
                $query->orWhereHas('vendor', function ($q) use ($request, &$where_added, &$similarity_column_entries) {

                  if($where_added)
                    $q->orWherePGSimilarity('shop_name', "{$request->input('vendor')}");
                  else
                    $q->wherePGSimilarity('shop_name', "{$request->input('vendor')}");
  
                  $where_added  = true;

                  $similarity_column_entries['vendors.name'] = "similarity(vendors.shop_name, '{$request->input('vendor')}')";
               
                });
                      
               });

             
            
        }



    
     }
        // Search by variation attribute name and/or value
      
    



        if($request->has('attributes') && is_array($request->input('attributes')) && count($request->input('attributes'))>0) {


            $big_query->where(function ($query) use ($request, &$variation_where_added,&$similarity_column_entries) {

            foreach ($request->input('attributes') as $attribute_name => $value) {


                 //get variattion attribute name corresponding to value

                 $variation_attribute = VariationAttributeValue::where('value', $value)->first()->attribute()->first();
                 
                 $key = $variation_attribute ? $variation_attribute->name : $attribute_name;

                $query->orWhereHas('variations.attributeValues.attribute', function ($q) use ($key, $value, $variation_where_added, &$similarity_column_entries) {

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

                           $similarity_column_entries['variation_attribute_values.value'] = "similarity(variation_attribute_values.value, '{$v}')";

                           $similarity_column_entries['products.description_variation'] = "similarity(products.description, '{$v}')";

                        }
                        
                    } else {


                        if($variation_where_added) {
                            $q->orWherePGSimilarity('variation_attributes.name', $key)
                              ->wherePGSimilarity('variation_attribute_values.value', "{$value}");
                        } else {
                            $q->wherePGSimilarity('variation_attributes.name', $key)
                              ->wherePGSimilarity('variation_attribute_values.value', "{$value}");
                        }
                       
                         $similarity_column_entries['variation_attribute_values.value'] = "similarity(variation_attribute_values.value, '{$value}')";
                       

                    }
                   
                  });
                }
              }
          );

        $intermediate_queries['query_product_brand_attribute'] = ['q'=>$big_query,'s'=>$similarity_column_entries];
     }


     /*
        create similarity scores by adding each columns similarity score   
    
     */

        $similarity_score_sql = count($similarity_column_entries)>0?implode(' + ', $similarity_column_entries):'';
        
        $similarity_score_sql = $similarity_score_sql != '' ?  '( 0 '.$similarity_score_sql.') as similarity_score' : '0 as similarity_score';


        //dd($similarity_column_entries);

        

       //dd($big_query->get());

        $page_size = $request->input('per_page', 33);

        $page = intval($request->input('page', 1));

        //dd($page);
       
        // $products = $big_query->select(DB::raw('distinct on (products.id) products.*,parent_categories as parent_category_id, grandparent_categories.id as grandparent_category_id,'.$similarity_score_sql))->orderBy('similarity_score', 'desc')->orderBy('products.id')->paginate($request->input('per_page', $page_size), $page)->appends($request->query());


         //dd($big_query->select(DB::raw('distinct products.*,parent_categories as parent_category_id, grandparent_categories.id as grandparent_category_id,'.$similarity_score_sql))->toSql(), $big_query->getBindings());

        // dd($big_query->select('')->toSql());


        $sub = $big_query->select(DB::raw('distinct on (products.id) products.*, parent_categories.id as parent_category_id, grandparent_categories.id as grandparent_category_id, '.$similarity_score_sql));



        $final_query = Product::fromSub($sub, 'sq')
              ->select('sq.*')
              ->with(['vendor', 'brand', 'category', 'images', 'variations.attributeValues'])
              ->orderBy('sq.similarity_score', 'desc');

       // dd( $final_query->toSql(), $final_query->getBindings());

       $products = $final_query
            ->paginate($request->input('per_page', $page_size), ['*'], 'page', $page)
            ->appends($request->query());
       

        while($products->count()==0 && count($intermediate_queries)>0){

            $last_query = array_pop($intermediate_queries);

           
            if($last_query){

                $similarity_score_sql = count($last_query['s'])>0?implode(' + ', $last_query['s']):'';

                $similarity_score_sql = $similarity_score_sql != '' ?  '( 0 '.$similarity_score_sql.') as similarity_score' : '0 as similarity_score';

                $products = $last_query['q']->select(DB::raw('distinct on (products.id) products.*,parent_categories as parent_category_id, grandparent_categories.id as grandparent_category_id,'.$similarity_score_sql))->orderBy('products.id')->orderBy('similarity_score', 'desc')->paginate($request->input('per_page', $page_size),$page)->appends($request->query());

                

            }


        }

         //dd($products->getCollection());
         /*
            Remove duplicate product entries from current page (keeps pagination totals unchanged)

            duplicate products can arise from joins above, when multiple variation/attribute matches exist for a product
         */
         $unique = $products->getCollection()->unique('id')->values();
         $products->setCollection($unique);

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
                    'parent_category' => $product->category->parent_id ?[
                        'id' => $product->category->parent ? $product->category->parent->id : null,
                        'name' => $product->category->parent ? $product->category->parent->name : null,
                        'grandparent_category' => $product->category->parent && $product->category->parent->parent_id ? [
                            'id' => $product->category->parent->parent ? $product->category->parent->parent->id : null,
                            'name' => $product->category->parent->parent ? $product->category->parent->parent->name : null,
                        ] : null,
                    ]:null,
                ] : null,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
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

        $big_query = Product::with(['vendor', 'category', 'images', 'variations.attributeValues']);

         if($request->has('field')){

              $field = $request->input('field');
              $value = $request->input('value');

               $big_query = $big_query->whereHas($field, function($q) use ($field, $value) {
                   $q->where('id', '=', "{$value}");
               });
         }
         
         $page_size = $request->input('per_page', 3);
         $page = intval($request->input('page', 1));

          //$products = $products->paginate($request->input('per_page', $page_size), $page)->appends($request->query());

          $products = $big_query->select(DB::raw('products.*'))->orderBy('products.name', 'desc')->paginate($request->input('per_page', $page_size), $page)->appends($request->query());


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
