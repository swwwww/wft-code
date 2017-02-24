/**
  * Module dependencies.
  */

var mysql = require('mysql');
var config_util = require("../util/config_util");

/**
 * Shared private members.
 */
var pool;
function buildPool(){
    var db_config = config_util.getConfig('db');
    pool = mysql.createPool(db_config);
}

exports.getPool = function() {
    return pool;
}

buildPool();