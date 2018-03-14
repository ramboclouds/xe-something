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

function stFollowAdd(add_member_srl)
{
    mfNowClickSrl=add_member_srl;
    userFollowAdd();
}