<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    /**
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'body',
        'slug',
        'is_published',
        'tags',
        'publish_date',
        'meta_description',
        'keywords'
    ];



    /**
     * 
     * @var array
     */
    protected $casts = [
        'is_published' => 'boolean',
        'publish_date' => 'date:d-m-Y',
    ];
}
