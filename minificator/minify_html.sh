#!/bin/sh

# С cборка шаблонов
#  stat-backend/public/templates/entity_template.html
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/public/templates/entity_template.dev.html -o /var/www/stat-backend/public/templates/entity_template.html
#  stat-backend/public/templates/scroller_template.html
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/public/templates/scroller_template.dev.html -o /var/www/stat-backend/public/templates/scroller_template.html
#  stat-backend/public/templates/organization.html
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/public/templates/organization.dev.html -o /var/www/stat-backend/public/templates/organization.html
#  stat-backend/public/templates/modal_template.html
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/public/templates/modal_template.dev.html -o /var/www/stat-backend/public/templates/modal_template.html
#  stat-backend/public/templates/ckecks_modal_template.html
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/public/templates/ckecks_modal_template.dev.html -o /var/www/stat-backend/public/templates/ckecks_modal_template.html

### С cборка представлений
## Backend
# stat-backend/app/views/index.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/app/views/index.dev.phtml -o /var/www/stat-backend/app/views/index.phtml
# stat-backend/app/views/partials/menu.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/app/views/partials/menu.dev.phtml -o /var/www/stat-backend/app/views/partials/menu.phtml
# stat-backend/app/views/layouts/index.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/app/views/layouts/index.dev.phtml -o /var/www/stat-backend/app/views/layouts/index.phtml
# stat-backend/app/views/layouts/profile.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php /var/www/stat-backend/app/views/layouts/profile.dev.phtml -o /var/www/stat-backend/app/views/layouts/profile.phtml
# stat-backend/app/views/tasks/index.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/app/views/tasks/index.dev.phtml -o /var/www/stat-backend/app/views/tasks/index.phtml
# stat-backend/app/views/login/index.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-backend/app/views/login/index.dev.phtml -o /var/www/stat-backend/app/views/login/index.phtml

## Frontend
# stat-frontend/app/views/index.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-frontend/app/views/index.dev.phtml -o /var/www/stat-frontend/app/views/index.phtml
# stat-frontend/app/views/partials/menu.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-frontend/app/views/partials/menu.dev.phtml -o /var/www/stat-frontend/app/views/partials/menu.phtml
# stat-frontend/app/views/layouts/index.dev.phtml
java -jar /var/www/stat-backend/minificator/htmlcompressor-1.5.3.jar --type html --preserve-php --js-compressor closure-compiler-v20170626 --closure-opt-level advanced /var/www/stat-frontend/app/views/layouts/index.dev.phtml -o /var/www/stat-frontend/app/views/layouts/index.phtml
