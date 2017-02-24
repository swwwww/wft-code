#!/bin/bash
#set -x

# 1 - set rpm
rpm -Uvh http://mirror.webtatic.com/yum/el6/latest.rpm

# 2 - php and fpm
yum install php55w.x86_64 -y
yum install php55w-fpm -y

# 3 - php devel & extension
yum -y install php55w-devel.x86_64
yum -y install pcre-devel.x86_64
yum -y install php55w-pear

# 4 - nginx
yum -y install nginx.x86_64

# 5 - mysql
yum install mysql-server mysql mysql-devel -y
service mysqld start
mysqladmin -u root passpword 'wanfantian' -h127.0.0.1

# 6 - redis
yum install redis.x86_64 -y

# 7 - php plugin
yum install php55w-pdo.x86_64 -y
yum install php55w-mbstring.x86_64 -y
yum install php55w-mcrypt.x86_64 -y
yum install php55w-mysql.x86_64 -y
yum install php55w-pecl-redis.x86_64 -y
yum install php55w-odbc.x86_64 -y

yum install php55w-cli.x86_64 -y
yum install php55w-common.x86_64 -y
yum install php55w-gd.x86_64 -y
yum install php55w-ldap.x86_64 -y
