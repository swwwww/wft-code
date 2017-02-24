#!/bin/bash

set -x
set -e


version=`svn cat http://192.168.0.2/repos/build/test.baidu.com/version/enableVersion | head -n 1 | tr -d '\n' | tr -d '\r'` 

if [ ! -d "/home/work/test.baidu.com/$version" ]; then
    /var/www/node/agent/shell/test.baidu.com/deploy.sh
fi

svn export http://192.168.0.2/repos/conf/httpd.conf /etc/httpd/conf/httpd.conf
svn export http://192.168.0.2/repos/conf/php.ini /etc/php.ini

if [ ! -f "/etc/httpd/run/httpd.pid" ]; then
	apachectl start
fi

