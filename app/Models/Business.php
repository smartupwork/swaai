<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $table = 'businesses';

    protected $fillable = [
        'user_id',
        'cat_id',
        'name',
        'business_address',
        'is_featured',
        'status',
        'description',
        'website_url',
        'latitude',
        'longitude'
    ];


    public function images()
    {
        return $this->hasMany(UserMedia::class, 'user_id', 'user_id');
    }

    public function savedByUsers(): HasMany
    {
        return $this->hasMany(SavedBusiness::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckInBusiness::class);
    }
}
