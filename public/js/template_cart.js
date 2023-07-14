$('.cart-btn').on('click','#add-all-to-cart', function(event) {
    console.log("happen");
    // event.preventDefault();
    // console.log("delete");
    $.ajax({
        url: '/add_all_to_cart/'+$('#uid').text(),
        type: 'POST',
        processData: false,
        contentType: false,
        success: function(data) {
            console.log(data);
            $(".shoppingCart").html(data);

        }
    });
});