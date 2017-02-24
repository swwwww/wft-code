;
/**
 * @file trace_util.js
 * @auth qintao02@baidu.com || @quenteenfix
 * @date 2015-08-21
 */
(function() {
    var TU, TraceUtil = TU = TU || {};

    /**
     * trace util帮助类
     */
    TU = (function() {
        var RE_FUNCTION = /^\s*function\b[^\)]+\)/;
        var MAX_STACKTRACE_DEEP = 20;
        var ERROR_CACHE = {};
        var ga_data = [];

        var browser = function() {
            var u = navigator.userAgent, app = navigator.appVersion;
            return {// 移动终端浏览器版本信息
                trident : u.indexOf('Trident') > -1, // IE内核
                google : u.indexOf('Chrome') > -1,
                webkit : u.indexOf('AppleWebKit') > -1, // 苹果、谷歌内核
                gecko : u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, // 火狐内核
                presto : u.indexOf('Presto') > -1, // opera内核
                mobile : !!u.match(/AppleWebKit.*Mobile.*/), // 是否为移动终端
                ios : !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), // ios终端
                android : u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, // android终端或者uc浏览器
                iphone : u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, // 是否为iPhone或者QQHD浏览器
                ipad : u.indexOf('iPad') > -1, // 是否iPad
                webapp : u.indexOf('Safari') == -1,// 是否web应该程序，没有头部与底部
                version : app,
                base : {}
            };
        }();

        var browserVersion = function() {
            var ua = navigator.userAgent.toLowerCase();
            var t;
            (t = ua.match(/msie\s{1}([\d.]+)/)) ? browser.base.ie = t[1] : (t = ua.match(/chrome\/([\d.]+)/)) ? browser.base.chrome = t[1] : (t = ua.match(/firefox\/([\d.]+)/)) ? browser.base.ff = t[1] : (t = ua.match(/opera.([\d.]+)/)) ? browser.base.opera = t[1] : (t = ua.match(/version\/([\d.]+).*safari/)) ? browser.base.safari = t[1] : browser.base.mobile = "mobile";
        }();

        var getBrowser = function() {
            var result;

            if (browser.base.ie) {
                result = 'ie' + '-' + browser.base.ie;
            } else if (browser.base.chrome) {
                result = 'chrome' + '-' + browser.base.chrome;
            } else if (browser.base.ff) {
                result = 'firefox' + '-' + browser.base.ff;
            } else if (browser.base.safari) {
                result = 'safari' + '-' + browser.base.safari;
            } else if (browser.base.opera) {
                result = 'opera' + '-' + browser.base.opera;
            } else {
                result = 'mobile' + '-' + browser.version;
            }

            return result;
        };

        var getTypeOf = function(obj) {
            return Object.prototype.toString.call(obj);
        };

        var rand = function(num) {
            return ('' + Math.random()).slice(0 - num);
        };

        /**
         * 将obj转换成param的参数。
         *
         * @param {Object}
         *            obj, 参数对象。
         * @return {String} param参数。
         */
        var param = function(obj) {
            if (getTypeOf(obj) !== '[object Object]') {
                return '';
            }
            var p = [];

            for ( var k in obj) {
                if (obj.hasOwnProperty(k)) {
                    var objType = getTypeOf(obj[k]);

                    if (objType === '[object Array]' || objType === '[object Object]') {
                        try {
                            p.push(k + '=' + encodeURIComponent(JSON.stringify(obj[k])));
                        } catch (e) {
                            break;
                        }
                    } else {
                        p.push(k + '=' + encodeURIComponent(obj[k]));
                    }
                }
            }

            return p.join('&');
        };

        /**
         * 获得函数名
         *
         * @param {Function}
         *            func，函数对象。
         * @return {String} 函数名。
         */
        var getFuncName = function(func) {
            var match = String(func).match(RE_FUNCTION);

            return match ? match[0] : 'global';
        }

        /**
         * 函数调用堆栈。
         *
         * @param {Function}
         *            call, functions caller.
         * @return {String} stack trace.
         */
        var stacktrace = function(call) {
            var stack = [];
            var deep = 0;

            while (call.arguments && call.arguments.callee && call.arguments.callee.caller) {
                call = call.arguments.callee.caller;

                stack.push('at ' + getFuncName(call));

                // Because of a bug in Navigator 4.0, we need this line to
                // break.
                // c.caller will equal a rather than null when we reach the end
                // of the stack. The following line works around this.
                if (call.caller === call) {
                    break;
                }

                if (depp++ > MAX_STACKTRACE_DEEP) {
                    break;
                }
            }

            return stack.join('\n');
        };

        /**
         * JavaScript 异常统一处理函数。
         *
         * @param {String}
         *            message, 异常消息。
         * @param {String}
         *            file, 异常所在文件。
         * @param {Number}
         *            line, 异常所在行。
         * @param {Number}
         *            column, 异常所在列。
         * @return {Object} 主要用于单元测试，本身可以不返回。
         */
        var error = function(message, file, line, column, stack) {
            if (!stack && arguments.callee.caller) {
                stack = stacktrace(arguments.callee.caller);
            }

            var url = window.location.href;
            var ua = navigator.userAgent
            var browser = getBrowser();

            if (checkErrorValid(file, line, browser) === false) {
                return false;
            }

            // 不一定所有浏览器都支持col参数
            column = column || (window.event && window.event.errorCharacter) || 0;
            line = line + ',' + column;

            var data = {
                type : 'js_error',
                url : url || '',
                message : message || '',
                file : file || '',
                line : line || '',
                browser : browser,
                content : {
                    ua : ua || '',
                    stack : stack || ''
                },
                r : TU.rand(4)
            };

            var key = file + ':' + line + ':' + message;
            // 避免重复发送同样的错误信息
            if (!ERROR_CACHE.hasOwnProperty(key)) {
                ERROR_CACHE[key] = true;

                var url = base_url_module + 'trace/error';
                send(url, data);
            }

            return data;
        };

        /**
         * JavaScript 异常接口，用于监控 `try/catch` 中被捕获的异常。
         *
         * @param {Error}
         *            ex, JavaScript 异常对象。
         * @return {Object} 主要用于单元测试。
         */
        var exception = function(ex) {
            if (!(ex instanceof Error)) {
                return;
            }
            var stack = ex.stack || ex.stacktrace;
            return error(ex.message || ex.description, ex.fileName, ex.lineNumber || ex.line, ex.number, stack);
        };

        /**
         * 通过image跨域发送数据
         *
         * @param host,
         *            data, callback
         */
        var send = function(host, data, callback) {
            callback = callback || function() {
            };
            if (!host || !data) {
                return callback();
            }

            var paramStr = param(data);
            var url = host + (host.indexOf('?') < 0 ? "?" : "&") + paramStr;

            // 忽略超长 url 请求，避免资源异常。
            if (url.length > 5200) {
                return callback();
            }

            // @see http://www.javascriptkit.com/jsref/image.shtml
            var img = new Image(1, 1);
            img.onload = img.onerror = img.onabort = function() {
                callback();
                img.onload = img.onerror = img.onabort = null;
                img = null;
            };

            img.src = url;
        };

        var checkErrorValid = function(file, line, browser) {
            var flag = false;

            if (browser.indexOf('Baiduspider') > -1 || !line || line == '') {
                return flag;
            }

            if (file && file != '') {
                var except = [ 'bdimg.share.baidu.com', 'crowdtest/js/tangram-all.js' ];
                for ( var item in except) {
                    var target = except[item];

                    if (file.indexOf(target) > -1) {
                        return false;
                    }
                }

                flag = true;
            }

            return flag;
        };

        return {
            browser : browser,
            getBrowser : getBrowser,
            rand : rand,
            param : param,
            send : send,
            error : error,
            exception : exception,
            stacktrace : stacktrace,
            getFuncName : getFuncName,
            checkErrorValid : checkErrorValid
        };
    })();

    // 数据埋点
    $('html').on('click', '.ct-trace-point', function() {
        var $me = $(this);
        var traceId = parseInt($me.attr('data-ct-trace-id'), 10);
        var traceContent = $me.attr('data-ct-trace-content');

        if (traceId > 0) {
            var url = base_url_module + 'trace/record';
            var localUrl = window.location.href;
            var data = {
                ci : traceId,
                cc : traceContent || '',
                cl : localUrl,
                cr : TU.rand(6)
            };
            TU.send(url, data);
        }
    });

    // 错误监控
    window.onerror = function(message, file, line, column) {
        var data = TU.error(message, file, line, column);
        return true;
    };
})();
