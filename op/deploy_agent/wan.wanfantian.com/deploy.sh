#!/bin/bash

set -x
set -e

echo "go start"

(
echo "try to acquire lock"
flock 200
echo "lock acquired"
if [ ! -d "/home/work" ]; then
	cp /var/www/node/agent/shell/work /home/work -R
	chown work:work /home/work
	chmod 755 /home/work -R
fi

if [ ! -d "/home/work/log" ]; then
	mkdir /home/work/log
	chmod 777 /home/work/log -R
fi

if [ ! -d "/home/work/test.baidu.com" ]; then
	mkdir /home/work/test.baidu.com
fi

version=`svn cat http://192.168.0.2/repos/build/test.baidu.com/version/enableVersion | head -n 1 | tr -d '\n' | tr -d '\r'` 


if [ ! -d "/home/work/test.baidu.com/0" ]; then
    svn co http://192.168.0.2/repos/build/test.baidu.com/$version /home/work/test.baidu.com/0
else
    svn switch http://192.168.0.2/repos/build/test.baidu.com/$version /home/work/test.baidu.com/0 --force
fi

if [ -d "/home/work/test.baidu.com/$version" ]; then
	cp -R /home/work/test.baidu.com/$version /home/work/test.baidu.com/${version}tmp
    chmod 777 /home/work/test.baidu.com/${version}tmp/temp -R
	ln -s -n -f /home/work/test.baidu.com/${version}tmp /var/www/html
	sleep 5
	rm /home/work/test.baidu.com/$version -Rf
fi

cp /home/work/test.baidu.com/0 /home/work/test.baidu.com/$version -R
find /home/work/test.baidu.com/$version -type d -name ".svn"|xargs rm -rf
chmod 777 /home/work/test.baidu.com/$version/temp -R

ln -s -n -f /home/work/test.baidu.com/$version /var/www/html


if [ -d "/home/work/test.baidu.com/${version}tmp" ]; then
	sleep 5
	rm /home/work/test.baidu.com/${version}tmp -Rf
fi
) 200>/var/lock/test-online-deploy.lock

rm /var/lock/test-online-deploy.lock -f

svn export http://192.168.0.2/repos/conf/httpd.conf /etc/httpd/conf/httpd.conf
svn export http://192.168.0.2/repos/conf/php.ini /etc/php.ini

if [ ! -f "/etc/httpd/run/httpd.pid" ]; then
	apachectl start
fi

