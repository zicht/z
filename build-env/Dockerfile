FROM docker.zicht.nl:443/php-build

USER root

# These are needed for the functional tests specifying a shell
RUN apt-get update && apt-get -y install mysql-client perl python && apt-get clean

USER user
ADD ./.my.cnf /home/user/.my.cnf
