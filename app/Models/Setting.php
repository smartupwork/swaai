<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'logo',

        'sec_spl_srn_image',

        'sec_spl_srn_title',

        'sec_spl_srn_desc',

        'business_spl_srn_image',

        'business_spl_srn_title',

        'consumer_spl_srn_image',

        'consumer_spl_srn_title'
    ];
}
