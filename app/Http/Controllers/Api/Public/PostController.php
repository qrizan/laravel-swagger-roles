<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;

class PostController extends Controller
{    
    /**
    * @OA\Get(
    *     path="/api/public/posts",
    *     tags={"Public"},
    *     summary="Get public posts",
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
        $posts = Post::with('user', 'category')->when(request()->search, function($posts) {
            $posts = $posts->where('title', 'like', '%'. request()->search . '%');
        })->latest()->paginate(10);

        return new PostResource(true, 'Success', $posts);
    }
    
    /**
    * @OA\Get(
    *     path="/api/public/posts/{slug}",
    *     tags={"Public"},
    *     summary="Get posts by post slug",
    *     @OA\Parameter(
    *       name="slug",
    *       in="path",
    *       description="Post slug",
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
        $post = Post::with('user', 'category')->where('slug', $slug)->first();

        if($post) {
            return new PostResource(true, 'Success', $post);
        }

        return new PostResource(true, 'Failed', null);

    }    
}
