#!/bin/sh
mysqldump -u root -prefaliu  stat > /var/www/stat-backend/backups/stat.sql
cd /var/www/stat-backend/backups
tar -zcvf stat_$(date +%d%m%y).tgz *.sql
find -name '*.tgz' -type f -mtime +2 -exec rm -f {} \;
