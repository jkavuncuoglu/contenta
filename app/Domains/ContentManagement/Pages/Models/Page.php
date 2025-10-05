<?php

namespace App\Domains\ContentManagement\Pages\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'published',
    ];
}
