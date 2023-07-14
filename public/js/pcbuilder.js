Quill.register("modules/resize",window.QuillResizeModule);
let tooltipTriggerList;
let tooltipList;
$( document ).ready(function() {
    tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    // console.log(tooltipList);
});



var quill = new Quill('#editor', {
    modules: {
        toolbar: [
            [{ header: [1, 2, false] }],
            ['bold', 'italic', 'underline'],
            ['link','image','video', 'code-block']
        ],
        resize:{
            showSize:true,
            locale:{}
        },
        imageDrop: true
    },
    placeholder: 'Compose an epic...',
    theme: 'snow'  // or 'bubble'
});
quill.root.innerHTML=$('#pc_builder_templateDescription').val();
$("#categoryList").on('click','a', function () {
    var category = $(this).data('category');
    var categoryTitle = $(this).data('bs-original-title');
    var selectedItemForCategory = $("#"+categoryTitle).find('td.prod-select-actions').data('pid');
    var templateUID = $("#uid").html();
    // console.log(selectedItemForCategory);

    if(!$(this).hasClass("active")){
        $("#categoryList").find(".active").removeClass("active");
        $(this).addClass("active");
    }
    //console.log(optionSelected);
    // $("#"+prevValue).css("display","none");
    // $("#"+valueSelected).show();
    $("#productList").html('<div class="text-center">\n' +
        '  <div class="spinner-border" role="status">\n' +
        '    <span class="visually-hidden">Loading...</span>\n' +
        '  </div>\n' +
        '</div>');
    $.ajax({
        url: '/pcbuilder/fetch_products',
        type: 'POST',
        data: {
            category_id:category,
            selected_product:selectedItemForCategory,
            template_uid:templateUID,
        },
        success: function(data) {
            console.log(data);
            $("#productList").html(data['product_table']);
            $("#filterList").html(data['filter_list']);
            // console.log(data['selected_filters']);
            $.each(data['selected_filters'], function(filterName, filterValue){
                    $.each(filterValue, function(filterArrKey,filterArrValue){
                        $("input[value="+filterName+"-"+filterArrValue+"]").prop('checked', true);
                    })
            });
            $(".filter").each(function(index){
               var filter_value=$(this).parent().find("filter_val").text()

            });

        }
    });
});
$("#productList").on('click','.prod-select', function (event) {
    event.stopPropagation();
    event.stopImmediatePropagation();
    var selectedElem=$(this);
    var selectedPid=selectedElem.data('pid');
    var selectedCat=selectedElem.data('cat');
    var templateUID=$("#uid").html();
    var specificComponentId=$("#"+selectedCat).find('td.prod-select-actions').data('pid');
    if(selectedPid!==specificComponentId){
        $.ajax({
            url: '/pcbuilder/add_component',
            type: 'POST',
            data: {
                newComponent:selectedPid,
                uid:templateUID,
            },
            success: function(data) {
                console.log(data);
                $("#"+selectedCat).html(data['html']);
                $("#priceTotal").html(data['total']+" RON");
                $("#psuAdvice").html(data['psu_advice']);
                $("#consumptionTotal").html("Consumption: "+data['consumption_total']+" W");
                $(".prod-row").find(".disabled").removeClass("disabled").html("Select");
                selectedElem.addClass("disabled").html("Selected");
                console.log(selectedElem);

                infoboxAlter(data['problems']);

                //$(this).addClass("disabled").html("Selected");

            }
        });
    }
});
var myOffcanvas = $('#offcanvasRight');
var myOffcanvasButton = $('.size-button');
myOffcanvas.on('hide.bs.offcanvas', function () {
    myOffcanvasButton.removeClass('shown');
});
myOffcanvas.on('show.bs.offcanvas', function () {
    myOffcanvasButton.addClass('shown');
});
$("#category-table").on('click','.del-btn', function (event) {
    event.stopPropagation();
    event.stopImmediatePropagation();
    var selectedElem=$(this);
    var selectedGrandParent=selectedElem.parent().parent();
    var selectedCid=selectedElem.parent().data('pid');
    var templateUID=$("#uid").html();
    // console.log(selectedCid);
    // var specificComponentId=$("#"+selectedCat).find('td.prod-select-actions').data('pid');
    $.ajax({
        url: '/pcbuilder/remove_component',
        type: 'POST',
        data: {
            component_id:selectedCid,
            uid:templateUID,
        },
        success: function(data) {
            console.log(data);
            selectedGrandParent.html(data['html']);
            $("#consumptionTotal").html("Consumption: "+data['consumption_total']+" W");
            $("#psuAdvice").html(data['psu_advice']);
            $("#priceTotal").html(data['total']+" RON");
            console.log($("#priceTotal").html());
            var disabledRow=$(".prod-row").find(".disabled");
            if(disabledRow.data('cat')==selectedGrandParent.attr('id')){
                disabledRow.removeClass("disabled").html("Select");
            }

            infoboxAlter(data['problems'])
            //$(this).addClass("disabled").html("Selected");

        }
    });
});
$("#t-update-name").on('click',function(event){
    var buttonParent=$(this).parent();
    var hiddenInput=buttonParent.find("input");
    var currentName=$("#t-name");
    var templateUID=$("#uid").html();

    if(hiddenInput.hasClass('d-none')){
        currentName.addClass('d-none');
        hiddenInput.removeClass('d-none');
    }
    else{
        hiddenInput.addClass('d-none');
        $.ajax({
            url: '/pcbuilder/rename_template',
            type: 'POST',
            data: {
                new_name:hiddenInput.val(),
                uid:templateUID,
            },
            success: function(data) {
                console.log(data);
                currentName.html(hiddenInput.val());
                currentName.removeClass('d-none');
                }
        });
    }
});
$("#t-update-desc").on('click',function(event){
    var pressedButton=$(this)
    var hiddenInput=$("#pc_builder_templateDescription")
    var newDesc=quill.root.innerHTML;
    var templateUID=$("#uid").html();

    pressedButton.addClass("disabled");

    $.ajax({
        url: '/pcbuilder/update_description',
        type: 'POST',
        data: {
            new_desc:newDesc,
            uid:templateUID,
        },
        success: function(data) {
            // console.log(data);
            if(data['ok']==0){
                // console.log("oke!");
                pressedButton.removeClass("disabled");
                hiddenInput.val(quill.root.innerHTML);
            }

        }
    });
});
var filters={};
var last_selected_product;
$('#filterList').on('change',".filter", function(event) {
    var filter_val=$(this).parent().find('.filter_val').text()
    var filter_col=$(this).data('col')
    var filter_header=$("#filterh");
    var search_val=filter_header.data('v');
    var search_fun=filter_header.data("f");
    var selected_product=$(".prod-row").find(".disabled").data('pid');
    if(selected_product===undefined){
        selected_product=last_selected_product;
    }else
        last_selected_product=selected_product;
    console.log(selected_product);
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
    $("#productList").html('<div class="text-center">\n' +
        '  <div class="spinner-border" role="status">\n' +
        '    <span class="visually-hidden">Loading...</span>\n' +
        '  </div>\n' +
        '</div>');
    //console.log(filters[filter_col]);
    console.log(filters);
    $.ajax({
        url: '/pcbuilder/apply_filter/',
        data: {filter_array:filters,
            function:search_fun,
            value:search_val,
            selected_product:selected_product},
        type: 'POST',
        success: function(data) {
            console.log(data);
            $('#productList').html(data);
        }
    });
});
$("#fetchBuilds").on('click',function(){
    // console.log("I ran")
    var uid = $("#uid").html();
    var income = $("#income").val();
    var buildBtn=$(this);
    buildBtn.prop( "disabled", true );

    $.ajax({
        url: '/pcbuilder/generate_tbob',
        type: 'POST',
        data: {
            income:income,
            uid:uid,
        },
        success: function(data) {
            console.log(data);
            $("#category-table").html(data['html']);
            $("#genOutcome").text(data['msg']);
            window.scrollBy(0, 500);
            buildBtn.prop( "disabled", false );
            // console.log("HTML"+$("#builds-row").html());
            // console.log("happen");
        }
    });
})
$("#category-table").on('click','.info-btn', function (event) {
    event.stopPropagation();
    event.stopImmediatePropagation();
    var componentRow = $(this).parent().parent();
    // var compId = componentRow.data('cat-id');
    var compName = componentRow.attr('id');
    const compNameCapitalized = compName.charAt(0).toUpperCase() + compName.slice(1);
    $("#infoModalDesc").text(quickadvice[compName]);
    $("#infoModalLabel").text(compNameCapitalized + " Info");
    // console.log(selectedCid);
    // var specificComponentId=$("#"+selectedCat).find('td.prod-select-actions').data('pid');
});
function infoboxAlter(problems){
    let infoboxHtmlStart='<span id="cat-compat-';
    let infoboxHtmlMid ='" class="text-truncate text-bg-warning p-1 border border-dark rounded" data-bs-toggle="tooltip" data-bs-title="';
    let infoboxHtmlEnd = '"><i class="bi bi-exclamation-circle"></i></span>';

    for(let i=1;i<10;i++){
        var infobox=$("#cat-compat-"+i);
        if(infobox.length){
            if(i in problems){
                $(infobox).data('bs-title',problems[i])
            }else{
                $(infobox).remove();
            }
        }else{
            if(i in problems){
                $( "#cat-name-"+i ).after( infoboxHtmlStart+[i]+infoboxHtmlMid+problems[i]+infoboxHtmlEnd );
            }
        }
    }
    tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

}


