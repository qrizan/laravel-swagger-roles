<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;

class DashboardController extends Controller
{
    /**
    * @OA\Get(
    *     path="/api/admin/dashboard",
    *     tags={"Admin"},
    *     summary="Get dashboard data",
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
    public function __invoke(Request $request)
    {
        $categories = Category::count();
        $posts = Post::count();
        $users = User::count();
    
        return response()->json([
            'success'   => true,
            'message'   => 'Success',
            'data'      => [
                'categories' => $categories,
                'posts'      => $posts,
                'users'      => $users,
            ]
        ]);

    }

}
