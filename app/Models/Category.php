<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Category extends Model
{
    use HasFactory;
    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'image'
    ];
    
    /**
     * posts
     *
     * @return void
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    /**
     * image
     *
     * @return Attribute
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => asset('/storage/categories/' . $image),
        );
    }
}
