#!/bin/bash

set -e
#set -x

if [[ $3 = "--version_id" ]]; then
    site_name=$2
    version_id=$4
    deploy_type=$6

    link=/html/www_code/${site_name}_${deploy_type}
    target_version=/html/code/online/$site_name/$version_id

    echo $version_id

    rm -f $link
    ln -s $target_version $link
fi