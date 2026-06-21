<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brands;
use App\Models\Products;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brands::all();

        return response()->json([
            "message" => "Brands retrieved successfully",
            "data" => $brands
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'brand_name' => 'required|string|max:255',
            'brand_location' => 'nullable|string|max:255',
            'brand_detail' => 'nullable|string|max:255',
            'brand_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile("brand_pic")) {
            $cloudinary = new Cloudinary();
            $upload = $cloudinary->uploadApi()->upload(
                $request->file("brand_pic")->getRealPath(),
                ["folder" => "pharmacy-brand-pic"]
            );
            $validatedData["brand_pic"] = $upload['secure_url'];
            $validatedData["brand_pic_public_id"] = $upload['public_id'];
        }else{
            $validatedData["brand_pic"] = null;
            $validatedData["brand_pic_public_id"] = null;
        }
        
        $brands = new Brands();
        $brands -> fill($validatedData);
        $brands -> save();

        return response()->json([
            "message" => "Brands insert successfully",
            "data" => $brands
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brands = Brands::find($id);

        if (!$brands) {
            return response()->json([
                "message" => "Brand not found"
            ], 404);
        }

        return response()->json([
            "message" => "Brand retrieved successfully",
            "data" => $brands
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brands = Brands::find($id);

        if(!$brands){
            return response()->json([
                "message" => "Brand not found"
            ], 404);
        }

        $validatedData = $request->validate([
            'brand_name' => 'sometimes|string|max:255',
            'brand_location' => 'nullable|string|max:255',
            'brand_detail' => 'nullable|string|max:255',
            'brand_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile("brand_pic")) {
            $cloudinary = new Cloudinary();

            if($brands->brand_pic_public_id) {
                $cloudinary->uploadApi()->destroy($brands->brand_pic_public_id);
            }

            $upload = $cloudinary->uploadApi()->upload(
                $request->file("brand_pic")->getRealPath(),
                ["folder" => "pharmacy-brand-pic"]
            );
            $validatedData["brand_pic"] = $upload['secure_url'];
            $validatedData["brand_pic_public_id"] = $upload['public_id'];
        }

        $brands->fill($validatedData);
        $brands->save();

        return response()->json([
            "message" => "Brand updated successfully",
            "data" => $brands
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brands = Brands::find($id);

        if(!$brands){
            return response()->json([
                "message" => "Brand not found"
            ], 404);
        }

        $cloudinary = new Cloudinary();

        if($brands->brand_pic_public_id) {
            $cloudinary->uploadApi()->destroy($brands->brand_pic_public_id);
        }

        Products::where('brand_id', $id)->update(['brand_id' => null]);

        $brands->delete();

        return response()->json([
            "message" => "Brand deleted successfully"
        ]);
    }
}
