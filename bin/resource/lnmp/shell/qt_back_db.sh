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
ago10=`date -d "10 days ago" +%Y%m%d`

mysql_exe='mysql -uroot -pwanfantian -h127.0.0.1 -P3306'
mysqldump_exe='/work/thirdparty/mysql/bin/mysqldump -uroot -pwanfantian -h127.0.0.1 -P3306'
back_dir=/work/backup/db

# stop slave
${mysql_exe} -e "stop slave"

sleep 5

#0 save slave status
${mysql_exe} -e "show slave status\G" > ${back_dir}/${now}_slave_status.txt

#1. dump the sql
${mysqldump_exe} --default-character-set=utf8 --max_allowed_packet=256M wft > ${back_dir}/${now}.sql

#2. slave start
${mysql_exe} -e "start slave"

#3. rm 10 days ago backup
rm ${back_dir}/${ago10}*.sql
rm ${back_dir}/${ago10}*_slave_status.txt
