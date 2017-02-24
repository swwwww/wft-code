#!/bin/bash
#set -x

# crontab -l
# back database
#0 4 * * * sh /home/work/shell/qt_back_db.sh

# back nginx access log
#0 0 * * * sh /home/work/shell/qt_back_nginx_log.sh
#==============================================

now=`date +%Y%m%d_%H%M%S`
current_hour=`date +%H`
ago3=`date -d "3 days ago" +%Y%m%d`
ago5=`date -d "5 days ago" +%Y%m%d`

#1. dump the sql
mysqldump -uwft_dev -p12345677 -h127.0.0.1 -P3306 --default-character-set=utf8 --max_allowed_packet=256M wft > /home/work/db_back/${now}.sql

sql_name=/home/work/db_back/${now}.sql

#0 drop database wft_test
mysql -uwft_dev -p12345677 -h127.0.0.1 -P3306 -e "drop database wft_test; create database wft_test; use wft_test; source ${sql_name};"
