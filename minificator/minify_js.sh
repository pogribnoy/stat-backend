#!/bin/sh

# public/js: common, entity, modal, scroller
java -jar /var/www/stat-backend/minificator/closure-compiler-v20170626.jar --js /var/www/stat-backend/public/js/common.js --compilation_level WHITESPACE_ONLY /var/www/stat-backend/public/js/entity.js /var/www/stat-backend/public/js/modal.js /var/www/stat-backend/public/js/scroller.js --js_output_file /var/www/stat-backend/public/js/cmn.js

# public/templates/common_templates
java -jar /var/www/stat-backend/minificator/closure-compiler-v20170626.jar --js /var/www/stat-backend/public/templates/common_templates.dev.js --compilation_level WHITESPACE_ONLY --js_output_file /var/www/stat-backend/public/templates/c_t.js

# app/views/partials/common/translator.phtml
# java -jar /var/www/stat-backend/minificator/closure-compiler-v20170626.jar --js /var/www/stat-backend/app/common/tasks/templates/translator.dev.js --compilation_level ADVANCED_OPTIMIZATIONS --js_output_file /var/www/stat-backend/app/common/tasks/templates/translator.js