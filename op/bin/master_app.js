var config_file = process.argv[2];
if (!config_file) {
    config_file = './config/config.json';
}

GLOBAL.CONFIG = require(config_file);
GLOBAL.NODE_DEBUG = true;
GLOBAL.NODE_DEBUG = false;

var config = require(config_file);

var config_util = require('./util/config_util');

config_util.loadConfig(config, function(err){
    if(!err){
        var build_service = require('./service/build_service');

        var config_port = config_util.getConfig('build_service_port');

        build_service.startService(config_port);
    }else{
        console.log(err);
    }
});
