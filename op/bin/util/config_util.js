var fs = require('fs');

var config = GLOBAL.CONFIG;

exports.getConfig = function(key) {
    if (config.hasOwnProperty(key)) {
        return config[key];
    }
};

exports.loadConfig = function(source, callback) {
    if (source) {
        for ( var key in source) {
            if (source.hasOwnProperty(key)) {
                config[key] = source[key];
            }
        }
    }

    callback(null);
};
