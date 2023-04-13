$(document).ready(function() {
    $('#modal-form').on('submit', function(event) {
        event.preventDefault();
        $('#modal-submit').prop("disabled",true);
        var formData = new FormData(this);

        $.ajax({
            url: '/user/add_contact',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                $('#modal-submit').prop("disabled",false);
                // Append the new element to the table
                $('#contact-table tbody').append(data);
                // Close the modal
                $('#myModal').modal('hide');
            },
            fail: function(xhr,textStatus, errorThrown){
                console.log(textStatus);
                $('#modal-submit').prop("disabled",false);
            }
        });
    });
    $('#confirmModal').on('shown.bs.modal', function () {
        $('#confirmTitle').trigger('focus')
    })
    $('#contact-table').on('click', 'button.conbtn',function(){
        const grandparentElemText=$(this).parent().parent().find('.pname').text();
        console.log(grandparentElemText);
        console.log($(this).data('id'));
        $('#confirmModal .modal-body').text("Are you sure you want to delete "+grandparentElemText+"?");
        $('#confirmModal').data('id',$(this).data('id'));
    })
    $('#modConfirmBtn').on('click', function(event) {
        // event.preventDefault();
        // console.log("delete");
        $.ajax({
            url: '/user/delete/contact/'+$('#confirmModal').data('id'),
            type: 'DELETE',
            processData: false,
            contentType: false,
            success: function(data) {
                console.log(data);
                $('.conbtn[data-id="' + data['id']+'"]').parent().parent().remove();
                // Append the new element to the table
               // $('#contact-table tbody').append(data);
                // Close the modal
               $('#confirmModal').modal('hide');
            }
        });
    });
});