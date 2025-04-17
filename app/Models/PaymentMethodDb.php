<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodDb extends Model
{
    use HasFactory;

    protected $table = 'payment_methods';

    protected $fillable = [
        'user_id',
        'country_id',
        'stripe_method_id',
        'card_type',
        'last_4',
        'expiry_month',
        'expiry_year'
    ];
}
