var config_file = process.argv[2];
if (!config_file) {
    config_file = "../config/config.json";
}
GLOBAL.CONFIG = require(config_file);

var string_util = require('../util/string_util');
var version_dao = require('../model/version_dao');

var diff_arr = [ 0, 1, 2, 3, 4, 5, 6 ];

var version_id = string_util.trimTr(diff_arr[0]);
var now_cid = string_util.trimTr(diff_arr[1]);
var next_cid = string_util.trimTr(diff_arr[2]);
var diff_detail = diff_arr[3];
var diff_info = diff_arr[4];

version_dao.findById(0, '', function(err, result){
    if(err){
        console.log(err);
    }else{
        console.log(result);
    }
});

/*
version_dao.create({
    version_id : version_id,
    site_name : site.name,
    now_cid : now_cid,
    next_cid : next_cid,
    diff_detail : diff_detail,
    diff_info : diff_info,
    created : new Date()
}, function(err, result) {
    if (err) {
        console.log(err);
    } else {
        console.log(result);
    }
});
*/