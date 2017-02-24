/**
 * Shared private members.
 */
var pool = require("../util/db_util").getPool();

exports.findById = function(version_id, site_name, callback) {
    pool.query("SELECT * FROM ps_op_version WHERE version_id = ? and site_name = ?", [ version_id, site_name ], function(err, results) {
        if (err) {
            callback(err, null);
        } else {
            callback(null, results.length > 0 ? results[0] : null);
        }
    });
}

exports.create = function(data, callback) {
    pool.query("INSERT INTO ps_op_version SET ?", data, callback);
}

exports.update = function(data, callback) {
    pool.query("UPDATE ps_op_version SET ? WHERE version_id = ? and site_name = ?", [ data, data.version_id, data.site_name ], callback);
}

exports.setVersionOfflineBySiteName = function(site_name, callback) {
    var data = {
        status : 0
    };
    pool.query("update ps_op_version set ? where site_name = ? and status = 1", [ data, site_name ], callback);
}

exports.getNowVersionId = function(site_name, callback) {
    pool.query("SELECT version_id FROM ps_op_version WHERE site_name = ? and status = 1", [ site_name ], function(err, results) {
        if (err) {
            callback(err, null);
        } else {
            callback(null, results.length > 0 ? results[0]['version_id'] : 0);
        }
    });
}

exports.upsert = function(data, callback) {
    var self = this;
    self.findById(data.version_id, data.site_name, function(err, result) {
        if (err) {
            callback(err, null);
        } else {
            if (result) {
                self.update(data, callback);
            } else {
                self.create(data, callback);
            }
        }
    });
}
