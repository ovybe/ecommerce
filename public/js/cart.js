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