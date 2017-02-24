#!/bin/bash
#set -x

yum install zip -y
yum install unzip -y

# 1.unzip lnmp env
mirror_dir=/root/mirror/
lnmp_dir=${mirror_dir}lnmp/
unzip ${mirror_dir}lnmp.zip -d ${mirror_dir}

## 1-1.fix yum repo
cp ${lnmp_dir}/yum/* /etc/yum.repos.d -rf

## 1-2.yum install env
dos2unix ${mirror_dir}lnmp/shell/qt_yum_install.sh
sh ${mirror_dir}lnmp/shell/qt_yum_install.sh
#================================

# 2.make /home/work dir
cd /home
mkdir work

# 3.make necessary deploy dir
cd /home/work
mkdir access
mkdir db_back
mkdir online
mkdir shell
mkdir thirdparty
mkdir wanfantian.com

chmod 777 access

# 4.cp online & db_back shell
cp ${lnmp_dir}/online/* /home/work/online/ -rf
cp ${lnmp_dir}/shell/* /home/work/shell/ -rf

# 5.cp LNMP env
cp ${lnmp_dir}/mysql /home/work/thirdparty/ -rf
cp ${lnmp_dir}/nginx /home/work/thirdparty/ -rf
cp ${lnmp_dir}/php /home/work/thirdparty/ -rf
cp ${lnmp_dir}/phpmyadmin /home/work/thirdparty/ -rf

# 6.change access & owner for mysql
chmod 777 /home/work/thirdparty/mysql/log -R
chmod 777 /home/work/thirdparty/mysql/tmp -R
chown mysql /home/work/thirdparty/mysql -R

# 7.replace LNMP config
## 7-1.mysql
cd /home/work/thirdparty/mysql
mv my.cnf /etc/my.cnf -f
ln -s /etc/my.cnf ./etc/my.cnf

## 7-2.nginx
cd /home/work/thirdparty/nginx
mv nginx.conf /etc/nginx/nginx.conf -f
mv phpmyadmin.conf /etc/nginx/conf.d/phpmyadmin.conf -f
mv wanfantian.com.conf /etc/nginx/conf.d/wanfantian.com.conf -f
ln -s /etc/nginx/nginx.conf ./etc/nginx.conf
ln -s /etc/nginx/conf.d/phpmyadmin.conf ./etc/phpmyadmin.conf
ln -s /etc/nginx/conf.d/wanfantian.com.conf ./etc/wanfantian.com.conf

## 7-3.php
cd /home/work/thirdparty/php
mv fpm.conf /etc/fpm.conf -f
mv fpm.www.conf /etc/php-fpm.d/www.conf -f
mv php.ini /etc/php.ini -f
ln -s /etc/fpm.conf ./etc/fpm.conf
ln -s /etc/php-fpm.d/www.conf ./etc/fpm.www.conf
ln -s /etc/php.ini ./etc/php.ini

## 7-4.phpmyadmin
cd /home/work/thirdparty/phpmyadmin
mv config.inc.php /etc/phpMyAdmin/config.inc.php -f
ln -s /etc/phpMyAdmin/config.inc.php ./etc/config.inc.php
ln -s /usr/share/phpMyAdmin ./code
ln -s /var/lib/phpMyAdmin/upload/ ./upload

# 8.ln quick for root
cd /root
ln -s /home/work/access access
ln -s /home/work/db_back db_back
ln -s /home/work/online online
ln -s /home/work/shell shell
ln -s /home/work/thirdparty thirdparty
ln -s /home/work/wanfantian.com wanfantian.com