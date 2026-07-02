<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Products;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Categories::all();

        return response()->json([
            "message" => "Categories retrieved successfully",
            "data" => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_name' => 'required|string|max:255|unique:categories',
            'category_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile("category_pic")) {
            $cloudinary = new Cloudinary();
            $upload = $cloudinary->uploadApi()->upload(
                $request->file("category_pic")->getRealPath(),
                ["folder" => "pharmacy-category-pic"]
            );
            $validatedData["category_pic"] = $upload['secure_url'];
            $validatedData["category_pic_public_id"] = $upload['public_id'];
        }else{
            $validatedData["category_pic"] = null;
            $validatedData["category_pic_public_id"] = null;
        }
        
        $categories = new Categories();
        $categories -> fill($validatedData);
        $categories -> save();

        return response()->json([
            "message" => "Categories insert successfully",
            "data" => $categories
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categories = Categories::find($id);

        if(!$categories){
            return response()->json([
                "message" => "Category not found"
            ], 404);
        }

        return response()->json([
            "message" => "Category retrieved successfully",
            "data" => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $categories = Categories::find($id);

        if(!$categories){
            return response()->json([
                "message" => "Category not found"
            ], 404);
        }

        $validatedData = $request->validate([
            'category_name' => 'sometimes|string|max:255',
            'category_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile("category_pic")) {
            $cloudinary = new Cloudinary();

            if($categories->category_pic_public_id) {
                $cloudinary->uploadApi()->destroy($categories->category_pic_public_id);
            }

            $upload = $cloudinary->uploadApi()->upload(
                $request->file("category_pic")->getRealPath(),
                ["folder" => "pharmacy-category-pic"]
            );
            $validatedData["category_pic"] = $upload['secure_url'];
            $validatedData["category_pic_public_id"] = $upload['public_id'];
        }

        $categories->fill($validatedData);
        $categories->save();

        return response()->json([
            "message" => "Category updated successfully",
            "data" => $categories
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $categories = Categories::find($id);

        if(!$categories){
            return response()->json([
                "message" => "Category not found"
            ], 404);
        }

        $cloudinary = new Cloudinary();

        if($categories->category_pic_public_id) {
            $cloudinary->uploadApi()->destroy($categories->category_pic_public_id);
        }

        Products::where('category_id', $id)->update(['category_id' => null]);

        $categories->delete();

        return response()->json([
            "message" => "Category deleted successfully"
        ]);
    }
}
