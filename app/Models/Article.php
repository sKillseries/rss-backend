<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'link',
        'source',
        'pub_date',
        'category',
        'is_read',
        'is_favorite',
    ];
}
