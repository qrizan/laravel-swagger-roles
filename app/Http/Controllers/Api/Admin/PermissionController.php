<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\PermissionResource;

class PermissionController extends Controller
{    
    /**
    * @OA\Get(
    *     path="/api/admin/permissons",
    *     tags={"Admin"},
    *     summary="Get permissons",
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
        $permissions = Permission::when(request()->search, function($permissions) {
            $permissions = $permissions->where('name', 'like', '%'. request()->search . '%');
        })->latest()->paginate(10);

        $permissions->appends(['search' => request()->search]);

        return new PermissionResource(true, 'Successfully get permissions data', $permissions);
    }
    
    /**
    * @OA\Get(
    *     path="/api/admin/permissons/all",
    *     tags={"Admin"},
    *     summary="Get all permissons",
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
        $permissions = Permission::latest()->get();
        return new PermissionResource(true, 'Successfully get all permissions data', $permissions);
    }    
}
