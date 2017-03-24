#!/bin/sh
cd /var/www/stat-backend/backups
tar -zcvf stat_$(date +%d%m%y)_files.tgz /var/www/stat-backend/public/upload

