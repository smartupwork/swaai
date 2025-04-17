<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostShare extends Model
{
    use HasFactory;


    protected $table = 'post_shares';

    protected $fillable = [
        'user_id',
        'post_id',
        'share_to'
    ];
}
