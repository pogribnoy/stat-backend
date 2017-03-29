#!/bin/sh
cd /var/www/stat-backend/backups
tar -zcvf stat_$(date +%Y%m%d)_files.tgz /var/www/stat-backend/public/upload

