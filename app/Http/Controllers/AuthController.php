<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct() {
        
        $this->middleware('auth:api', ['except' => ['query', 'login', 'register']]);

    }

    public function query() {

        $user = User::get();
        return response()->json([
            'user' => $user,
            'message' => 'User retrieved successfully'
        ], 200);

    }

    public function login(Request $request) {

        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string'
        ]);
        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'errors' => 'invalid username and password'
            ], 401);
        }

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
        ]);

        // $credentials = request(['email', 'password']);

        // if (!auth()->attempt($credentials)) {
        //     return response()->json([
        //         'errors' => 'invalid username and password'
        //     ], 401);
        // }

        // $token = auth('api')->claims(['roles' => auth('api')->user()->getRoleIDs()])->attempt($credentials);

        return response()->json([
            'user' => auth('api')->user(),
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
        ]);

    }

    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'name'      => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string',
            'address'   => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'address'   => $request->address
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);

    }

    public function logout() {

        Auth::logout();
        return response()->json([
            'message' => 'User logged out successfully'
        ]);

    }

    public function refresh() {


        return response()->json([
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'Bearer'
            ]
        ]);

    }

}
