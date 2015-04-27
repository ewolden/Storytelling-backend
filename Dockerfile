FROM ubuntu:trusty

# Install packages
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && \
  apt-get -y install supervisor git apache2 libapache2-mod-php5 mysql-server php5-mysql pwgen php-apc php5-mcrypt && \
  echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Install maven, java
RUN apt-get -y install openjdk-7-jdk maven
RUN apt-get -y install wget
RUN wget http://www.gtlib.gatech.edu/pub/apache/mahout/0.9/mahout-distribution-0.9-src.tar.gz
RUN tar -xf mahout-distribution-0.9-src.tar.gz
RUN mv mahout-distribution-0.9 /opt/
RUN cd /opt/mahout-distribution-0.9 && mvn -DskipTests install

#Download app and config files
RUN git clone https://github.com/ewolden/Storytelling-backend /app
RUN mv /app/docker/start-apache2.sh /start-apache2.sh
RUN mv /app/docker/start-mysqld.sh /start-mysqld.sh
RUN mv /app/docker/run.sh /run.sh
RUN chmod 755 /*.sh
RUN mv /app/docker/my.cnf /etc/mysql/conf.d/my.cnf
RUN mv /app/docker/supervisord-apache2.conf /etc/supervisor/conf.d/supervisord-apache2.conf
RUN mv /app/docker/supervisord-mysqld.conf /etc/supervisor/conf.d/supervisord-mysqld.conf

# Remove pre-installed database
RUN rm -rf /var/lib/mysql/*

# Add MySQL utils
RUN mv /app/docker/create_mysql_admin_user.sh /create_mysql_admin_user.sh
RUN chmod 755 /*.sh

# config to enable .htaccess
RUN mv /app/docker/apache_default /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Configure /app folder with app

RUN mkdir -p /app && rm -fr /var/www/html && ln -s /app /var/www/html

# Add cron job to perform harvesting
RUN mv /app/docker/crons.conf /crons.conf
RUN chmod 755 /crons.conf
RUN crontab /crons.conf

#Enviornment variables to configure php
ENV PHP_UPLOAD_MAX_FILESIZE 10M
ENV PHP_POST_MAX_SIZE 10M

# Add volumes for MySQL 
VOLUME  ["/etc/mysql", "/var/lib/mysql" ]

# See if app has been updated, pull from git
ADD http://www.random.org/strings/?num=10&len=8&digits=on&upperalpha=on&loweralpha=on&unique=on&format=plain&rnd=new uuid
RUN cd /app && git pull


EXPOSE 80 3306
CMD ["/run.sh"]