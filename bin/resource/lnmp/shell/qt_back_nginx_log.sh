#!/bin/bash
#set -x

now=`date +%Y%m%d_%H%M%S`
current_hour=`date +%H`
ago5=`date -d "5 days ago" +%Y%m%d`
ago10=`date -d "10 days ago" +%Y%m%d`

#0 save slave status
cp /home/work/thirdparty/nginx/log/access.log /home/work/thirdparty/nginx/log/${now}_access.log
echo '' > /home/work/thirdparty/nginx/log/access.log

#1. rm 10 days ago backup
rm /home/work/thirdparty/nginx/log/${ago10}*_access.log
