<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\Cards;
use App\Models\Favourites;
use App\Models\Orders;
use App\Models\Payments;
use App\Models\User;
use Cloudinary\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return response()->json([
            "message" => "Users retrieved successfully",
            "data" => $users
        ]);
    }

    public function findIdByEmail(Request $request) {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        return response()->json(['id' => $user->id]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'required|string|max:20|unique:users',
            'address'  => 'nullable|string|max:255',
            "profile_pic" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            'password' => 'required|string|min:8',
        ]);

        if (Cache::has('pending_registration_' . $validatedData['email'])) {
            return response()->json([
                "message" => "A verification code was already sent to this email. Please check your inbox or wait for it to expire."
            ], 422);
        }

        if ($request->hasFile("profile_pic")) {
            $cloudinary = new Cloudinary();
            $upload = $cloudinary->uploadApi()->upload(
                $request->file("profile_pic")->getRealPath(),
                ["folder" => "pharmacy-user-profile"]
            );
            $validatedData["profile_pic"] = $upload['secure_url'];
            $validatedData["profile_pic_public_id"] = $upload['public_id'];
        }else{
            $validatedData["profile_pic"] = null;
            $validatedData["profile_pic_public_id"] = null;
        }

        $verificationCode = random_int(100000, 999999);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $registrationData = [
            'user_details'      => $validatedData,
            'verification_code' => $verificationCode,
        ];

        Cache::put('pending_registration_' . $validatedData['email'], $registrationData, now()->addMinutes(10));

        Mail::to($validatedData['email'])->send(new VerificationCodeMail($validatedData['username'], $verificationCode));
            
        return response()->json([
            "status"  => "registration_pending",
            "message" => "Verification code sent! Verify to complete your registration.",
            "email"   => $validatedData['email']
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|string|email|max:255",
            "password" => "required|string|min:8",
        ]);

        $user = User::where("email", $request->email)->first();
        
        if (!$user) {
            return response()->json([
                "message" => "User not found!!"
            ], 404);
        }

        if(!Hash::check($request->password,$user->password)){
            return response()->json([
                "message" => "Invalid credentials"
            ],401);
        }

        $verificationCode = random_int(100000, 999999);
        $loginKey = 'pending_login_' . $user->email;

        Cache::put($loginKey, $verificationCode, now()->addMinutes(10));

        Mail::to($user->email)->send(new VerificationCodeMail($user->username, $verificationCode));

        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json([
            "status"  => "login_pending",
            "message" => "Verification code sent to your email. Please verify to log in.",
            "email"   => $user->email
        ], 200);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|numeric',
        ]);

        $email = $request->email;
        $inputCode = (int)$request->code;

        $regKey   = 'pending_registration_' . $email;
        $loginKey = 'pending_login_' . $email;
        $deleteKey = 'pending_delete_user_' . $email;

        if (Cache::has($regKey)) {
            $registrationData = Cache::get($regKey);

            if ((int)$registrationData['verification_code'] !== $inputCode) {
                return response()->json(['message' => 'The verification code is incorrect.'], 401);
            }

            $userData = $registrationData['user_details'];

            if (User::where('email', $userData['email'])->exists()) {
                Cache::forget($regKey);
                return response()->json(['message' => 'This email has already been taken.'], 422);
            }

            $user = User::create([
                'username'              => $userData['username'],
                'email'                 => $userData['email'],
                'phone'                 => $userData['phone'],
                'address'               => $userData['address'],
                'profile_pic'           => $userData['profile_pic'] ?? null,
                'profile_pic_public_id' => $userData['profile_pic_public_id'] ?? null,
                'password'              => $userData['password'],
                'email_verified_at'     => now(),
            ]);

            Cache::forget($regKey);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                "message"      => "Registration successful!",
                "access_token" => $token,
                "token_type"   => "Bearer",
                "user"         => $user
            ], 201);
        }

        if (Cache::has($loginKey)) {
            $cachedCode = (int)Cache::get($loginKey);

            if ($cachedCode !== $inputCode) {
                return response()->json(['message' => 'The verification code is incorrect.'], 401);
            }

            $user = User::firstWhere('email', $email);

            Cache::forget($loginKey);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                "message"      => "Login successful!",
                "access_token" => $token,
                "token_type"   => "Bearer",
                "user"         => $user
            ], 200);
        }

        if (Cache::has($deleteKey)) {
            $cachedCode = (int)Cache::get($deleteKey);

            if ($cachedCode !== $inputCode) {
                return response()->json(['message' => 'The verification code is incorrect.'], 401);
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                $cloudinary = new Cloudinary();

                if ($user->profile_pic_public_id) {
                    $cloudinary->uploadApi()->destroy($user->profile_pic_public_id);
                }

                Favourites::where('user_id', $id)->delete();
                Cards::where('user_id', $id)->delete();
                Orders::where('user_id', $id)->update(['user_id' => null]);
                Payments::where('user_id', $id)->update(['user_id' => null]);

                // Delete personal access tokens first if using Sanctum
                $user->tokens()->delete();
                $user->delete();
            }

            Cache::forget($deleteKey);

            return response()->json([
                "message" => "Your account has been permanently deleted."
            ], 200);
        }

        return response()->json([
            'message' => 'Verification code has expired or code request session was not found.'
        ], 422);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if($user){
            $user -> tokens() -> delete();
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
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        return response()->json([
            "message" => "User retrieved successfully",
            "data" => $user
        ]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        $user = User::find($id);

        if(!$user){
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        if ($request->has('email') || $request->has('password')) {
            return response()->json([
                "message" => "Email and Password cannot be updated via this endpoint."
            ], 422);
        }

        $validatedData = $request->validate([
            'username' => 'sometimes|required|string|max:255',
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id),
            ],
            'address' => 'nullable|string|max:255',
            "profile_pic" => "nullable | image | mimes:jpeg,png,jpg,gif,svg | max:2048",
        ]);

        if ($request->hasFile("profile_pic")) {
            $cloudinary = new Cloudinary();

            if ($user->profile_pic_public_id) {
                $cloudinary->uploadApi()->destroy($user->profile_pic_public_id);
            }

            $upload = $cloudinary->uploadApi()->upload(
                $request->file("profile_pic")->getRealPath(),
                ["folder" => "pharmacy-user-profile"]
            );
            $validatedData["profile_pic"] = $upload['secure_url'];
            $validatedData["profile_pic_public_id"] = $upload['public_id'];
        }else{
            $validatedData["profile_pic"] = null;
            $validatedData["profile_pic_public_id"] = null;
        }

        $user->update($validatedData);

        return response()->json([
            "message" => "User updated successfully",
            "data" => $user
        ]);
    }

    public function updatePassword(Request $request, string $id)
    {
        
        $user = User::find($id);

        if(!$user){
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $request->validate([
            'old_password' => 'required|string',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            "message" => "Password updated successfully",
            "data" => $user
        ]);
    }

    public function requestPasswordUpdate(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(["message" => "User not found"], 404);
        }

        $verificationCode = rand(100000, 999999);

        Mail::to($user->email)->send(new VerificationCodeMail($user->username, $verificationCode));

        $user->verification_code = $verificationCode;
        $user->code_expires_at = now()->addMinutes(10);
        $user->save();

        return response()->json([
            "message" => "A verification code has been sent to your email."
        ]);
    }

    public function confirmPasswordUpdate(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(["message" => "User not found"], 404);
        }

        $request->validate([
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($user->verification_code != $request->code || now()->gt($user->code_expires_at)) {
            return response()->json(["message" => "Invalid or expired verification code."], 422);
        }

        $user->password = bcrypt($request->password);
        $user->verification_code = null;
        $user->code_expires_at = null;
        $user->save();

        return response()->json([
            "message" => "Password updated successfully."
        ]);
    }

    // Step 1: Send the code (Trigger this via POST)
    public function initiateDelete(string $id)
    {
        $user = User::find($id);
        if(!$user) return response()->json(["message" => "User not found"], 404);

        $verificationCode = random_int(100000, 999999);
        $deleteKey = 'pending_delete_user_' . $user->email;

        Cache::put($deleteKey, $verificationCode, now()->addMinutes(10));
        Mail::to($user->email)->send(new VerificationCodeMail($user->username, $verificationCode));

        return response()->json(["message" => "Verification code sent to your email."]);
    }

    // Step 2: Actually delete the user (Trigger this via DELETE)
    public function confirmDelete(Request $request, string $id)
    {
        $user = User::find($id);
        $request->validate(['code' => 'required']);

        $deleteKey = 'pending_delete_user_' . $user->email;
        $storedCode = Cache::get($deleteKey);

        if (!$storedCode || $request->code != $storedCode) {
            return response()->json(["message" => "Invalid or expired verification code."], 422);
        }

        // Code is correct, proceed with deletion
        $user->delete();
        Cache::forget($deleteKey);

        return response()->json(["message" => "Account successfully deleted."]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if(!$user){
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $verificationCode = random_int(100000, 999999);
        $deleteKey = 'pending_delete_user_' . $user->email;

        Cache::put($deleteKey, $verificationCode, now()->addMinutes(10));

        Mail::to($user->email)->send(new VerificationCodeMail($user->username, $verificationCode));

        // $user->delete();

        return response()->json([
            "message" => "A verification code has been sent to your email to confirm account deletion.",
            "email"   => $user->email
        ]);
    }
}
