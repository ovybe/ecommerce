$("#choices").on('click','a', function () {
    var choice = $(this).data('choice');

    if(choice==="blank")
        $.ajax({
            url: 'pcbuilder/generate_template',
            type: 'POST',
            data: {
                choice_type:choice,
            },
            success: function(data) {
                location.href = data['route'];

            }
        });
});
$("#fetchBuilds").on('click',function(){
    // console.log("I ran")
    var choice = $(this).data('choice');
    var income = $("#income").val();
    var buildBtn=$(this);
    buildBtn.prop( "disabled", true );

    $.ajax({
        url: 'pcbuilder/generate_template',
        type: 'POST',
        data: {
            choice_type:"budget",
            income:income,
        },
        success: function(data) {
            $("#builds-row").html(data['html']);
            window.scrollBy(0, 500);
            buildBtn.prop( "disabled", false );
            // console.log("HTML"+$("#builds-row").html());
            // console.log("happen");
        }
    });
})

$("#builds-row").on('click','a.build-btn', function () {
    var buildBtn=$(this);
    buildBtn.prop( "disabled", true );
    var choice = $(this).data('choice');
    var parts=[
        ".gpu",".cpu",".motherboard",".memory",".pccase",".ssd",".hdd"
    ];
    let parts_id_arr = []
    for(let i=0; i<parts.length; i++){
        parts_id_arr[i]=$(this).parent().find(parts[i]).data("id");
    }

    $.ajax({
        url: 'pcbuilder/generate_template',
        type: 'POST',
        data: {
            choice_type:choice,
            parts:parts_id_arr,
        },
        success: function(data) {
            buildBtn.prop( "disabled", false );
            location.href = data['route'];

        }
    });
});

