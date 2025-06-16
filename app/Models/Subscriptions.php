<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'payment_id',
        'stripe_method_id',
        'plan',
        'status',
        'status_message',
        'start_date',
        'end_date'
    ];



    public static function getStripeStatusMessage($status, $lastPaymentErrorMessage = null)
    {
        return match ($status) {
            'incomplete' => $lastPaymentErrorMessage
                ? 'Payment failed: ' . $lastPaymentErrorMessage
                : 'Your payment is incomplete. Please complete it to activate your subscription.',
            'incomplete_expired' => 'Your payment attempt failed and the subscription expired.',
            'past_due' => 'Your payment is past due. Please update your payment method.',
            'active' => 'Your subscription is active.',
            'trialing' => 'Your subscription is in trial period.',
            'unpaid' => 'Your payment failed and the subscription is now unpaid.',
            'canceled' => 'Your subscription has been canceled.',
            default => 'Subscription status is unknown. Please contact support.',
        };
    }
}
