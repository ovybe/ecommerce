$(document).ready(function () {

    $('#usertable').DataTable();
    // $('#producttable').DataTable();
    $('#producttable').DataTable( {
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
        columnDefs: [ {
            className: 'control',
            orderable: false,
            targets:   0
        } ],
        order: [ 1, 'asc' ]
    } );
    $('#locationtable').DataTable();
});
$('#confirmModal').on('shown.bs.modal', function () {
    $('#confirmTitle').trigger('focus')
})
$('.conbtn').on('click',function(){
    const grandparentElemText=$(this).parent().parent().find('.pname').text();
    // console.log(parentElem);
    $('#confirmModal .modal-body').text("Are you sure you want to delete "+grandparentElemText+"?");
    $('#confirmModal').data('href',$(this).data('href'));
})
$('#modConfirmBtn').on('click',function(){
    location.href = $('#confirmModal').data('href');
})