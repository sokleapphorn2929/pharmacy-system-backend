<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brands;
use App\Models\OrderItems;
use App\Models\Products;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::all();

        return response()->json([
            "message" => "Products retrieved successfully",
            "date" => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'product_name' => 'required|string|max:255|unique:categories',
            'product_price' => 'required|numeric|min:1',
            'product_discount' => 'required|numeric|min:0',
            'product_status' => 'required|in:available, out_of_stock',
            'product_manufactured_date' => 'required|date|before:today',
            'product_expired_date' => 'required|date|after:today',
            'product_detail' => 'nullable|string|max:255',
            'product_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'nullable|exists:categories,_id',
            'brand_id' => 'nullable|exists:brands,_id',
        ]);

        if ($request->hasFile("product_pic")) {
            $cloudinary = new Cloudinary();
            $upload = $cloudinary->uploadApi()->upload(
                $request->file("product_pic")->getRealPath(),
                ["folder" => "pharmacy-product-pic"]
            );
            $validatedData["product_pic"] = $upload['secure_url'];
            $validatedData["product_pic_public_id"] = $upload['public_id'];
        }else{
            $validatedData["product_pic"] = null;
            $validatedData["product_pic_public_id"] = null;
        }

        $validatedData['admin_id'] = auth()->id();

        $products = new Products();
        $products -> fill($validatedData);
        $products -> save();

        return response()->json([
            "message" => "Products insert successfully",
            "data" => $products
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $products = Products::find($id);

        if(!$products){
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }

        return response()->json([
            "message" => "Product retrieved successfully",
            "data" => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $products = Products::find($id);

        if(!$products){
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }

        $validatedData = $request->validate([
            'product_name' => 'sometimes|string|max:255|unique:categories',
            'product_price' => 'sometimes|numeric|min:1',
            'product_discount' => 'sometimes|numeric|min:0',
            'product_status' => 'sometimes|in:available, out_of_stock',
            'product_manufactured_date' => 'sometimes|date|before:today',
            'product_expired_date' => 'sometimes|date|after:today',
            'product_detail' => 'nullable|string|max:255',
            'product_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'nullable|exists:categories,_id',
            'brand_id' => 'nullable|exists:brands,_id',
        ]);

        if ($request->hasFile("product_pic")) {
            $cloudinary = new Cloudinary();

            if($products->product_pic_public_id) {
                $cloudinary->uploadApi()->destroy($products->product_pic_public_id);
            }

            $upload = $cloudinary->uploadApi()->upload(
                $request->file("product_pic")->getRealPath(),
                ["folder" => "pharmacy-product-pic"]
            );
            $validatedData["product_pic"] = $upload['secure_url'];
            $validatedData["product_pic_public_id"] = $upload['public_id'];
        }

        $validatedData['updated_by'] = auth()->id();

        $products->fill($validatedData);
        $products->save();

        return response()->json([
            "message" => "Product updated successfully",
            "data" => $products
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $products = Products::find($id);

        if(!$products){
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }

        $cloudinary = new Cloudinary();

        if($products->product_pic_public_id) {
            $cloudinary->uploadApi()->destroy($products->product_pic_public_id);
        }

        OrderItems::where('order_item_id', $id)->update(['order_item_id' => null]);

        $products->delete();

        return response()->json([
            "message" => "Product deleted successfully"
        ]);
    }
}
