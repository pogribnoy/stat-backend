#!/bin/sh

# ������������ HTML
/var/www/stat-backend/minificator/minify_html.sh

# ������������ JS
/var/www/stat-backend/minificator/minify_js.sh

# ������� ���
## backend
rm -f /var/www/stat-backend/app/cache/views/*.html
rm -f /var/www/stat-backend/app/cache/metadata/*.php
rm -f /var/www/stat-backend/app/cache/*.php

## frontend
rm -f /var/www/stat-frontend/app/cache/views/*.html
rm -f /var/www/stat-frontend/app/cache/metadata/*.php
rm -f /var/www/stat-frontend/app/cache/*.php

