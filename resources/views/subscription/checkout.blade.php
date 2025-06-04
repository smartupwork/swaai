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

        <form id="subscription-form" action="{{ route('subscribe.process') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="plan_id" value="{{ $plan_id }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="payment_method_id" id="payment_method_id">

            <input type="text" name="card_holder" id="card-holder" placeholder="NAME" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <div id="card-element" class="p-4 bg-white border rounded-md"></div>

            <input type="text" name="postal_code" id="postal_code" placeholder="ZIP/Postal Code" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="text" name="address_line1" id="address_line1" placeholder="Billing Address" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="text" name="address_line2" id="address_line2" placeholder="Billing Address 2"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <div class="flex gap-2">
                <input type="text" name="city" id="city" placeholder="City" required
                    class="w-1/2 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" name="state" id="state" placeholder="State" required
                    class="w-1/2 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <input type="text" name="country" id="country" placeholder="Country (e.g. US)" required maxlength="2"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit" id="submit-button"
                class="w-full mt-4 bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-200">
                Continue
            </button>
        </form>
    </div>

    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');
        const elements = stripe.elements();
        const card = elements.create('card', {
            hidePostalCode: true
        });
        card.mount('#card-element');

        const form = document.getElementById('subscription-form');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const billingDetails = {
                name: document.getElementById('card-holder').value,
                email: '{{ $email }}',
                address: {
                    line1: document.getElementById('address_line1').value,
                    line2: document.getElementById('address_line2').value,
                    city: document.getElementById('city').value,
                    state: document.getElementById('state').value,
                    postal_code: document.getElementById('postal_code').value,
                    country: document.getElementById('country').value,
                }
            };

            const {
                error,
                paymentMethod
            } = await stripe.createPaymentMethod({
                type: 'card',
                card: card,
                billing_details: billingDetails,
            });

            if (error) {
                alert(error.message);
            } else {
                document.getElementById('payment_method_id').value = paymentMethod.id;
                form.submit();
            }
        });
    </script>
</body>

</html>
