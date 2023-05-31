$(document).ready(function() {
    moment.locale($('#hl').data('locale'))
    var now = moment.tz($('#hl').data('tz'));
    // console.log(now.format('H:mm'))
    var logic = function( currentDateTime ){
        // 'this' is jquery object datetimepicker
        if( moment(currentDateTime).tz($('#hl').data('tz')).format('YYYY-MM-DD')===now.format('YYYY-MM-DD') ){
            console.log("what");
            this.setOptions({
                minTime:now.format('H:mm')
            });
        }else
            this.setOptions({
                minTime:'00:00'
            });
    };
    jQuery('#discount_expiration').datetimepicker({
        onChangeDateTime:logic,
        onShow:logic,
        format:'d.m.Y H:i',
        inline:true,
        minDate:0,
        utcOffset: now.utcOffset(),
        lang:$("#hl").data('locale'),
    });
    // TODO: Look into offset, locale
    // console.log(now.utcOffset(), now.tz('Europe/Bucharest').utcOffset());
});