<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMedia extends Model
{
    use HasFactory;

    protected $table = 'users_media';

    protected $fillable = [
        'images',
        'videos',
        'title',
        'description',
        'user_id',
        'image_redirect_url'
    ];
}
