$(document).ready(function() {
$('#order_country').on('change',function(){
    console.log("wait??");
    var phone=$('#order_country option:selected').data('phone');
    if(phone[0]!=='+'){
        phone='+'+phone;
    }
    console.log(phone);
    $('#prefixPhone').html(phone);
})
// $('form [name="order"]').on('submit',function(event){
//     event.preventDefault();
//     var orderPhone=$('#order_phone');
//     // orderPhone.val($('#prefixPhone')+orderPhone.val());
// })
    $('#order_country').trigger('change');
});