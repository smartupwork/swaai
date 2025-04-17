<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;

    protected $table = 'communities';

    protected $fillable = [
        'user_id',
        'cat_id',
        'name',
        'description',
        'banner_image',
        'status',
        'members'
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'community_members');
    }

    public function membersCount()
    {
        return $this->members()->count();
    }
}
