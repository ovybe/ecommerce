$(document).ready(function() {
    // $('#contact-table').DataTable();
    $('#template-table').DataTable();
    // $('#modal-form').on('submit', function(event) {
    //     event.preventDefault();
    //     $('#modal-submit').prop("disabled",true);
    //     var formData = new FormData(this);
    //
    //     $.ajax({
    //         url: '/user/add_contact',
    //         type: 'POST',
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         success: function(data) {
    //             $('#modal-submit').prop("disabled",false);
    //             // Append the new element to the table
    //             // $('#contact-table tbody').append(data);
    //             // $('#payment-table').DataTable().row.add($(data)).draw();
    //             // Close the modal
    //             $('#myModal').modal('hide');
    //         },
    //         fail: function(xhr,textStatus, errorThrown){
    //             console.log(textStatus);
    //             $('#modal-submit').prop("disabled",false);
    //         }
    //     });
    // });
    $('#confirmModal').on('shown.bs.modal', function () {
        $('#confirmTitle').trigger('focus')
    })
    // $('#contact-table').on('click', 'button.conbtn',function(){
    //     const grandparentElemText=$(this).parent().parent().find('.pname').text();
    //     console.log(grandparentElemText);
    //     console.log($(this).data('id'));
    //     $('#confirmModal .modal-body').text("Are you sure you want to delete "+grandparentElemText+"?");
    //     $('#confirmModal').data('id',$(this).data('id'));
    // });
    $('#template-table').on('click', 'button.conbtnp',function(){
        const grandparentElemText=$(this).parent().parent().find('.pname').text();
        // console.log(grandparentElemText);
        // console.log($(this).data('id'));
        $('#confirmModalTemplate .modal-body').text("Are you sure you want to delete "+grandparentElemText+"?");
        $('#confirmModalTemplate').data('id',$(this).data('id'));
    });
    // $('#modConfirmBtn').on('click', function(event) {
    //     // event.preventDefault();
    //     // console.log("delete");
    //     $.ajax({
    //         url: '/user/delete/contact/'+$('#confirmModal').data('id'),
    //         type: 'DELETE',
    //         processData: false,
    //         contentType: false,
    //         success: function(data) {
    //             console.log(data);
    //             $('.conbtn[data-id="' + data['id']+'"]').parent().parent();
    //             $('#contact-table').DataTable().row(deletedRow).remove().draw();
    //             // Append the new element to the table
    //            // $('#contact-table tbody').append(data);
    //             // Close the modal
    //            $('#confirmModal').modal('hide');
    //         }
    //     });
    // });
    $('#modConfirmBtnTemplate').on('click', function(event) {
        // event.preventDefault();
        // console.log("delete");
        $.ajax({
            url: '/user/delete/template/'+$('#confirmModalTemplate').data('id'),
            type: 'DELETE',
            processData: false,
            contentType: false,
            success: function(data) {
                console.log(data);
                deletedRow=$('.conbtnp[data-id="' + data['id']+'"]').parent().parent();
                $('#template-table').DataTable().row(deletedRow).remove().draw();
                // Append the new element to the table
                // $('#contact-table tbody').append(data);
                // Close the modal
                $('#confirmModalTemplate').modal('hide');
            }
        });
    });
    $('#verifMsg').on('click','#resend', function(event) {
        verifMsg=$('#verifMsg');
        $('#resend').attr("disabled","disabled");
        $.ajax({
            url: '/user/resend',
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data) {
                verifMsg.html('The verification email for '+verifMsg.data('email')+' has been sent again! Check your spam if you cannot find it, or click <a href="javascript:void(0)" class="link-primary" id="resend">here</a> to send it again!')
                $('#resend').attr("enabled","enabled");
            }
        });
    });
});