<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{    
        
    /**
     * __construct
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware(['permission:posts.index'], ['only' => ['index']]);
        $this->middleware(['permission:posts.create'], ['only' => ['store','storeImagePost']]);
        $this->middleware(['permission:posts.edit'], ['only' => ['update','show']]);
        $this->middleware(['permission:posts.delete'], ['only' => ['destroy']]);
    }

    /**
    * @OA\Get(
    *     path="/api/admin/posts",
    *     tags={"Admin"},
    *     summary="Get posts",
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
        $posts = Post::with('user', 'category')->when(request()->search, function($posts) {
            $posts = $posts->where('title', 'like', '%'. request()->search . '%');
        })->where('user_id', auth()->user()->id)->latest()->paginate(10);
        
        $posts->appends(['search' => request()->search]);

        return new PostResource(true, 'Success', $posts);
    }
    
    /**
    * @OA\Post(
    *     path="/api/admin/posts",
    *     tags={"Admin"},
    *     summary="Store new post data",
    *     security={{ "apiAuth": {} }},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="title",
    *                     description="Post title",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="image",
    *                     description="Post image",
    *                     type="file",
    *                     format="file",
    *                 ),
    *                 @OA\Property(
    *                     property="category_id",
    *                     description="Post category (id)",
    *                     type="number"
    *                 ),
    *                 @OA\Property(
    *                     property="content",
    *                     description="Post content",
    *                     type="string"
    *                 ),        
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
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2000',
            'title'         => 'required|string|min:3|max:255|unique:posts',
            'category_id'   => 'required|numeric|exists:categories,id',
            'content'       => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Post::create([
            'image'       => $image->hashName(),
            'title'       => strip_tags($request->title),
            'slug'        => Str::slug($request->title, '-'),
            'category_id' => $request->category_id,
            'user_id'     => auth()->guard('api')->user()->id,
            'content'     => \Purifier::clean($request->content)
        ]);

        if($post) {
            return new PostResource(true, 'Success', $post);
        }

        return new PostResource(false, 'Failed', null);
    }    
    
   /**
    * @OA\Get(
    *     path="/api/admin/posts/{id}",
    *     tags={"Admin"},
    *     summary="Get post detail by id",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="ID post",
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
        $post = Post::with('category')->whereId($id)->where('user_id', auth()->user()->id)->first();
        
        if($post) {
            return new PostResource(true, 'Success', $post);
        }

        return new PostResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Put(
    *     path="/api/admin/posts/{id}",
    *     tags={"Admin"},
    *     summary="Update post data by id ( documentation not working now )",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Post id",
    *       required=true,
    *       @OA\Schema(type="number")
    *     ),     
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="title",
    *                     description="Post title",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="image",
    *                     description="Post image",
    *                     type="file",
    *                     format="file",
    *                 ),
    *                 @OA\Property(
    *                     property="category_id",
    *                     description="Post category (id)",
    *                     type="number"
    *                 ),
    *                 @OA\Property(
    *                     property="content",
    *                     description="Post content",
    *                     type="string"
    *                 ),        
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
    public function update(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
            'image'         => 'nullable|image|mimes:jpeg,jpg,png|max:2000',
            'title'         => 'required|string|min:3|max:255|unique:posts,title,'.$post->id,
            'category_id'   => 'required|numeric|exists:categories,id',
            'content'       => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($post->user_id != auth()->guard('api')->user()->id) {
            return new PostResource(false, 'Unauthorized', null);
        }

        if ($request->file('image')) {

            Storage::disk('local')->delete('public/posts/'.basename($post->image));
        
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            $post->update([
                'image'       => $image->hashName(),
                'title'       => strip_tags($request->title),
                'slug'        => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'user_id'     => auth()->guard('api')->user()->id,
                'content'     => \Purifier::clean($request->content)  
            ]);

        }

        $post->update([
                'title'        => strip_tags($request->title),
                'slug'        => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'user_id'     => auth()->guard('api')->user()->id,
                'content'     => \Purifier::clean($request->content)
        ]);

        if($post) {
            return new PostResource(true, 'Success', $post);
        }

        return new PostResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Delete(
    *     path="/api/admin/posts/{id}",
    *     tags={"Admin"},
    *     summary="Remove post by id",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="ID post",
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
    public function destroy(Post $post)
    {   
        if ($post->user_id != auth()->guard('api')->user()->id) {
            return new PostResource(false, 'Unauthorized', null);
        }

        Storage::disk('local')->delete('public/posts/'.basename($post->image));

        if($post->delete()) {
            return new PostResource(true, 'Success', null);
        }

        return new PostResource(false, 'Failed', null);
    }    

    /**
    * @OA\Post(
    *     path="/api/admin/posts/storeImagePost",
    *     tags={"Admin"},
    *     summary="Store new post image",
    *     security={{ "apiAuth": {} }},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="image",
    *                     description="Post image",
    *                     type="file",
    *                     format="file",
    *                 ),
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
    public function storeImagePost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/post_images', $image->hashName());

        return response()->json([
            'url'       => asset('storage/post_images/'.$image->hashName())
        ]);
    }    
}
