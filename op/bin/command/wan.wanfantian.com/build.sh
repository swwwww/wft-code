#!/bin/bash

set -e
#set -x

if [[ $2 = "--module" ]]; then
    BUILD_MODULE=$3
else
    BUILD_MODULE="all"
fi

SITE="wan.wanfantian.com"
GIT_NAME="wanfantian"

ONLINE_DIR=/html/code/online/$SITE

BUILD_DIR=/home/work/code/build/$SITE
GIT_DIR=http://wftgit.greedlab.com:10080/wwjie/${GIT_NAME}.git

SPLIT="---+++===+++---"

cd $BUILD_DIR

(
#echo "try to acquire lock"
flock 200

now_version=`ls -l $ONLINE_DIR | awk '{print $9}' | grep '[0-9]\\+' | sort -nr | head -1 | tr -d '\n' | tr -d '\r'`
if [ ! $now_version ]; then
	now_version=1
	next_version=1
else
	next_version=$((now_version+1))
fi

if [ -d "$ONLINE_DIR/$now_version" ]; then
	cp -R $ONLINE_DIR/$now_version $BUILD_DIR/$next_version
	cd $BUILD_DIR/$next_version

	#生成的版本号
	echo $next_version
	echo $SPLIT

	#老的提交id
	old_commit_id=`git log --pretty=oneline -1 | awk '{print $1}' | tr -d '\n' | tr -d '\r'`
	echo $old_commit_id
	echo $SPLIT

	#忽略本地修改
	git reset --hard --quiet

	#合并新的代码
	git fetch origin --quiet
	git merge origin/master --quiet

	#新的提交id
	new_commit_id=`git log --pretty=oneline -1 | awk '{print $1}' | tr -d '\n' | tr -d '\r'`
	echo $new_commit_id
	echo $SPLIT

	#diff详细信息
	git diff $old_commit_id $new_commit_id
	echo $SPLIT

	#diff说明
	git diff $old_commit_id $new_commit_id --stat

	mv $BUILD_DIR/$next_version  $ONLINE_DIR/$next_version
else
	git clone $GIT_DIR
	mv $GIT_NAME $ONLINE_DIR/$next_version
fi

) 200>/var/lock/build-${SITE}.lock

