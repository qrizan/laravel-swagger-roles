<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{    
    /**
    * @OA\Get(
    *     path="/api/public/categories",
    *     tags={"Public"},
    *     summary="Get public categories",
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
        $categories = Category::latest()->paginate(10);

        return new CategoryResource(true, 'Success', $categories);
    }
    
    /**
    * @OA\Get(
    *     path="/api/public/categories/{slug}",
    *     tags={"Public"},
    *     summary="Get category detail by category slug",
    *     @OA\Parameter(
    *       name="slug",
    *       in="path",
    *       description="Category slug",
    *       required=true,
    *       @OA\Schema(type="string")
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
    public function show($slug)
    {
        $category = Category::with('posts.category', 'posts.user')->where('slug', $slug)->first();

        if($category) {
            return new CategoryResource(true, 'Success', $category);
        }

        return new CategoryResource(false, 'Success', null);
    }    
}
