#!/bin/bash

cat $1 | awk -F '[@*!*!*@]+' '{print $8}' | sort -rn > speed.out

cat 20151110_ds.log | grep -v spiderman | grep -v +http:// | awk -F '@' '{if($25) {print $0}}'

cat $1 | grep -v bot.html | grep -v ahrefs.com | grep -v majestic12.co | grep -v bingbot | grep -v Googlebot | grep -v spider | grep -v Spider | grep -v ysearch | awk -F '[@*!*!*@]+' '{print $0}'
