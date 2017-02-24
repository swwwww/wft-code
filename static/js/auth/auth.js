(function(){
    $(document).on(M.click_tap, '.js-login', function(){
        var url = base_url_module + 'auth/submitPhoneLogin';
        var callback_url = $('#hide_callback_url').val();

        phone_login.submit(url, function(){
            if(callback_url){
                window.location = callback_url;
            }else{
                window.location = base_url_module + 'recommend/index';
            }
        });
    })
})();