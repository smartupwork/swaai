<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Subscribe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md px-6 py-8 bg-gray-100 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Add a payment method</h2>
        <p class="text-sm text-gray-600 mb-6">You won’t be charged until you confirm the subscription</p>

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="subscription-form" action="{{ route('subscribe.submit') }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="user_id" value="{{ $user_id }}">
            <input type="hidden" name="product" value="{{ $product }}">
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="price_id" value="{{ $price_id }}">
            {{-- <input type="hidden" name="email" value="{{ $email }}"> --}}
            <input type="hidden" name="payment_method_id" id="payment_method_id">
            <input type="hidden" name="expiry_date" id="expiry_date">

            <input type="text" name="card_holder" id="card-holder" placeholder="Name on Card" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="email" name="email" value="{{ $email }}" id="email" placeholder="Email" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <div id="card-number-element" class="p-4 bg-white border rounded-md"></div>
            <div id="card-expiry-element" class="p-4 bg-white border rounded-md"></div>
            <div id="card-cvc-element" class="p-4 bg-white border rounded-md"></div>

            <input type="text" name="postal_code" id="postal_code" placeholder="ZIP/Postal Code"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="text" name="billing_address1" id="billing_address1" placeholder="Billing Address 1" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="text" name="billing_address2" id="billing_address2" placeholder="Billing Address 2"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <div class="flex gap-2">
                <input type="text" name="city" id="city" placeholder="City"
                    class="w-1/2 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

                <input type="text" name="state" id="state" placeholder="State"
                    class="w-1/2 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <input type="text" name="country" id="country" placeholder="Country"
                    class="w-1/2 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" name="zip_code" id="zip_code" placeholder="ZIP Code"
                    class="w-1/2 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" id="submit-button"
                class="w-full mt-4 bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-200">
                Continue
            </button>
        </form>
    </div>

    <script>
        const stripe = Stripe('{{ $stripeKey }}');
        const elements = stripe.elements();

        const cardNumber = elements.create('cardNumber');
        const cardExpiry = elements.create('cardExpiry');
        const cardCvc = elements.create('cardCvc');

        cardNumber.mount('#card-number-element');
        cardExpiry.mount('#card-expiry-element');
        cardCvc.mount('#card-cvc-element');

        // Capture expiry date on change
        cardExpiry.on('change', function(event) {
            if (event.complete && event.value) {
                const [month, year] = event.value.split('/');
                document.getElementById('expiry_date').value = `${month.trim()}/${year.trim()}`;
            }
        });

        const form = document.getElementById('subscription-form');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            document.getElementById('submit-button').disabled = true;

            const {
                error,
                paymentMethod
            } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardNumber,
                billing_details: {
                    name: document.getElementById('card-holder').value,
                    address: {
                        line1: document.getElementById('billing_address1').value,
                        line2: document.getElementById('billing_address2').value,
                        city: document.getElementById('city').value,
                        state: document.getElementById('state').value,
                        postal_code: document.getElementById('postal_code').value || document
                            .getElementById('zip_code').value
                    }
                }
            });

            if (error) {
                alert(error.message);
                document.getElementById('submit-button').disabled = false;
                return;
            }

            document.getElementById('payment_method_id').value = paymentMethod.id;
            form.submit();
        });
    </script>
</body>

</html>
