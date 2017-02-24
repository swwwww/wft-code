(function(window, undefined) {
    $('.com-foot-menu').on('click', '.js-cf-main-menu', function() {
        var $me = $(this);
        $('.cf-tab-item').removeClass('active');
        $me.parent('.cf-tab-item').addClass('active');
        var subStatus = $me.attr('data-sub-status');
        var $subMenu = $me.siblings('.js-cf-sub-menu');

        var width = $me.width();
        var left = $me.position().left;

        var offset = left + width / 2 - 69;

        MoExt.hideSubMenu();

        if (subStatus == 'hide') {
            if ($subMenu.length) {
                $subMenu.css('left', offset + 'px').show();
                $me.attr('data-sub-status', 'show');
            }
        }
    });

    $('.com-foot-menu').on('click', '.js-cf-sub-menu a', function() {
        MoExt.hideSubMenu();
    });

    $('#header').on('click', '.js-slide-menu', function() {
        MoExt.hideSubMenu();
    });

    $('#header').on('click', '.back', function() {
        var $me = $(this);

        var $action = $me.children('.action');
        var action_name = $action.text();

        if (action_name == '返回') {
            if (history.length > 1) {
                history.back();
            } else {
                window.location.href = base_url;
            }
        }
    });

    MoExt = (function() {
        var hideSubMenu = function() {
            $('.js-cf-main-menu').attr('data-sub-status', 'hide');
            $('.js-cf-sub-menu').hide();
        };

        var showSlipMask = function() {
            $('body').append('<div class="popup-mask dn"></div>');
            $('.popup-mask').show();
        };

        var hideSlipMask = function() {
            $('.popup-mask').remove();
        }

        var slide = function() {
            var file_list = $('#hide_file_list').val();
            var hide_href_flag = $('#hide_href_flag').val();
            var file_arr;
            if (file_list != undefined && file_list != '') {
                file_arr = eval('(' + file_list + ')');

                if(file_arr.length > 0){
                    var file_data = [];
                    var file_prefix = base_url_cloud;
                    for ( var item in file_arr) {
                        var href = hide_href_flag === 'false' ? 'javascript:;' : '/news/' + file_arr[item]['id'];
                        var url = file_prefix + file_arr[item]['url'];

                        var file = {
                            content : '<a href="' + href + '"><img src="'  + url + '"/></a>'
                        };
                        file_data.push(file);
                    }

                    var config = {
                        type : 'dom',
                        data : file_data,
                        dom : document.querySelector('.js-com-slide-media'),
                        isLooping : true,
                        isAutoplay : true,
                        duration: 4500,
                        animateType: 'rotate',
                        useZoom : true
                    };

                    var islider = new iSlider(config);

                    islider.addDot();
                }
            }

            var notice_target = document.querySelector('.js-broadcast-wrap');
            if(notice_target){
                var notice_data = [];
                var item1 = {
                    content : '<a href="http://">！</a>'
                };
                var item2 = {
                    content : '<a href="http://">！</a>'
                };
                notice_data.push(item1);
                notice_data.push(item2);
                var config = {
                    type : 'dom',
                    data : notice_data,
                    dom : notice_target,
                    isLooping : true,
                    isAutoplay : true,
                    duration: 4500,
                    animateType: 'flip',
                    useZoom : true
                };

                var islider = new iSlider(config);
            }
        }

        return {
            hideSubMenu : hideSubMenu,
            showSlipMask : showSlipMask,
            hideSlipMask : hideSlipMask,
            slide : slide
        };

    })();

    window.MoExt = MoExt;
    MoExt.slide();
})(window, undefined);