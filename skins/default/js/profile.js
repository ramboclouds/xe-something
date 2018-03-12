jQuery(function($) {
    $('.button, .item').popup();

    $('.message .close')
    .on('click', function() {
        $(this)
        .closest('.message')
        .transition('fade')
        ;
    });

});