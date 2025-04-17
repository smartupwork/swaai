<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumerAnalytic extends Model
{
    use HasFactory;

    
    protected $table = 'consumer_analytics';

    protected $fillable = [
        'user_id',
        'local_impact',
        'family_impact',
        'cultural_impact',
        'sustain_impact',
        'non_pr_impact'
    ];
}
