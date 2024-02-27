<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{    

    /**
     * __construct
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware(['permission:users.index'], ['only' => ['index']]);
        $this->middleware(['permission:users.create'], ['only' => ['store']]);
        $this->middleware(['permission:users.edit'], ['only' => ['update','show']]);
        $this->middleware(['permission:users.delete'], ['only' => ['destroy']]);
    }

    /**
    * @OA\Get(
    *     path="/api/admin/users",
    *     tags={"Admin"},
    *     summary="Get users",
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
    public function index()
    {
        $users = User::when(request()->search, function($users) {
            $users = $users->where('name', 'like', '%'. request()->search . '%');
        })->with('roles')->latest()->paginate(10);

        $users->appends(['search' => request()->search]);
        
        return new UserResource(true, 'Success', $users);
    }
    
    /**
    * @OA\Post(
    *     path="/api/admin/users",
    *     tags={"Admin"},
    *     summary="Store new user data",
    *     security={{ "apiAuth": {} }},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="name",
    *                     description="Name user",
    *                     type="string"
    *                 ),
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
    *                 @OA\Property(
    *                     property="password_confirmation",
    *                     description="Password user",
    *                     type="string"
    *                 ),    
    *                 @OA\Property(
    *                     property="roles",
    *                     description="Password user",
    *                     type="string"
    *                 ),    
    *                 example={"name": "test admin","email": "admin@email.com","password": "Password123$","password_confirmation": "Password123$","roles": {"marketing", "author", "reviewer"}}
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|min:3|max:255',
            'email'    => 'required|string|email|min:3|max:255|unique:users,email,',
            'roles'    => 'required',
            'roles.*'  => 'required|string|distinct|exists:roles,name',
            'password' => [
                'required',
                'string',
                'max:255',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    //->uncompromised(3)
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name'      => strip_tags($request->name),
            'email'     => strip_tags($request->email),
            'password'  => bcrypt($request->password)
        ]);

        $user->assignRole($request->roles);

        if($user) {
            return new UserResource(true, 'Success', $user);
        }

        return new UserResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Get(
    *     path="/api/admin/users/{id}",
    *     tags={"Admin"},
    *     summary="Get user detail by id",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="ID user",
    *       required=true,
    *       @OA\Schema(type="number")
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
    public function show($id)
    {
        $user = User::with('roles')->whereId($id)->first();
        
        if($user) {
            return new UserResource(true, 'Success', $user);
        }

        return new UserResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Put(
    *     path="/api/admin/users/{id}",
    *     tags={"Admin"},
    *     summary="Update user by id",
    *     security={{ "apiAuth": {} }},
     *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="ID user",
    *       required=true,
    *       @OA\Schema(type="number")
    *     ),           
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="name",
    *                     description="Name user",
    *                     type="string"
    *                 ),
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
    *                 @OA\Property(
    *                     property="password_confirmation",
    *                     description="Password user",
    *                     type="string"
    *                 ),    
    *                 @OA\Property(
    *                     property="roles",
    *                     description="Password user",
    *                       type="array",
    *                       @OA\Items(
    *                       type="string",
    *                           description="The survey ID",
    *                           @OA\Schema(type="number")
    *                       ),
    *                 ),    
    *                 example={"name": "test admin","email": "admin@email.com","password": "Password123$","password_confirmation": "Password123$","roles": {"marketing", "author", "reviewer"}}
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
    public function update(Request $request, User $user)
    {

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|min:3|max:255',
            'email'    => 'required|string|email|min:3|max:255|unique:users,email,'.$user->id,
            'password' => 'confirmed',
            'roles'    => 'required',
            'roles.*'  => 'required|string|distinct|exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if($request->password == "") {
            $user->update([
                'name'      => strip_tags($request->name),
                'email'     => strip_tags($request->email),
            ]);

        } else {
            $user->update([
                'name'      => strip_tags($request->name),
                'email'     => strip_tags($request->email),
                'password'  => bcrypt($request->password)
            ]);
        }

        $user->syncRoles($request->roles);

        if($user) {
            return new UserResource(true, 'Success', $user);
        }

        return new UserResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Delete(
    *     path="/api/admin/users/{id}",
    *     tags={"Admin"},
    *     summary="Remove user by id",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="ID user",
    *       required=true,
    *       @OA\Schema(type="number")
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
    public function destroy(User $user)
    {
        if($user->delete()) {
            return new UserResource(true, 'Success', null);
        }

        return new UserResource(false, 'Failed', null);
    }    
}
