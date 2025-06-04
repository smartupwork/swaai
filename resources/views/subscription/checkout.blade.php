<!DOCTYPE html>
<html>

<head>
    <title>Subscribe</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://js.stripe.com/v3/"></script>
</head>

<body>
    <h2>Subscribe to Plan</h2>

    @if ($errors->any())
        <div style="color:red;">
            {{ $errors->first() }}
        </div>
    @endif

    <form id="subscription-form" action="" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="plan_id" value="{{ $plan_id }}">
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="name" value="{{ $name }}">
        <input type="hidden" name="payment_method_id" id="payment_method_id">

        <label>Card Holder Name</label>
        <input type="text" name="card_holder" id="card-holder" required value="{{ $name }}">

        <div id="card-element"></div>

        <button id="submit-button">Subscribe</button>
    </form>

    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        const form = document.getElementById('subscription-form');
        const cardHolderName = document.getElementById('card-holder');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const {
                setupIntent,
                error,
                paymentMethod
            } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: cardHolderName.value,
                    email: '{{ $email }}',
                },
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
