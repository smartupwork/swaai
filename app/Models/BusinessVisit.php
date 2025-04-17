<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessVisit extends Model
{
    use HasFactory;

    protected $table = 'business_visits';

    protected $fillable = [
        'user_id',
        'business_id'
    ];
}
