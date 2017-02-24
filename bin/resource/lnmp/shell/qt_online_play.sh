#!/bin/bash
set -x
#一键上线脚本+备份回滚机制

now=`date +%Y%m%d_%H%M%S`

ago10=`date -d "10 days ago" +%Y%m%d`

back_dir=/work/backup/code/play.wanfantian.com
online_dir=/work/www/play.wanfantian.com

#创建备份文件夹
cd ${back_dir}
mkdir ${now}

#备份
cp -rf ${online_dir}/* ${back_dir}/${now}

#拷贝zip文件
cp /work/shell/yii-playsky.zip ${online_dir} -f
cd ${online_dir}

#解压文件
unzip -xo yii-playsky.zip
rm -f yii-playsky.zip

#清除历史
rm ${back_dir}/${ago10}* -rf
