<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{    

    /**
     * __construct
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware(['permission:categories.index'], ['only' => ['index']]);
        $this->middleware(['permission:categories.create'], ['only' => ['store']]);
        $this->middleware(['permission:categories.edit'], ['only' => ['update','show']]);
        $this->middleware(['permission:categories.delete'], ['only' => ['destroy']]);
    }

    /**
    * @OA\Get(
    *     path="/api/admin/categories",
    *     tags={"Admin"},
    *     summary="Get categories",
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
        $categories = Category::when(request()->search, function($categories) {
            $categories = $categories->where('name', 'like', '%'. request()->search . '%');
        })->latest()->paginate(10);

        $categories->appends(['search' => request()->search]);
        
        return new CategoryResource(true, 'Success', $categories);
    }
    
    /**
    * @OA\Post(
    *     path="/api/admin/categories",
    *     tags={"Admin"},
    *     summary="Store new category data",
    *     security={{ "apiAuth": {} }},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="name",
    *                     description="Category name",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="image",
    *                     description="Category image",
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
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'image'    => 'required|mimes:jpeg,jpg,png|max:2000',
            'name'     => 'required|string|min:3|max:255|unique:categories',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/categories', $image->hashName());

        $category = Category::create([
            'image'=> $image->hashName(),
            'name' => strip_tags($request->name),
            'slug' => Str::slug($request->name, '-'),
        ]);

        if($category) {
            return new CategoryResource(true, 'Success', $category);
        }

        return new CategoryResource(false, 'Failed', null);
    }    
    
   /**
    * @OA\Get(
    *     path="/api/admin/categories/{id}",
    *     tags={"Admin"},
    *     summary="Get category detail",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="ID category",
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
        $category = Category::whereId($id)->first();
        
        if($category) {
            return new CategoryResource(true, 'Success', $category);
        }

        return new CategoryResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Put(
    *     path="/api/admin/categories/{id}",
    *     tags={"Admin"},
    *     summary="Update category by id (documentation not working now)",
    *     security={{ "apiAuth": {} }},
    *     @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Category id",
    *       required=true,
    *       @OA\Schema(type="number")
    *     ),           
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="name",
    *                     description="Category name",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="image",
    *                     description="Category image",
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
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'image'    => 'nullable|image|mimes:jpeg,jpg,png|max:2000',
            'name'     => 'required|string|min:3|max:255|unique:categories,name,'.$category->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->file('image')) {

            // remove old image
            Storage::disk('local')->delete('public/categories/'.basename($category->image));
        
            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/categories', $image->hashName());

            $category->update([
                'image'=> $image->hashName(),
                'name' => strip_tags($request->name),
                'slug' => Str::slug($request->name, '-'),
            ]);

        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
        ]);

        if($category) {
            return new CategoryResource(true, 'Success', $category);
        }

        return new CategoryResource(false, 'Failed', null);
    }    
    
    /**
    * @OA\Delete(
    *     path="/api/admin/categories/{id}",
    *     tags={"Admin"},
    *     summary="Remove category by id",
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
    public function destroy(Category $category)
    {
        // remove image
        Storage::disk('local')->delete('public/categories/'.basename($category->image));

        if($category->delete()) {
            return new CategoryResource(true, 'Success', null);
        }
        return new CategoryResource(false, 'Failed', null);
    }  
    

    /**
    * @OA\Get(
    *     path="/api/admin/categories/all",
    *     tags={"Admin"},
    *     summary="Get all categories",
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
        $categories = Category::latest()->get();

        return new CategoryResource(true, 'Success', $categories);
    }    
}
