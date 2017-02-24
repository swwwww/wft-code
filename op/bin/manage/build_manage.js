var shelljs = require('shelljs');
var Q = require('q');

var version_dao = require('../model/version_dao');

exports.instance = function(option) {
    var instance = {};

    // 版本历史记录
    instance.versionList = function() {
        var defer = Q.defer();

        shelljs.exec(option.shell.version_list, function(code, output) {
            if (code === 0) {
                // 找出所有的版本号
                var version_list = output.split('\n').filter(function(item) {
                    return (item && /[0-9]+/.test(item));
                }).map(function(item) {
                    return parseInt(item, 10);
                });

                version_list.sort(function(a, b) {
                    return b - a;
                });

                var result = {};
                // 展示的版本数量
                var k = 10;
                var promises = [];

                var now_version = -1;

                version_list.filter(function(item) {
                    k -= 1;
                    if (k < 0) {
                        return false;
                    }

                    var defer_log = Q.defer();

                    result[item] = {
                        version : item
                    };

                    promises.push(defer_log.promise);

                    version_dao.findById(item, option.name, function(err, response) {
                        if (!err) {
                            result[item].log = response;
                            if (response && response.status == 1) {
                                now_version = item;
                            }
                            defer_log.resolve();
                        } else {
                            console.log(err);
                            defer_log.reject();
                        }
                    });

                    return true;
                });

                var defer_enable_version = Q.defer();

                /*
                 * promises.push(defer_enable_version.promise);
                 *
                 * var now_version = -1; shelljs.exec(option.shell.now_version,
                 * function(code, output) { if (code === 0) { now_version =
                 * parseInt(output, 10); defer_enable_version.resolve(); } else {
                 * defer_enable_version.reject(); } });
                 */

                Q.all(promises).then(function() {
                    defer.resolve({
                        versions : result,
                        now_version : now_version
                    });
                }, function() {
                    defer.reject();
                });
            } else {
                defer.reject(output);
            }
        });

        return defer.promise;
    };

    instance.build = function(build_option) {
        var defer = Q.defer();

        var build_msg_arr = [];
        var build_msg = '';
        build_msg_arr.push('Auth: ' + build_option.author);
        build_msg_arr.push(build_option.message);

        build_msg = build_msg_arr.join(' ');
        build_msg = build_msg.replace('"', '\\"');

        var opt_str = '';
        for ( var key in build_option) {
            if ((key !== 'author') && (key !== 'message')) {
                opt_str += ' --' + key + ' "' + build_option[key] + '"';
            }
        }

        var cmd = option.shell.build + ' "' + build_msg + '"' + opt_str;
        shelljs.exec(cmd, function(code, output) {
            if (code === 0) {
                defer.resolve(output);
            } else {
                defer.reject(output);
            }
        });

        return defer.promise;
    };

    instance.deploy = function(build_option) {
        var defer = Q.defer();

        var opt_str = '';
        for ( var key in build_option) {
            opt_str += ' --' + key + ' "' + build_option[key] + '"';
        }

        var cmd = option.shell.deploy + opt_str;
        shelljs.exec(cmd, function(code, output) {
            if (code === 0) {
                defer.resolve(output);
            } else {
                defer.reject(output);
            }
        });

        return defer.promise;
    };

    instance.selectVersion = function(version) {
        var defer = Q.defer();
        var code = 0;
        if (code === 0) {
            defer.resolve(1);
        } else {
            defer.reject(0);
        }

        return defer.promise;
    };

    return instance;
};