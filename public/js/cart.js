$('.product-content').on('click','button.add-to-cart', function(event) {
    console.log("happen");
    // event.preventDefault();
    // console.log("delete");
    $.ajax({
        url: '/add_to_cart/'+$(this).data('uid'),
        type: 'POST',
        processData: false,
        contentType: false,
        success: function(data) {
            console.log(data);
            $(".shoppingCart").html(data);

        }
    });
});
$('.shoppingCart').on('click','a.cart-rbtn', function(event) {
    console.log("happen");
    // event.preventDefault();
    // console.log("delete");
    $.ajax({
        url: '/remove_from_cart/'+$(this).data('id'),
        type: 'POST',
        processData: false,
        contentType: false,
        success: function(data) {
            console.log(data);
            $(".shoppingCart").html(data['cart']);

        }
    });
});

$('#cart-clear-btn').on('click', function(event) {
    console.log("happen");
    // event.preventDefault();
    // console.log("delete");
    $.ajax({
        url: '/remove_all_from_cart/',
        type: 'POST',
        processData: false,
        contentType: false,
        success: function(data) {
            console.log(data);
            $(".shoppingCart").html(data['cart']);

        }
    });
});
