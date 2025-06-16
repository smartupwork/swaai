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
use Stripe\BillingPortal\Session as BillingPortalSession;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionConfirmation;

use Illuminate\Support\Facades\DB;

class StripeController extends Controller
{

    protected $stripeClient;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripeClient = new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    public function createBillingPortal(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        if (!$user->stripe_customer_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stripe customer ID not found for this user.',
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = BillingPortalSession::create([
            'customer' => $user->stripe_customer_id,
            'return_url' => 'https://yourapp.com/account',
        ]);

        return response()->json([
            'status' => 'success',
            'url' => $session->url,
        ]);
    }

    public function getPlans()
    {
        try {
            $products = $this->stripeClient->products->all();

            \Log::info('Stripe Products:', $products->data);

            if (empty($products->data)) {
                return response()->json(['error' => 'No products found'], 404);
            }

            $plans = [];

            foreach ($products->data as $product) {
                $prices = $this->stripeClient->prices->all(['product' => $product->id]);

                \Log::info("Prices for Product ID {$product->id}:", $prices->data);

                if (empty($prices->data)) {
                    \Log::info("No prices found for product: " . $product->name);
                    continue;
                }

                foreach ($prices->data as $price) {
                    $plans[] = [
                        'product' => $product->name,
                        'price' => $price->unit_amount / 100,
                        'currency' => strtoupper($price->currency),
                        'price_id' => $price->id,
                    ];
                }
            }

            return response()->json($plans);
        } catch (\Exception $e) {
            \Log::error('Error fetching plans: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch plans'], 500);
        }
    }

    public function showCheckoutForm(Request $request)
    {

        $request->validate([
            'token' => 'required',
            'price_id' => 'required',
            'product' => 'required',
            'user_id' => 'required',
            'email' => 'required|email',
        ]);

        return view('subscription.checkout', [
            'stripeKey' => config('services.stripe.public'),
            'token' => $request->token,
            'price_id' => $request->price_id,
            'user_id' => $request->user_id,
            'product' => $request->product,
            'email' => $request->email,
        ]);
    }

    // public function subscribeWithCard(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'price_id' => 'required',
    //         'product' => 'required',
    //         'expiry_date' => 'required|string',
    //         'billing_address1' => 'required|string',
    //         'billing_address2' => 'nullable|string',
    //         'city' => 'nullable|string',
    //         'state' => 'nullable|string',
    //         'postal_code' => 'nullable|string',
    //         'zip_code' => 'nullable|string',
    //     ]);
    //     try {

    //         $user = User::findOrFail($request->user_id);

    //         \App\Models\Subscriptions::where('user_id', $user->id)->delete();

    //         if (!$user->stripe_customer_id) {
    //             $customer = \Stripe\Customer::create([
    //                 'email' => $user->email,
    //                 'name' => $user->first_name . ' ' . $user->last_name,
    //                 'address' => [
    //                     'line1' => $request->billing_address1,
    //                     'line2' => $request->billing_address2 ?? '',
    //                     'city' => $request->city ?? '',
    //                     'state' => $request->state ?? '',
    //                     'postal_code' => $request->postal_code ?? '',
    //                 ],
    //             ]);
    //             $user->stripe_customer_id = $customer->id;
    //             $user->save();
    //         } else {
    //             $customer = \Stripe\Customer::retrieve($user->stripe_customer_id);
    //         }
    //         $token = 'tok_visa'; // Use a live token for production
    //         $paymentMethod = \Stripe\PaymentMethod::create([
    //             'type' => 'card',
    //             'card' => [
    //                 'token' => $token,
    //             ],
    //         ]);
    //         $paymentMethod->attach(['customer' => $customer->id]);
    //         $payment = PaymentMethodDb::create([
    //             'user_id' => $user->id,
    //             'stripe_method_id' => $paymentMethod->id,
    //             'card_type' => $paymentMethod->card->brand,
    //             'expiry_month' => $paymentMethod->card->exp_month,
    //             'expiry_year' => $paymentMethod->card->exp_year,
    //             'last_4' => $paymentMethod->card->last4,
    //         ]);
    //         UserMeta::updateOrCreate(
    //             ['user_id' => $user->id],
    //             [
    //                 'billing_address1' => $request->billing_address1,
    //                 'billing_address2' => $request->billing_address2 ?? '',
    //                 'city' => $request->city ?? '',
    //                 'state' => $request->state ?? '',
    //                 'postal_code' => $request->postal_code ?? '',
    //                 'country_id' => 1,
    //             ]
    //         );
    //         $subscription = \Stripe\Subscription::create([
    //             'customer' => $customer->id,
    //             'items' => [['price' => $request->price_id]],
    //             'default_payment_method' => $paymentMethod->id,
    //         ]);
    //         $status = $subscription->status === 'active' ? 1 : 0;
    //         \App\Models\Subscriptions::create([
    //             'user_id' => $user->id,
    //             'payment_id' => $payment->id,
    //             'stripe_plan_id' => $request->price_id,
    //             'plan' => $request->product,
    //             'status' => $status,
    //             'start_date' => now(),
    //             'end_date' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
    //         ]);
    //         return response()->json([
    //             'message' => 'Subscription created successfully.',
    //             'subscription_id' => $subscription->id,
    //         ]);
    //     } catch (\Exception $e) {
    //         \Log::error('Subscription Error: ' . $e->getMessage());
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function subscribeWithCard(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'price_id' => 'required|string',
            'product' => 'required|string',
            'payment_method_id' => 'required|string',
            'billing_address1' => 'required|string',
            'billing_address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'required|string|max:2',
        ]);

        try {
            $user = User::findOrFail($request->user_id);

            // Delete any existing subscription records
            \App\Models\Subscriptions::where('user_id', $user->id)->delete();

            // Create Stripe customer if not already created
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
                        'country' => strtoupper($request->country),
                    ],
                ]);

                $user->stripe_customer_id = $customer->id;
                $user->save();
            } else {
                $customer = \Stripe\Customer::retrieve($user->stripe_customer_id);
            }

            // Attach provided payment method to the customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);
            $paymentMethod->attach(['customer' => $customer->id]);

            // Update customer's default payment method
            \Stripe\Customer::update($customer->id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod->id,
                ],
            ]);

            // Save card details locally
            $payment = PaymentMethodDb::create([
                'user_id' => $user->id,
                'stripe_method_id' => $paymentMethod->id,
                'card_type' => $paymentMethod->card->brand,
                'expiry_month' => $paymentMethod->card->exp_month,
                'expiry_year' => $paymentMethod->card->exp_year,
                'last_4' => $paymentMethod->card->last4,
            ]);

            // Save billing address
            UserMeta::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'billing_address1' => $request->billing_address1,
                    'billing_address2' => $request->billing_address2 ?? '',
                    'city' => $request->city ?? '',
                    'state' => $request->state ?? '',
                    'postal_code' => $request->postal_code ?? '',
                    'country_id' => 1, // Change this if you're storing ISO code
                ]
            );

            // Create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => $request->price_id],
                ],
                'default_payment_method' => $paymentMethod->id,
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $paymentIntent = $subscription->latest_invoice->payment_intent ?? null;
            $lastPaymentError = $paymentIntent->last_payment_error->message ?? null;

            // Get friendly status message
            $statusMessage = Subscriptions::getStripeStatusMessage($subscription->status, $lastPaymentError);
            // Save subscription record in DB
            $subscriptionRecord = \App\Models\Subscriptions::create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'stripe_plan_id' => $request->price_id,
                'plan' => $request->product,
                'status' => $subscription->status,
                'start_date' => now(),
                'end_date' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
                'status_message' => $statusMessage,
            ]);

            Mail::to($user->email)->send(new SubscriptionConfirmation($user, $subscriptionRecord));

            return view('subscription.success', [
                'message' => $statusMessage,
                'subscription_id' => $subscription->id,
                'return_url' => 'myapp://payment-status?success=true'
            ]);
        } catch (\Exception $e) {
            \Log::error('Subscription Error: ' . $e->getMessage());

            return view('subscription.success', [
                'message' => 'Sorry something went wrong.',
                'return_url' => 'myapp://payment-status?success=false'
            ]);
        }
    }


    public function getAllCards($id)
    {
        try {

            $user = User::findOrFail($id);
            if (!$user->stripe_customer_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User does not have a valid Stripe customer ID.',
                ]);
            }

            $cards = DB::table('cards')
                ->where('user_id', $id)
                ->get();

            $cardDetails = [];
            foreach ($cards as $card) {
                $cardDetails[] = [
                    'card_id' => $card->id,
                    'card_stripe_id' => $card->stripe_id,
                    'last4' => $card->last4,
                    'brand' => $card->brand,
                    'exp_month' => $card->exp_month,
                    'exp_year' => $card->exp_year,
                    'is_default' => $card->is_default,
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
            $user = User::findOrFail($request->user_id);

            if (!$user->stripe_customer_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please provide a valid Stripe Customer ID.',
                ]);
            }

            $last4 = substr($request->card_number, -4);
            $expMonth = substr($request->expiry_date, 0, 2);
            $expYear = substr($request->expiry_date, -4);

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

            $paymentMethod = $this->stripeClient->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'token' => $token,
                ],
            ]);

            $this->stripeClient->paymentMethods->attach(
                $paymentMethod->id,
                ['customer' => $user->stripe_customer_id]
            );

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
            \Log::error('Add Card Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function editCard(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'card_id' => 'required|exists:cards,id',
            'stripe_card_id' => 'required|string',
            'card_number' => 'required|string',
            'expiry_date' => 'required|string',
            'cvc' => 'required|string',
            'name' => 'nullable|string',
        ]);

        try {
            $user = User::findOrFail($request->user_id);

            $last4 = substr($request->card_number, -4);
            $expMonth = substr($request->expiry_date, 0, 2);
            $expYear = substr($request->expiry_date, -4);

            // This token is a placeholder. Replace with actual tokenization logic if using real card input.
            $token = 'tok_visa';

            // Create a new payment method on Stripe (without attaching to customer)
            $newPaymentMethod = $this->stripeClient->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'token' => $token,
                ],
            ]);

            // Update card details in the local database
            DB::table('cards')
                ->where('id', $request->card_id)
                ->update([
                    'stripe_id' => $newPaymentMethod->id,
                    'last4' => $newPaymentMethod->card->last4,
                    'brand' => $newPaymentMethod->card->brand,
                    'card_type' => $newPaymentMethod->card->funding,
                    'exp_month' => $newPaymentMethod->card->exp_month,
                    'exp_year' => $newPaymentMethod->card->exp_year,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Card updated successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Edit Card Error: ' . $e->getMessage());
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
            'stripe_payment_method_id' => 'required|string',
        ]);

        try {
            $user = User::findOrFail($request->user_id);

            if (!$user->stripe_customer_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User does not have a valid Stripe customer ID.',
                ]);
            }

            $this->stripeClient->customers->update(
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
            \Log::error('Set Default Card Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
