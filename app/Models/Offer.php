<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $table = 'offers';

    protected $fillable = [
        'user_id',
        'business_id',
        'name',
        'price',
        'description',
        'status',
        'start_date',
        'end_date'
    ];
}
