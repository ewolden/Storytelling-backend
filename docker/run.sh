#!/bin/bash

VOLUME_HOME="/var/lib/mysql"

sed -ri -e "s/^upload_max_filesize.*/upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}/" \
    -e "s/^post_max_size.*/post_max_size = ${PHP_POST_MAX_SIZE}/" /etc/php5/apache2/php.ini
if [[ ! -d $VOLUME_HOME/mysql ]]; then
    echo "=> An empty or uninitialized MySQL volume is detected in $VOLUME_HOME"
    echo "=> Installing MySQL ..."
    mysql_install_db > /dev/null 2>&1
    echo "=> Done!"  
    /create_mysql_admin_user.sh
else
    echo "=> Using an existing volume of MySQL"
fi

#set java path for maven
export JAVA_HOME=/usr/lib/jvm/java-1.7.0-openjdk-amd64
export MAHOUT_HOME=/opt/mahout-distribution-0.9/bin
export PATH=$PATH:$MAHOUT_HOME
cron -f &
echo "=> Cron is running, harvesting performed at 03:05"
exec supervisord -n 
