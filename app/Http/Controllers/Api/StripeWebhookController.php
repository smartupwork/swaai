<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\PaymentMethodDb;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentMethodUpdatedMail;

class StripeWebhookController extends Controller
{
    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'payment_method.attached':
                $this->sendCardNotification($event->data->object, 'added');
                break;

            case 'payment_method.updated':
                $this->sendCardNotification($event->data->object, 'updated');
                break;

            case 'payment_method.detached':
                $this->sendCardNotification($event->data->object, 'removed');
                break;

            case 'payment_method.automatically_updated':
                $this->sendCardNotification($event->data->object, 'automatically updated');
                break;

            case 'customer.updated':
                // Check if default payment method changed
                $object = $event->data->object;
                if (isset($event->data->previous_attributes['invoice_settings']['default_payment_method'])) {
                    $this->sendCardNotification($object, 'set as default');
                }
                break;
        }

        return response('Webhook received', 200);
    }

    protected function sendCardNotification($object, $action)
    {
        $email = $this->getCustomerEmail($object); // Already defined
        $message = "A payment method was {$action} on your account.";

        Mail::to($email)->send(new PaymentMethodUpdatedMail($message));
    }

    protected function getCustomerEmail($object)
    {
        if (isset($object->customer)) {
            $stripeCustomer = \Stripe\Customer::retrieve($object->customer);
            return $stripeCustomer->email;
        }

        if (isset($object->email)) {
            return $object->email;
        }

        return 'vigorousishizaka@freethecookies.com'; // fallback
    }
}
