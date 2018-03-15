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

function stFollowAdd(add_member_srl,login_status)
{
    if(login_status == "login")
    {
        mfNowClickSrl=add_member_srl;
        userFollowAdd();
    }
    else
    {
        jQuery('.st-modal-login.modal').modal('show');
    }
}