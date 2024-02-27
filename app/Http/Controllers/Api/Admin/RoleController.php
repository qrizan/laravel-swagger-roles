<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{    

    /**
     * __construct
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware(['permission:roles.index'], ['only' => ['index','all']]);
        $this->middleware(['permission:roles.create'], ['only' => ['store']]);
        $this->middleware(['permission:roles.edit'], ['only' => ['update','show']]);
        $this->middleware(['permission:roles.delete'], ['only' => ['destroy']]);
    }

    /**
    * @OA\Get(
    *     path="/api/admin/roles",
    *     tags={"Admin"},
    *     summary="Get roles",
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
        $roles = Role::when(request()->search, function($roles) {
            $roles = $roles->where('name', 'like', '%'. request()->search . '%');
        })->with('permissions')->latest()->paginate(10);

        $roles->appends(['search' => request()->search]);

        return new RoleResource(true, 'Success', $roles);
    }
    
    /**
    * @OA\Post(
    *     path="/api/admin/roles",
    *     tags={"Admin"},
    *     summary="Store new role data",
    *     security={{ "apiAuth": {} }},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="name",
    *                     description="Role name",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="permission",
    *                     description="Permissions name",
    *                     type="string"
    *                 ),    
    *                 example={"name": "new role","permissions": {"posts.index", "categories.index"}}
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
            'name'          => 'required|string|min:3|max:255|unique:roles,name',
            'permissions'   => 'required',
            'permissions.*' => 'required|string|distinct|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::create(['name' => strip_tags($request->name)]);
        $role->givePermissionTo($request->permissions);

        if($role) {
            return new RoleResource(true, 'Success', $role);
        }

        return new RoleResource(false, 'Failed', null);
    }    

    
    /**
    * @OA\Get(
    *     path="/api/admin/roles/{id}",
    *     tags={"Admin"},
    *     summary="Get role detail by id",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="ID role",
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
        $role = Role::with('permissions')->findOrFail($id);

        if($role) {
            return new RoleResource(true, 'Success', $role);
        }

        return new RoleResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Put(
    *     path="/api/admin/roles/{id}",
    *     tags={"Admin"},
    *     summary="Update role by id",
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
    *                     description="Role name",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="permission",
    *                     description="Permissions name",
    *                     type="string"
    *                 ),    
    *                 example={"name": "new role","permissions": {"posts.index", "categories.index"}}
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
    public function update(Request $request, Role $role)
    {
        /**
         * validate request
         */
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|min:3|max:255|',
            'permissions'   => 'required',
            'permissions.*' => 'required|string|distinct|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role->update(['name' => strip_tags($request->name)]);
        $role->syncPermissions($request->permissions);

        if($role) {
            return new RoleResource(true, 'Success', $role);
        }

        return new RoleResource(false, 'Failed', null);
    }  
    
    /**
    * @OA\Delete(
    *     path="/api/admin/roles/{id}",
    *     tags={"Admin"},
    *     summary="Remove role by id",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="ID role",
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
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if($role->delete()) {
            return new RoleResource(true, 'Success', null);
        }

        return new RoleResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Get(
    *     path="/api/admin/roles/all",
    *     tags={"Admin"},
    *     summary="Get all roles",
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
    public function all()
    {
        $roles = Role::latest()->get();
        return new RoleResource(true, 'Success', $roles);
    }    
}
