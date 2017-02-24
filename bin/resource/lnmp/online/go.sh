#!/bin/bash

#set -x

now=`date +%Y%m%d%H%M%S`

root_dir=/home/work/online/
code_dir=/home/work/wanfantian.com/

# bak last code
mkdir ${root_dir}bak/${now}
cp -rf ${code_dir}* ${root_dir}bak/${now}/

# remove last file
rm -rf ${root_dir}temp/*

# unzip target file
unzip ${root_dir}zip/yii-cms.zip -d ${root_dir}temp/
mv ${root_dir}zip/yii-cms.zip ${root_dir}zip/zip_bak/yii-cms${now}.zip

# remove useless file
rm -rf ${root_dir}temp/bin/runtime/*
rm -rf ${root_dir}temp/bin/temp/template/compile/smarty3/*
rm -f ${root_dir}temp/gulpfile.js

# cp to platform dir
cp -rf ${root_dir}temp/* ${root_dir}platform/

# cp index.php to platform dir
cp -f ${root_dir}code/index.php ${root_dir}platform/

# mv platform to wanfantian.com
cp -rf ${root_dir}platform/* ${code_dir}

# chmod smarty3
chmod 777 ${code_dir}bin/temp -R
chmod 777 ${code_dir}bin/runtime -R
chmod 777 ${code_dir}bin/temp/template/compile/smarty3

# rm temp file
rm -rf ${root_dir}temp/*
# rm -rf ${root_dir}temp/.*
rm -rf ${root_dir}platform/*

