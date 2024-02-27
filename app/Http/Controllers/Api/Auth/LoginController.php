<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{    
    /**
    * @OA\Post(
    *     path="/api/login",
    *     tags={"Auth"},
    *     summary="Authenticate user and generate JWT token",
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="email",
    *                     description="Email user",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="password",
    *                     description="Password user",
    *                     type="string"
    *                 ),    
    *                 example={"email": "admin@gmail.com","password": "password"}
    *             )
    *         )
    *     ), 
    *     @OA\Response(
    *         response=201,
    *         description="Success",
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Success",
    *     ),    
    *     @OA\Response(
    *         response=401,
    *         description="Unauthenticated"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad Request"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="not found"
    *     ),
    *     @OA\Response(
    *        response=403,
    *        description="Forbidden"
    *     )        
    * )
    */
    public function index(Request $request){

        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email|max:255',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        // check failed user
        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email or Password is incorrect'
            ], 400);        
        }

        return $this->createNewToken($token);     
    }
    
    /**
    * @OA\Post(
    *     path="/api/logout",
    *     tags={"Auth"},
    *     summary="Logout user account and remove JWT token",
    *     security={{ "apiAuth": {} }},
    *     @OA\Response(
    *         response=201,
    *         description="Success",
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Success",
    *     ),    
    *     @OA\Response(
    *         response=401,
    *         description="Unauthenticated"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad Request"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="not found"
    *     ),
    *     @OA\Response(
    *        response=403,
    *        description="Forbidden"
    *     )  
    * )
    */
    public function logout()
    {
        // blacklist token
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json([
            'success' => true,
            'message' => 'User successfully signed out'
        ], 200);
    }
        
    
    /**
     * createNewToken
     *
     * @param  mixed $token
     * @return void
     */
    protected function createNewToken($token){
        return response()->json([
            'success'       => true,
            'message'       => 'User successfully login',
            'token'         => $token,
            'user'          => auth()->guard('api')->user()->only(['name', 'email']),
            'permissions'   => auth()->guard('api')->user()->getPermissionArray(),         
        ]);
    }

}
