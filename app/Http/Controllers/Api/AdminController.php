<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admins;
use App\Models\Invoices;
use App\Models\Products;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use MongoDB\Operation\Find;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = Admins::all();

        return response()->json([
            "message" => "Users retrieved successfully",
            "data" => $admins
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'admin_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role' => 'required|in:super_admin,manager,pharmacist',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        if ($request->hasFile("admin_pic")) {
            $cloudinary = new Cloudinary();
            $upload = $cloudinary->uploadApi()->upload(
                $request->file("admin_pic")->getRealPath(),
                ["folder" => "pharmacy-admin-pic"]
            );
            $validatedData["admin_pic"] = $upload['secure_url'];
            $validatedData["admin_pic_public_id"] = $upload['public_id'];
        }else{
            $validatedData["admin_pic"] = null;
            $validatedData["admin_pic_public_id"] = null;
        }

        $admins = Admins::create($validatedData);

        // $token = $admins->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => $admins,
        ], 201);
        
    }

    public function login(Request $request){
        $admins = Admins::where("username", $request->username)->first();

        $request->validate([
            "username" => "required|string|max:255",
            "password" => "required|string|min:8",
        ]);

        if(!$admins){
            return response()->json([
                "message" => "Admin not found!",
            ], 404);
        }

        if(!Hash::check($request->password,$admins->password)){
            return response()->json([
                "message" => "Invalid credentials"
            ],401);
        }

        $token = $admins->createToken("auth_token")->plainTextToken;

        return response()->json([
            "message"      => "Login admin successful!",
            "admin"         => $admins,
            "access_token" => $token,
            "token_type"   => "Bearer",
        ], 200);
    }

    public function logout(Request $request)
    {
        $admins = $request->user();

        if($admins){
            $admins -> tokens() -> delete();
        }

        $cookie = Cookie::forget("auth_token");

        return response()->json([
            "message" => "Logout Successful!",
        ],200)->withCookie($cookie);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $admins = Admins::find($id);

        if(!$admins){
            return response()->json([
                "message" => "Admin not found!",
            ], 404);
        }

        return response()->json([
            "message" => "Admin found successfully",
            "data" => $admins
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'username' => 'sometimes|string|max:255|unique:admins',
            'password' => 'sometimes|string|min:8',
            'admin_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role' => 'sometimes|in:super_admin,manager,pharmacist',
        ]);

        $admins = Admins::find($id);

        if(!$admins){
            return response()->json([
                "message" => "Admin not found!",
            ], 404);
        }

        if ($request->hasFile("admin_pic")) {
            $cloudinary = new Cloudinary();

            if($admins->admin_pic_public_id){
                $cloudinary->uploadApi()->destroy($admins->admin_pic_public_id);
            }

            $upload = $cloudinary->uploadApi()->upload(
                $request->file("admin_pic")->getRealPath(),
                ["folder" => "pharmacy-admin-pic"]
            );
            
            $validatedData["admin_pic"] = $upload['secure_url'];
            $validatedData["admin_pic_public_id"] = $upload['public_id'];
        }else{
            $validatedData["admin_pic"] = null;
            $validatedData["admin_pic_public_id"] = null;
        }

        $admins->fill($validatedData);
        $admins->save();

        return response()->json([
            "message" => "Admin updated successfully",
            "data" => $admins
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $admins = Admins::find($id);

        if(!$admins){
            return response()->json([
                "message" => "Admin not found"
            ], 404);
        }

        $cloudinary = new Cloudinary();

        if($admins->admin_pic_public_id) {
            $cloudinary->uploadApi()->destroy($admins->admin_pic_public_id);
        }

        Products::where('admin_id', $id)->update(['admin_id' => null]);
        Products::where('updated_by', $id)->update(['updated_by' => null]);
        Invoices::where('admin_id', $id)->update(['admin_id' => null]);

        $admins->delete();

        return response()->json([
            "message" => "Admin deleted successfully"
        ]);
    }
}
