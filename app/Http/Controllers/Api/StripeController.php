<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\UserMeta;
use App\Models\PaymentMethodDb;
use App\Models\Subscriptions;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Stripe\PaymentIntent;

use Stripe\Customer;

use Stripe\PaymentMethod;

use Stripe\Subscription;
use Illuminate\Support\Facades\DB;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    // Fetch plans (prices) from Stripe
    public function getPlans()
    {
        try {
            
            // Fetch all products from Stripe
            $products = \Stripe\Product::all();
            
            // Debug log to confirm if products are returned
            \Log::info('Stripe Products:', $products->data);
    
            // Check if products exist
            if (empty($products->data)) {
                return response()->json(['error' => 'No products found'], 404);
            }
    
            // Initialize an empty array for plans
            $plans = [];
    
            // Loop through each product to fetch associated prices
            foreach ($products->data as $product) {
                // Fetch prices for each product
                $prices = \Stripe\Price::all(['product' => $product->id]);
    
                // Debug log to confirm if prices are returned for each product
                \Log::info("Prices for Product ID {$product->id}: ", $prices->data);
    
                // Check if prices exist for the product
                if (empty($prices->data)) {
                    \Log::info("No prices found for product: " . $product->name);
                    continue;  // Skip this product if no prices are found
                }
    
                // Loop through the prices and format the plan details
                foreach ($prices->data as $price) {
                    $plans[] = [
                        'product' => $product->name,
                        'price' => $price->unit_amount / 100, // Convert cents to dollars
                        'currency' => strtoupper($price->currency),
                        'price_id' => $price->id,  // You need this when performing checkout
                    ];
                }
            }
    
            // Return the plans as a JSON response
            return response()->json($plans);
    
        } catch (\Exception $e) {
            // Log the error and return a response
            \Log::error('Error fetching plans: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch plans'], 500);
        }
    }
    

  public function subscribeWithCard(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'price_id' => 'required',
            'product' => 'required',
            'expiry_date' => 'required|string',
            'billing_address1' => 'required|string',
            'billing_address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'zip_code' => 'nullable|string',
        ]);
        try {
            \Stripe\Stripe::setApiKey('');
            $user = User::findOrFail($request->user_id);
            if ($user->stripe_customer_id) {
                $stripe = new \Stripe\StripeClient('');
                $subscriptions = $stripe->subscriptions->all([
                    'customer' => $user->stripe_customer_id,
                    'status' => 'all',
                ]);
                $activeStatuses = ['active', 'trialing'];
                foreach ($subscriptions->data as $subscription) {
                    if (in_array($subscription->status, $activeStatuses)) {
                        return response()->json([
                            'message' => 'You already have an active or trialing subscription.',
                        ], 400);
                    }
                }
            }
            if (!$user->stripe_customer_id) {
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'address' => [
                        'line1' => $request->billing_address1,
                        'line2' => $request->billing_address2 ?? '',
                        'city' => $request->city ?? '',
                        'state' => $request->state ?? '',
                        'postal_code' => $request->postal_code ?? '',
                    ],
                ]);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            } else {
                $customer = \Stripe\Customer::retrieve($user->stripe_customer_id);
            }
            $token = 'tok_visa'; // Use a live token for production
            $paymentMethod = \Stripe\PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'token' => $token,
                ],
            ]);
            $paymentMethod->attach(['customer' => $customer->id]);
            $payment = PaymentMethodDb::create([
                'user_id' => $user->id,
                'stripe_method_id' => $paymentMethod->id,
                'card_type' => $paymentMethod->card->brand,
                'expiry_month' => $paymentMethod->card->exp_month,
                'expiry_year' => $paymentMethod->card->exp_year,
                'last_4' => $paymentMethod->card->last4,
            ]);
            UserMeta::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'billing_address1' => $request->billing_address1,
                    'billing_address2' => $request->billing_address2 ?? '',
                    'city' => $request->city ?? '',
                    'state' => $request->state ?? '',
                    'postal_code' => $request->postal_code ?? '',
                    'country_id' => 1,
                ]
            );
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [['price' => $request->price_id]],
                'default_payment_method' => $paymentMethod->id,
            ]);
            $status = $subscription->status === 'active' ? 1 : 0;
            \App\Models\Subscriptions::create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'stripe_plan_id' => $request->price_id,
                'plan' => $request->product,
                'status' => $status,
                'start_date' => now(),
                'end_date' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
            ]);
            return response()->json([
                'message' => 'Subscription created successfully.',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Subscription Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllCards($id)
    {
        try {
            // Retrieve the user by ID
            $user = User::findOrFail($id);
            // Check if the user has a Stripe customer ID
            if (!$user->stripe_customer_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User does not have a valid Stripe customer ID.',
                ]);
            }
            // Get the cards from the database for the user
            $cards = DB::table('cards')
                ->where('user_id', $id)
                ->get();
            $cardDetails = [];
            foreach ($cards as $card) {
                $cardDetails[] = [
                    'id' => $card->stripe_id,  // Use the stripe_id from the database
                    'last4' => $card->last4,
                    'brand' => $card->brand,
                    'exp_month' => $card->exp_month,
                    'exp_year' => $card->exp_year,
                    'is_default' => $card->is_default,  // Assuming you store the default status in the database
                ];
            }
            return response()->json([
                'status' => 'success',
                'cards' => $cardDetails,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

   public function addNewCard(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'card_number' => 'required|string',
            'expiry_date' => 'required|string',
            'cvc' => 'required|string',
            'name' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);
        try {
            \Stripe\Stripe::setApiKey('');
            $user = User::findOrFail($request->user_id);
            if (!$user->stripe_customer_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please provide a valid Stripe Customer ID.',
                ]);
            }
            $last4 = substr($request->card_number, -4);
            $expMonth = substr($request->expiry_date, 1, 1);
            $expYear = substr($request->expiry_date, 3, 4);
            $existingCard = DB::table('cards')
                ->where('user_id', $request->user_id)
                ->where('last4', $last4)
                ->where('exp_month', $expMonth)
                ->where('exp_year', $expYear)
                ->first();
            if ($existingCard) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This card is already saved in your account.',
                ]);
            }
            $token = 'tok_mastercard';
            $paymentMethod = \Stripe\PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'token' => $token,
                ],
            ]);
            $paymentMethod->attach([
                'customer' => $user->stripe_customer_id,
            ]);
            DB::table('cards')->insert([
                'user_id' => $request->user_id,
                'stripe_id' => $paymentMethod->id,
                'last4' => $paymentMethod->card->last4,
                'brand' => $paymentMethod->card->brand,
                'card_type' => $paymentMethod->card->funding,
                'exp_month' => $paymentMethod->card->exp_month,
                'exp_year' => $paymentMethod->card->exp_year,
                'is_default' => $request->is_default ? 1 : 0,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Card added successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function setDefaultCard(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'stripe_payment_method_id' => 'required|string'
        ]);
        try {
            \Stripe\Stripe::setApiKey('');
            $user = User::findOrFail($request->user_id);
            if (!$user->stripe_customer_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User does not have a valid Stripe customer ID.',
                ]);
            }
            $customer = \Stripe\Customer::update(
                $user->stripe_customer_id,
                [
                    'invoice_settings' => [
                        'default_payment_method' => $request->stripe_payment_method_id,
                    ],
                ]
            );
            DB::table('cards')
                ->where('user_id', $user->id)
                ->update(['is_default' => 0]);
            DB::table('cards')
                ->where('user_id', $user->id)
                ->where('stripe_id', $request->stripe_payment_method_id)
                ->update(['is_default' => 1]);
            return response()->json([
                'status' => 'success',
                'message' => 'Default card updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}


