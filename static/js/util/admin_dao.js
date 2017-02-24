/**
 * @data access object *
 * @author quenteenfix@gmail.com *
 * @date 2016-06-27
 */
/* global baidu,base_url,yii_csrf_token */
(function (exports, undefined) {
    var emptyFun = function () {
    };
    var mock_flag = false;

    var admin_dao = {};
    admin_dao.business = admin_dao.business || {};

    admin_dao.business = {
        //登陆
        postLogin: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'business';
            return admin_dao.request.post(base_url_module + controller + '/postLogin', params, success);
        },
        //获取公司信息
        postSellInfo: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'business';
            return admin_dao.request.post(base_url_module + controller + '/postSellInfo', params, success);
        },
        //获取验证码
        getVerifyCode: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'business';
            return admin_dao.request.post(base_url_module + controller + '/getVerifyCode', params, success);
        },
        //找回密码
        getPassword: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'business';
            return admin_dao.request.post(base_url_module + controller + '/getPassword', params, success);
        },
        //修改密码
        postPasswordChange: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'business';
            return admin_dao.request.post(base_url_module + controller + '/postPasswordChange', params, success);
        },
        
        //修改银行卡信息
        updateBankCard: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'business';
            return admin_dao.request.post(base_url_module + controller + '/updateBankCard', params, success);
        },

        //验证验证码接口
        checkCode: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'business';
            return admin_dao.request.post(base_url_module + controller + '/checkCode', params, success);
        },

        //确认验证码接口
        confirmCode: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'business';
            return admin_dao.request.post(base_url_module + controller + '/confirmCode', params, success);
        }
    };

    admin_dao.request = {
        get: function (url, params, success, fail) {
            params = params || '';

            return $.ajax({
                type: 'GET',
                url: url,
                data: params,
                cache: false
            }).then(function (data) {
                var jsonData = data;

                if (typeof data === 'string') {
                    if (JSON && JSON.parse) {
                        jsonData = JSON.parse(data);
                    } else {
                        jsonData = eval('(' + data + ')');
                    }
                }

                success = success || emptyFun;
                success(jsonData);
            }, fail || emptyFun);
        },

        post: function (url, params, success, fail, options) {
            if (typeof params === 'string') {
                params = params || '';
                params += '&' + M.util.getUrlFromJson(M.util.getPublicParam());
            } else {
                params = params || {};
                params = M.util.lightCopy(params, M.util.getPublicParam());
            }
            var settings = {
                type: 'POST',
                url: url,
                data: params || {},
                cache: false
            };
            // 若有额外的参数，添加到settings中去，目前没有判定传入的参数的有效性，需调用者保证
            if (typeof options === 'object' && !(options instanceof Array)) {
                options = options || {};
                $.each(options, function (key, value) {
                    settings[key] = value;
                });
            }

            return $.ajax(settings).then(function (data) {
                var jsonData = data;
                if (typeof data === 'string' && data.length) {
                    if (JSON && JSON.parse) {
                        jsonData = JSON.parse(data);
                    } else {
                        jsonData = eval('(' + data + ')');
                    }
                }

                if (data === '') {
                    jsonData = {
                        status: 1
                    };
                }
                success = success || emptyFun;
                success(jsonData);
            }, fail || emptyFun);
        }
    };

    exports.ADMIN_DAO = admin_dao;
})(window, undefined);
