var prevValue;
$(document).ready(function() {
    // you may need to change this code if you are not using Bootstrap Datepicker

    var typeSelect=$("select#add_product_type");
    var selectedValue=$("option:selected",typeSelect).value;
    if(!selectedValue==null && $("#formdiv").find("#"+selectedValue)!==undefined)
    {
        // TODO: form shouldn't have extra fields
        $(typeSelect).trigger("change");
    }
    $('.js-datepicker').datepicker({
        format: 'yyyy-mm-dd'
    });
});

$("select#add_product_type").on('change', function () {
    var optionSelected = $("option:selected", this);
    //console.log(optionSelected);
    var valueSelected = this.value;
    var formType= $("#container").data('type')
    // $("#"+prevValue).css("display","none");
    // $("#"+valueSelected).show();
    $.ajax({
        url: '/admin/render_subform',
        type: 'POST',
        data: {
            type:valueSelected,
            add_or_edit:formType,
        },
        success: function(data) {
            console.log(data);
            $("#formdiv").html(data);

        }
    });
    prevValue=this.value;
});
$("span#addloc").on('click', function () {
    var optionSelected = $("select#locations").find(":selected");
    var valueSelected = optionSelected.val();
    var lastRow = (".location-box").lastElementChild;
    var newRow = lastRow.cloneNode(true);

    newRow.dataset.index = valueSelected;
    newRow.innerHTML = newRow.innerHTML.replace(/__name__/g, valueSelected);

    $(".location-box").append(newRow);
});


Quill.register("modules/resize",window.QuillResizeModule);

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

var quillShort = new Quill('#editor2', {
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

$( "form" ).submit(function( event ) {
    // event.preventDefault();
    $("#add_product_product_description").val(quill.root.innerHTML);
    $("#add_product_product_shortDesc").val(quillShort.root.innerHTML);
    console.log($("#add_product_product_description").val());
    // return
});

const addFormToCollection = (e) => {
    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

    const item = document.createElement('tr');

    item.innerHTML = collectionHolder
        .dataset
        .prototype
        .replace(
            /__name__/g,
            collectionHolder.dataset.index
        );

    collectionHolder.getElementsByTagName('tbody')[0].appendChild(item);

    collectionHolder.dataset.index++;
};


$(".add_item_link").on('click',addFormToCollection);