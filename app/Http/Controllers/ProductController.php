<?php

namespace App\Http\Controllers;

use App\Product;
use Cache;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    const CACHE_TAG = 'products';
    const CACHE_DURATION_MINUTES = 30;

    public function index()
    {
    $page = 1;
        $search = null;
        $store_id = null;

        if ($request->has('page')) {
            $page = $request->input('page');
        }

        if ($request->has('q')) {
            $search = $request->input('q');
        }

        $cache_name = "search:{$search}:page:{$page}";

        if (Cache::tags(self::CACHE_TAG)->has($cache_name)) {
            $products = Cache::tags(self::CACHE_TAG)->get($cache_name);
        } else {
            $products = Product::search($search);

            $products = $products
                ->orderBy('created_at')
                ->paginate(20);

            Cache::tags(self::CACHE_TAG)->put($cache_name, $products, self::CACHE_DURATION_MINUTES);
        }

        return response()->json($products);    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {

        $product = Product::create($request->validated());

        Cache::tags(self::CACHE_TAG)->flush();

        return response()->json($product);   
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
      // Add store and currency related data.
        return response()->json($product);   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        Cache::tags(self::CACHE_TAG)->flush();

        return response()->json($product);  
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
      
        $product->destroy();

        Cache::tags(self::CACHE_TAG)->flush();

        return response()->json([
            'status' => 'success',
            'message' => 'The product has been removed.'
        ]);   
    }
}
