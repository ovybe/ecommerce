var filters={};
const cat = $('#filterh').data('cat');
$('.filter').on('change', function(event) {
    //console.log();
    var filter_val=$(this).parent().find('.filter_val').text()
    var filter_col=$(this).data('col')
    if(!$(this).is(":checked")){
        if(filter_col in filters){
            if(filters[filter_col].indexOf(filter_val)>-1){
                // If it isn't checked, remove the existent item from filters
                var index;
                if(filters[filter_col].length>1){
                    index=filters[filter_col].indexOf(filter_val)
                    filters[filter_col].splice(index,1)
                    //console.log(filter_val+" removed from Filters["+filter_col+"]: ",filters)
                }else{
                    delete filters[filter_col]
                    //filters.splice(index,1);
                    console.log("Filters["+filter_col+"] removed: ",filters);
                }
            }
        }
        }else{
            if(filter_col in filters){
                if(filters[filter_col].indexOf(filter_val)===-1) {
                    filters[filter_col].push(filter_val);
                    //console.log("added " + filter_val + " in array", filters);
                }
            }
            else{
                filters[filter_col]=[filter_val]
                //console.log("created "+filter_col+" and added "+filter_val+" in array" , filters);
            }
    }
    //console.log(filters[filter_col]);
    console.log(filters);
    $.ajax({
        url: '/apply_filter/',
        data: {filter_array:filters,
               category:cat},
        type: 'POST',
        success: function(data) {
            $('#prod-list').html(data);
        }
    });
});