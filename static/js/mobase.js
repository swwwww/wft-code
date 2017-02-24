/**
 * @title a simple lib for mobile application in namespace M *
 * @notice namespace can be changed in your own ways *
 * @author quenteenfix@gmail.com *
 * @date 2014-01-09
 */
(function(exports) {
    var MOCT, M, mb = MOCT = M = M || {
        version : '1.0'
    };

    // init domain by itself right away
    M.host = function(window, document, undefined) {
        var protocol = 'http';
        var host = 'play.wanfantian.com';
        var root = '/recommend';
        var base_url = 'http://play.wanfantian.com/recommend';

        function setHost() {
            var loc = window.location;
            host = loc.host || document.domain;
            protocol = loc.protocol;
            var path_name = loc.pathname.substring(1);
            root = '/' + (path_name === '' ? '' : path_name.substring(0, path_name.indexOf('/')));
            base_url = protocol + '://' + host + root + '/';
        }

        function init() {
            setHost();
        }

        init();// init all

        return {
            protocol : protocol,
            host : host,
            root : root,
            base_url : base_url
        };
    }(window, document);

    M.screen = function() {
         function setViewport() {
             var w = window.screen.width;
             var s = w / 750;
             var m = '<meta name="viewport" content="width=750, minimum-scale=' + s + ', maximum-scale=' + s + ', user-scalable=no">';
             document.write(m);
         }

         function init() {
             setViewport();
         }

         init();
    }();

    //验证输入信息的
    M.verify= function () {
        var  config_tips = {
            msg: '提示信息',
            padding_tb: '4%',
            padding_rl: '4%',
            top: '24%',
            font_size: 28,
            time: 2500,
            z_index: 100
        }
        var name = $("#pname");
        var card_id = $("#pid");
        // 验证输入的是否是中文名
        function nameFormat(name) {
            var nameReg = /^[\u4E00-\u9FA5]+$/;
            if (name == '') {
                config_tips.msg = "您还未输入姓名";
                M.util.popup_tips(config_tips);
                setTimeout(function () {
                    $('.per-button').attr('disabled', false);
                }, 3000);
                name.focus();
                return false;
            }else if(!nameReg.test(name.val())) {
                config_tips.msg = "请输入中文名！";
                M.util.popup_tips(config_tips);
                setTimeout(function () {
                    $('.per-button').attr('disable', false);
                }, 3000);
                card_id.focus();
                return false;
            }

            return true;
        }

        function idCardNumFormat(card_id) {
            var idReg = /^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;
            if (card_id == '') {
                config_tips.msg = "您还未输入身份证号";
                M.util.popup_tips(config_tips);
                setTimeout(function () {
                    $('.per-button').attr('disabled', false);
                }, 3000);
                card_id.focus();
                return false;
            } else if (!idReg.test(card_id.val())) {
                config_tips.msg = "身份证格式不正确！";
                M.util.popup_tips(config_tips);
                setTimeout(function () {
                    $('.per-button').attr('disabled', true);
                }, 3000);
                card_id.focus();
                return false;
            }

            return true;
        }

        return {
            nameFormat : nameFormat,
            idCardNumFormat : idCardNumFormat
        };
    }();
    /**
     * tools
     */
    M.util = function() {
        function browser() {
            var u = navigator.userAgent, app = navigator.appVersion;
            return {// 移动终端浏览器版本信息
                trident : u.indexOf('Trident') > -1, // IE内核
                presto : u.indexOf('Presto') > -1, // opera内核
                webkit : u.indexOf('AppleWebKit') > -1, // 苹果、谷歌内核
                gecko : u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, // 火狐内核
                mobile : !!u.match(/AppleWebKit.*Mobile.*/), // 是否为移动终端
                ios : !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), // ios终端
                android : u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, // android终端或者uc浏览器
                iphone : u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, // 是否为iPhone或者QQHD浏览器
                ipad : u.indexOf('iPad') > -1, // 是否iPad
                webapp : u.indexOf('Safari') == -1,// 是否web应该程序，没有头部与底部
                weixin : u.indexOf('MicroMessenger') > -1, // 是否微信
                wft : u.indexOf('wft') > -1, // 是否玩翻天客户端
                google : u.indexOf('Chrome') > -1,
                version : app
            };
        }

        function popup_choice() {
            var base_config = {
                title : '玩翻天',
                notice : '玩翻天',
                button : '',
                height : 450,
                width : 600,
                top : '25%',
                is_bg_close : true,// 是否点击浮层后关闭弹出框：true-关闭 false-不关闭
                is_border : true,// 是否有边框
                to : '',
                is_close : false,
                is_bg : true
            // 是否有背景蒙层
            };
            if (typeof (arguments[0]) == 'object') {
                var config = arguments[0];
                for ( var key in config) {
                    base_config[key] = config[key];
                }
            }
            var popup_arr = [];
            popup_arr.push('<div class="popup-choice">');
            popup_arr.push('<div class="popup">');

            popup_arr.push('<div class="popup-title">');
            popup_arr.push('<div id="popup_title" class="title-content"></div>');
            // popup_arr.push('<a class="popup-close"
            // href="javascript:;"></a>');
            popup_arr.push('</div>');
            popup_arr.push('<div class="popup-content">');
            popup_arr.push('<div class="popup-notice" id="popup_notice"></div>');
            popup_arr.push('</div>');
            popup_arr.push('<div class="popup-btn">');
            popup_arr.push('<a class="left-btn dib fl" href="javascript:;">否</a>');
            popup_arr.push('<a class="right-btn dib fr" href="javascript:;">是</a>');
            popup_arr.push('</div>');

            popup_arr.push('</div>');
            popup_arr.push('</div>');
            var popup_html = popup_arr.join('');
            $('body').append(popup_html);
            $('body').append('<div class="popup-mask"></div>');
            $('#popup_title').html(base_config['title']);
            $('#popup_notice').html(base_config['notice']);
            $('#popup_button').html(base_config['button']);

            var top = base_config['top'];
            var height = base_config['height'];
            var width = base_config['width'];
            // var margin_left = 0 - (width / 2) - 5;
            $('.popup').css({
                'position' : 'fixed',
                'backgroundColor' : '#ffffff',
                'height' : height + 10,
                'width' : width + 10,
                'margin-left' : 0,
                'top' : top,
                '-webkit-border-radius' : 0,
                'border-radius' : 0,
                'z-index' : 2
            });

            if (base_config['is_bg_close']) {
                $('.popup-mask').on(M.click_touchend, function() {
                    hidePopup();
                });
            }
            if (!base_config['is_bg']) {
                $('.popup-mask').remove();
            }
            if (base_config['is_border']) {
                $('.popup').css('border', '3px solid #dbdbdb');
            }

            if (base_config['is_border']) {
                hidePopup();
            }

            $('.popup .close').on(M.click_touchend, function(event) {
                hidePopup();
            });
            $('.popup-wrapper').show();
            // $('.popup-mask').animate({
            // opacity : 0.6
            // }, 0);
        }

        function popup_notice() {
            var base_config = {
                title : '',
                notice : '',
                button : '',
                height : 450,
                width : 600,
                top : '25%',
                is_bg_close : false,// 是否点击浮层后关闭弹出框：true-关闭 false-不关闭
                is_border : false,// 是否有边框
                to : ''
            };
            if (typeof (arguments[0]) == 'object') {
                var config = arguments[0];
                for ( var key in config) {
                    base_config[key] = config[key];
                }
            }
            var popup_arr = [];
            popup_arr.push('<div class="popup-wrapper">');
            popup_arr.push('<div class="popup">');
            popup_arr.push('<div class="popup-title">');
            popup_arr.push('<div id="popup_title" class="title-content"></div>');
            popup_arr.push('<a class="close" href="javascript:;">关闭');
            // popup_arr.push('<i class="close-line"></i>');
            popup_arr.push('</a>');
            popup_arr.push('</div>');
            popup_arr.push('<div class="popup-content">');
            popup_arr.push('<pre class="popup-notice" id="popup_notice"></pre>');
            popup_arr.push('<i class="bottom-line"></i>');
            popup_arr.push('</div>');
            popup_arr.push('</div>');
            popup_arr.push('</div>');
            var popup_html = popup_arr.join('');
            $('body').append(popup_html);
            $('body').append('<div class="popup-mask"></div>');
            $('#popup_title').html(base_config['title']);
            $('#popup_notice').html(base_config['notice']);
            $('#popup_button').html(base_config['button']);

            var top = base_config['top'];
            var height = base_config['height'];
            var width = base_config['width'];
            // var margin_left = 0 - (width / 2) - 5;
            $('.popup').css({
                'position' : 'fixed',
                'backgroundColor' : '#ffffff',
                'height' : height + 10,
                'width' : width + 10,
                'margin-left' : 0,
                'top' : top,
                '-webkit-border-radius' : 10,
                'border-radius' : 10,
                'z-index' : 9999
            });

            if (base_config['is_bg_close']) {
                $('.popup-mask').on(M.click_touchend, function() {
                    hidePopup();
                });
            }

            if (base_config['is_border']) {
                $('.popup').css('border', '3px solid #dbdbdb');
            }

            $('.popup .close').on(M.click_touchend, function(event) {
                hidePopup();
            });
            $('.popup-wrapper').show();
            // $('.popup-mask').animate({
            // opacity : 0.6
            // }, 0);
        }

        function popup_tips() {
            var base_config = {
                msg : '提示信息',
                padding_tb : '10',
                padding_rl : '10',
                top : '25%',
                time : 3000,
                font_size : 24,
                is_reload : false,
                is_matte : false,
                is_bg_close : false,
                z_index : 1
            };
            if (typeof (arguments[0]) == 'object') {
                var config = arguments[0];
                for ( var key in config) {
                    base_config[key] = config[key];
                }
            }
            var popup_arr = [];
            popup_arr.push('<div id="popup-msg"></div>');
            $('body').append(popup_arr);
            $('body').append('<div class="popup-mask"></div>');
            $('#popup-msg').html(base_config['msg']);

            var top = base_config['top'];
            var ptb = base_config['padding_tb'];
            var prl = base_config['padding_rl'];
            var time = base_config['time'];
            var font_size = base_config['font_size'];
            var z_index = base_config['z_index'];

            $('#popup-msg').css({
                'position' : 'fixed',
                'color' : '#ffffff',
                'font-size' : font_size,
                'text-align' : 'center',
                'backgroundColor' : 'rgba(0, 0, 0, 0.5)',
                'left' : '50%',
                'padding-top' : ptb,
                'padding-bottom' : ptb,
                'padding-left' : prl,
                'padding-right' : prl,
                'top' : top,
                'box-sizing' : 'border-box',
                'transform' : 'translate(-50%, -40%)',
                '-webkit-transform' : 'translate(-50%, -40%)',
                '-webkit-border-radius' : 15,
                'border-radius' : 15,
                'z-index' : z_index
            });

            if (!base_config['is_reload']) {
                setTimeout(function() {
                    $('#popup-msg').remove();
                    $('.popup-mask').remove();
                }, time);
            } else {
                // 刷新浏览器并关闭
                setTimeout(function() {
                    $('#popup-msg').remove();
                    $('.popup-mask').remove();
                    window.location.reload();
                }, time);
            }

            if (!base_config['is_matte']) {
                $('.popup-mask').remove();
            }

            if (base_config['is_bg_close']) {
                $('.popup-mask').on(M.click_touchend, function() {
                    hidePopup();
                });
            }
        }

        function hidePopup() {
            $('.popup-wrapper').remove();
            $('.popup-mask').remove();
            $('.popup-choice').remove();
            $('.popup').remove();
            $('.popup-msg').remove();
        }

        function getElementTop(element) {
            var actualTop = element.offsetTop;
            var current = element.offsetParent;
            while (current !== null) {
                actualTop += current.offsetTop;
                current = current.offsetParent;
            }

            return actualTop;
        }

        function getScrollTop() {
            var scroll_top = 0;
            if (document.documentElement && document.documentElement.scrollTop) {
                scroll_top = document.documentElement.scrollTop;
            } else if (document.body) {
                scroll_top = document.body.scrollTop;
            }
            return scroll_top;
        }

        function autoShowTop() {
            $(window).scroll(function() {
                var scroll_top = getScrollTop();
                if (scroll_top > 1000) {
                    $('.green-top').css('display', 'block');
                } else {
                    $('.green-top').css('display', 'none');
                }
            });
        }

        function showYoukuVideo(dom_id, v_id, autoplay) {
            var player = new YKU.Player(dom_id, {
                client_id : '58eecd0cb72120d0',
                vid : v_id,
                autoplay : autoplay,
                show_related : false
            });
        }

        // 异步执行队列，可用于异步执行，并实时输出结果：M.util.ayncQueue.add(function(){}).add(500).run();
        var ayncQueue = function() {
            var queue = [];

            function add(target) {
                if (!/function|number/.test(typeof target)) {
                    return;
                }
                queue.push(target);
                return this;
            }

            // 需要注释代码 - todo
            function run() {
                console.log('start');
                var source = queue.shift();
                if (!source) {
                    return;
                }
                if (typeof source == 'function') {
                    source();
                    run();
                } else {
                    console.log('aync here');
                    setTimeout(function() {
                        console.log('aync exe');
                        run();
                    }, source);
                    console.log('aync over');
                }
            }

            return {
                add : add,
                run : run
            };
        }();

        var tab_handlers = {
            onTabItemHover : function(e) {
                tab_handlers.fireTabEvent(e);
            },
            onTabItemClick : function(e) {
                tab_handlers.fireTabEvent(e);
            },
            fireTabEvent : function(e) {
                var target = e.target;

                if ($(target).hasClass('ui-tab-item')) {
                    var tab_word_id = $(target).attr('data-word');
                    var $target_block = $('.ui-tab-block[data-word="' + tab_word_id + '"]');

                    if (tab_word_id.length) {
                        $target_block.siblings('.ui-tab-block').hide();
                        $target_block.show();

                        $(target).siblings('.ui-tab-item-active').removeClass('ui-tab-item-active');
                        $(target).addClass('ui-tab-item-active');
                    }
                }
            }
        };

        var freshVerifyImg = function() {
            $(this).attr('src', $(this).attr('src').replace(/\?x=.*/, '') + '?x=' + Math.random());
        };

        var shortcut = function(event, target_obj, callback) {
            callback = callback || function() {
            };
            event = event || window.event;

            var key = event.keyCode || event.which || event.charCode;

            var quick_flag = false;
            for ( var i in target_obj) {
                var click_key = parseInt(target_obj[i], 10);
                if (key === click_key) {
                    quick_flag = true;
                    break;
                }
            }
            if (quick_flag) {
                callback();
            }
        };

        var checkLogin = function($target, url) {
            var login = $target.attr('data-login');
            if (login == 'no') {
                if (M.browser.wft) {
                    if (M.browser.ios) {
                        document.location.href = 'user_login$$';
                    } else {
                        window.getdata.user_login();
                    }
                } else {
                    var wft_param = $('#hide_wft_param').val();
                    var redirect_url = url == '' ? base_url_module + 'lottery/login' : url;
                    if (wft_param) {
                        if (redirect_url.indexOf('?') > -1) {
                            redirect_url += '&p=' + wft_param;
                        } else {
                            redirect_url += '?p=' + wft_param;
                        }
                    }
                    window.location = redirect_url;
                }
                return false;
            }

            return true;
        }

        var getPublicParam = function() {
            var param = {};
            param.YII_CSRF_TOKEN = yii_csrf_token;
            param.x_a = 1;// ajax flag
            return param;
        };

        var getUrlFromJson = function(source) {
            if (typeof (source) === 'undefined' || typeof (source) === false) {
                return '';
            }

            var tmps = [];
            for ( var key in source) {
                tmps.push(key + '=' + source[key]);
            }

            return tmps.join('&');
        };

        // 浅度对象拷贝
        var lightCopy = function(target, source) {
            for ( var key in source) {
                if (source.hasOwnProperty(key)) {
                    target[key] = source[key];
                }
            }

            return target;
        };

        return {
            browser : browser,
            popup_tips : popup_tips,
            popup_notice : popup_notice,
            popup_choice : popup_choice,
            hidePopup : hidePopup,
            autoShowTop : autoShowTop,
            showYoukuVideo : showYoukuVideo,
            ayncQueue : ayncQueue,
            tab_handlers : tab_handlers,
            freshVerifyImg : freshVerifyImg,
            shortcut : shortcut,
            getScrollTop : getScrollTop,
            getElementTop : getElementTop,
            checkLogin : checkLogin,
            getPublicParam : getPublicParam,
            getUrlFromJson : getUrlFromJson,
            lightCopy : lightCopy
        };
    }();

    M.location = function() {
        var city_map = {
            'wuhan' : 'wuhan',
            'nanjing' : 'nanjing',
            '武汉' : 'wuhan',
            '武汉市' : 'wuhan',
            '南京' : 'nanjing',
            '南京市' : 'nanjing'
        };

        var cn_city_map = {
            'wuhan' : '武汉',
            '武汉' : '武汉',
            '武汉市' : '武汉',
            'nanjing' : '南京',
            '南京' : '南京',
            '南京市' : '南京'
        };

        function getCityForCn(name) {
            var result = cn_city_map[name];
            result = (result == '' || result == undefined) ? '武汉' : result;

            return result;
        }

        function getCityForEn(name) {
            var result = city_map[name];
            result = (result == '' || result == undefined) ? 'wuhan' : result;

            return result;
        }

        // 自动设置地理位置cookie信息：1、map_data始终为用户当前的地理位置信息 | 2、人工选择城市的优先级高于自动定位的优先级
        function autoSetMapData(callback) {
            try {
                var bm_city = new BMap.LocalCity();

                bm_city.get(function(res) {
                    var city = getCityForEn(res.name);
                    var data = [ city, res.center.lat, res.center.lng ];

                    // map_data始终为用户当前的地理位置信息
                    M.cookie.set('map_data', data.join('|'));

                    if (typeof (callback) == 'function') {
                        callback(data);
                    }

                    // 人工选择的优先级高于自动定位的优先级
                    var choose_flag = M.cookie.get('choose_flag');
                    if (choose_flag != 'yes') {
                        M.cookie.set('custom_city', city);
                    }
                });
            } catch (e) {
            }
        }

        function getMapData() {
            var map_data = M.cookie.get('map_data');
            var map_arr = map_data.split('|');

            var result = {
                'city' : map_arr[0],
                'lat' : map_arr[1],
                'lng' : map_arr[2]
            };

            return result;
        }

        function setCustomCity(custom_city) {
            M.cookie.set('choose_flag', 'yes', 3);

            custom_city = getCityForEn(custom_city);
            M.cookie.set('custom_city', custom_city);
        }

        function getCustomCity() {
            var custom_city = M.cookie.get('custom_city');
            custom_city = getCityForEn(custom_city);

            return custom_city;
        }

        return {
            autoSetMapData : autoSetMapData,
            getMapData : getMapData,
            getCityForCn : getCityForCn,
            getCityForEn : getCityForEn,
            setCustomCity : setCustomCity,
            getCustomCity : getCustomCity
        };
    }();

    M.app = function() {
        var config = {
            ios : '',
            android : '',
            download : '',
            timeout : 2 * 60 * 1000
        };

        function tryToStartup() {
            var schema_url = M.browser.ios ? config.ios : config.android;
            /*
             * var frame = document.createElement('iframe'); frame.src =
             * schema_url; frame.style.display = 'none';
             * document.body.appendChild(frame);
             */
            window.location = schema_url;
        }

        function tryToDownload() {
            var timer = setTimeout(function() {
                window.location = config.download;
            }, config.timeout);
        }

        function open(init_config) {
            config = init_config;
            tryToStartup();
            tryToDownload();
        }

        return {
            open : open
        };
    }();

    M.lazyload = function() {
        function belowTop(target) {
            var top = $(window).scrollTop() - 120;
            var position_y = $(target).offset().top;
            return top < position_y;
        }

        function aboveBottom(target) {
            var $w = $(window);
            var bottom = $w.height() + $w.scrollTop() + 168;// 预加载buffer
            var position_y = $(target).offset().top;

            return bottom > position_y;
        }

        function inViewport(target) {
            return belowTop(target) && aboveBottom(target);
        }

        function loadImage(target) {
            if (target.length) {
                var img = target;
                var src = img.attr('data-src');

                if (src != '' && src != null && src != undefined) {
                    if (!img.hasClass('lazyload-fixed')) {
                        img.attr('src', src).addClass('lazyload-fixed').removeAttr('data-src');
                    } else {
                        // img.attr('src', src);
                    }
                }
            }
        }

        function showAllImageInViewport() {
            var all_image = $('.lazyload');

            all_image.each(function(i) {
                var img = all_image.eq(i);

                if (inViewport(img)) {
                    loadImage(img);
                }
            });
        }

        var timer = null;
        function init() {
            $(window).on('scroll', function() {
                if (M.lazyload.timer) {
                    clearTimeout(M.lazyload.timer);
                    M.lazyload.timer = null;
                }

                M.lazyload.timer = setTimeout(function() {
                    showAllImageInViewport();
                }, 98);
            });

            showAllImageInViewport();
        }

        return {
            timer : timer,
            init : init
        };
    }();

    M.time = function() {
        // 时间戳转换成“月-天 时：分”
        function month_to_minute(time) {
            var date = new Date(parseInt(time) * 1000);
            var Y = date.getFullYear() + '-';
            var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
            var D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
            var H = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
            var m = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes() + '');
            return M + D + H + m;
        }
        // 时间戳转换成“年-月-日”
        function year_to_date(time) {
            var date = new Date(parseInt(time) * 1000);
            var Y = date.getFullYear() + '-';
            var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
            var D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '';
            return Y + M + D;
        }

        // 时间戳转换成“年-月”
        function year_to_month(time) {
            var date = new Date(parseInt(time) * 1000);
            var Y = date.getFullYear() + '-';
            var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '';
            return Y + M;
        }

        //格式化时间"年-月-日"转化为时间戳
        function format_to_stamp(time) {
            var date_arr = time.split('-');
            var time_temp = new Date(Date.UTC(date_arr[0], date_arr[1]-1, date_arr[2]));
            var timestamp = time_temp.getTime()/1000;//格式时间转换为时间戳
            return timestamp;
        }

        return {
            month_to_minute : month_to_minute,
            year_to_date : year_to_date,
            year_to_month: year_to_month,
            format_to_stamp: format_to_stamp
        }
    }();

    M.load = function() {
        // load状态显示
        function loadTip(type, msg, state) {
            var tip_str, time = 800;
            $('.loader').remove();

            // 提示框状态判断 是否消失
            if (state == 'back') {
                return false
            }

            // 加载提示
            if (type == 'loadType') {
                tip_str = '<div class="loader">' + '<div class="loader-inner line-spin-fade-loader">' + '<div></div>' + '<div></div>' + '<div></div>' + '<div></div>' + '<div></div>' + '<div></div>' + '<div></div>' + '<div></div>' + '</div>' + '<span class="loader-text">' + msg + '</span>' + '</div>';
            }

            // 错误提示
            if (type == 'errorType') {
                tip_str = '<div class="loader">' + '<img class="error_cross" src="/static/img/site/mobile/cross.png"/>' + '<span class="loader-text">' + msg + '</span>' + '</div>';

            }

            // 完成提示
            if (type == 'doType') {
                tip_str = '<div class="loader">' + '<img class="do_tick" src="/static/img/site/mobile/check.png"/>' + '<span class="loader-text">' + msg + '</span>' + '</div>';
            }

            $('body').append(tip_str);

            // 提示框延迟消失
            if (state == 'delay') {
                setTimeout(function() {
                    $('.loader').remove();
                }, time)
            }
        }

        return {
            loadTip : loadTip
        }
    }();

    M.cookie = function() {
        // 设置cookie
        function set(cname, cvalue, days) {
            var d = new Date();
            if (days === undefined) {
                days = 15;
            }
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            // day
            var expires = "expires=" + d.toUTCString();
            var debug = get('YII_DEBUG');
            cvalue = encodeURIComponent(cvalue);
            if (debug == 1) {
                document.cookie = cname + "=" + cvalue + "; " + expires + '; path=/';
            } else {
                document.cookie = cname + "=" + cvalue + "; domain=.wanfantian.com; " + expires + '; path=/';
            }
        }

        // 获取cookie
        function get(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for ( var i = 0, len = ca.length; i < len; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) != -1) {
                    return decodeURIComponent(c.substring(name.length, c.length));
                }
            }

            return '';
        }

        return {
            set : set,
            get : get
        };
    }();

    M.browser = {};
    M.base = {};
    M.search = (function() {
        $(document).on('click', '.site-search-btn', function() {
            search();
        });

        $(document).on('keyup', '.site-search-text', function(event) {
            var event = event || window.event;
            var code = event.keyCode || event.which;
            if (code == 13) {
                search();
            }
        });

        function search() {
            var query = $.trim($('.site-search-text').val());
            if (query == '') {
                alert('请先输入搜索的关键词');
            } else {
                var search_url = '/search/' + window.encodeURIComponent(query);
                window.location = search_url;
            }
        }
    })();
    /**
     * Initialization Entrace define a function and execute it, so the function
     * will be exist and other object will be init.
     */
    M.base.start = function() {
        M.browser = M.util.browser();
        if (M.browser.mobile) {
            M.click_tap = 'tap';
            M.click_touchend = 'touchend';
        } else {
            M.click_tap = 'click';
            M.click_touchend = 'click';
        }

        // 设置城市cookie
        M.location.autoSetMapData(function(data) {
        });
        M.lazyload.init();
        M.util.autoShowTop();
    }();

    // you can change the global invoked var here
    exports.M = M;
})(window);

