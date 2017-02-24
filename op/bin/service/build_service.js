var express = require('express');
var shell = require('shelljs');
var request = require('request');
var bodyParser = require('body-parser');
var cookieParser = require('cookie-parser');
var Q = require('q');

var string_util = require('../util/string_util');
var version_dao = require('../model/version_dao');

var main = null;

exports.startService = function(port) {
    main = express();

    // app.use ： 过滤器 - 用于处理http請求的middleware（中间件），当一个请求来的时候，会依次被这些
    // middlewares处理,执行的顺序是你定义的顺序

    main.use(bodyParser.json());
    main.use(bodyParser.urlencoded({
        extended : true
    }));
    main.use(cookieParser());

    main.use('^/', function(req, res, next) {
        if (req.hostname !== 'p.wanfantian.com') {
            next();
        } else {
            req.status(403).send({
                errno : 403
            });
        }
    });

    // 指定static请求的目录
    main.use('/view', express.static(__dirname + '/../view/'));

    var build = require('../manage/build_manage');
    var build_config = require('../config/build.json');

    main.get('/build/', function(req, res) {
        res.send(build_config);
    });

    // 监听每个网站的build请求
    // for ( var i in build_config.sites) {
    build_config.sites.forEach(function(site) {
        // var site = build_config.sites[i];
        var instance = build.instance(site);

        main.get('/build/' + site.name, function(req, res) {
            instance.versionList().then(function(result) {
                res.send({
                    status : 1,
                    'output' : result
                });
            }, function(result) {
                res.send({
                    status : 0,
                    'output' : result
                });
            });
        });

        main.post('/build/' + site.name + '/build', function(req, res) {
            var post_data = req.body;
            instance.build(post_data).then(function(result) {
                var diff_arr = result.split('---+++===+++---');
                var version_id = string_util.trimTr(diff_arr[0]);
                var now_cid = string_util.trimTr(diff_arr[1]);
                var next_cid = string_util.trimTr(diff_arr[2]);
                var diff_detail = diff_arr[3];
                var diff_info = diff_arr[4];

                var create_user = post_data['author'];
                var detail = post_data['message'];

                version_dao.create({
                    version_id : version_id,
                    site_name : site.name,
                    now_cid : now_cid,
                    next_cid : next_cid,
                    diff_detail : diff_detail,
                    diff_info : diff_info,
                    create_user : create_user,
                    detail : detail,
                    created : new Date()
                }, function(err, result) {
                    if (err) {
                        console.log(err);
                    } else {
                        console.log(result);
                    }
                });

                res.send({
                    status : 1,
                    'output' : diff_arr
                });
            }, function(result) {
                res.send({
                    status : 0,
                    'output' : result
                });
            });
        });

        main.post('/build/' + site.name + '/select', function(req, res) {
            instance.selectVersion(req.body.version).then(function(result) {
                var version_id = req.body.version;
                var data = {
                    version_id : version_id,
                    site_name : site.name,
                    status : 1
                };

                version_dao.setVersionOfflineBySiteName(site.name, function() {
                    version_dao.upsert(data, function() {
                        console.log('select the right version: ' + version_id);
                    });
                });

                res.send({
                    status : 1,
                    'output' : result
                });
            }, function(result) {
                res.send({
                    status : 0,
                    'output' : result
                });
            });
        });

        main.post('/build/' + site.name + '/deploy_online', function(req, res) {
            version_dao.getNowVersionId(site.name, function(err, version_id) {
                if (!err) {
                    var option = {
                        site_name : site.name,
                        version_id : version_id,
                        deploy_type : 'online'
                    };
                    instance.deploy(option).then(function(result) {
                        res.send({
                            status : 1,
                            'output' : result
                        });
                    }, function(result) {
                        res.send({
                            status : 0,
                            'output' : result
                        });
                    });
                }
            });
        });

        main.post('/build/' + site.name + '/deploy_preview', function(req, res) {
            version_dao.getNowVersionId(site.name, function(err, version_id) {
                if (!err) {
                    var option = {
                        site_name : site.name,
                        version_id : version_id,
                        deploy_type : 'preview'
                    };
                    instance.deploy(option).then(function(result) {
                        res.send({
                            status : 1,
                            'output' : result
                        });
                    }, function(result) {
                        res.send({
                            status : 0,
                            'output' : result
                        });
                    });
                }
            });
        });
    });
    // }

    main.listen(port);
};
