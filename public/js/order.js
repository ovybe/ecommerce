$(document).ready(function() {
    $('.q-div').on('click', 'button.q-inc', function () {
        var div = $(this).parent();
        $.ajax({
            url: '/add_quantity/' + $(this).data('index'),
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (data) {
                inputObj = div.find('input');
                //console.log(inputObj);
                inputObj.val(data);
            }
        });
    });
    $('.q-div').on('click', 'button.q-dec', function () {
        var div = $(this).parent();
        $.ajax({
            url: '/sub_quantity/' + $(this).data('index'),
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (data) {
                inputObj = div.find('input');
                //console.log(inputObj);
                inputObj.val(data);
            }
        });
    });
    $('#order_type_form').on('submit', function (event) {
        event.preventDefault();
        $('form[name="order_payment"] button').prop("disabled", true);
        var formData = new FormData(this);
        $.ajax({
            url: '/user/changeOrderType',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                $('form[name="order_payment"] button').prop("disabled", false);
                console.log("changed");
            },
            fail: function (xhr, textStatus, errorThrown) {
                console.log("failedchange");
                $('#modal-submit').prop("disabled", false);
            }
        });
    });
    if($("#updateOrder").length){
        $("#order_type_form").trigger("submit");
        $("#updateOrder").remove();
    }
});