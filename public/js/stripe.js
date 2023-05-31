var stripe = Stripe("{{stripe_key}}");
var elements = stripe.elements();
var cardElement = elements.create('card');
cardElement.mount('#card-element');

function createToken() {
    document.getElementById("pay-btn").disabled = true;
    stripe.createToken(cardElement).then(function(result) {


        if(typeof result.error != 'undefined') {
            document.getElementById("pay-btn").disabled = false;
            alert(result.error.message);
        }

        // creating token success
        if(typeof result.token != 'undefined') {
            document.getElementById("stripe-token-id").value = result.token.id;
            document.getElementById('checkout-form').submit();
        }
    });
}