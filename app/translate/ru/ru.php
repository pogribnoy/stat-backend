<?php
$messages = array(
// Locale
"code"				=> "ru",
"date_format_short"	=> "d.m.Y",
"date_format_long"	=> "l dS F Y",
"time_format"		=> "h:i:s A",
"decimal_point"		=> ".",
"thousand_point"	=> " ",
"currency_short"	=> "р",

// Text
"text_home"				=> "Главная",
"text_yes"				=> "Да",
"text_no"				=> "Нет",
"text_none"				=> " --- Не выбрано --- ",
"text_select"			=> " --- Выберите --- ",
"text_pagination"		=> "Показано с {start} по {end} из {total} (страниц: {pages})",
"text_page_sizes"		=> "Показывать по",
"text_separator"		=> " &raquo; ",
"text_search"			=> "Поиск",
"text_active_short"		=> "(акт.)",
"text_nonactive_short"	=> " (неакт.)",
"text_site_full_name"	=> "Интернет портал общедоступной информации о расходах муниципальных образований «Расходы города»",
"text_site_short_name"	=> "Расходы города",
"text_site_version"		=> "Версия",


// Scrollers and pages
// Password recover
"text_password_recover_title"			=> "Укажите адрес электронной почты",
"text_password_recover_annotation"		=> "На указанный адрес электронной почты будет направлено письмо с Вашим текущим паролем. Поменять пароль всегда можно вличном кабинете сайта",
"text_password_recover_success"			=> "Письмо с Вашим паролем поставлено в очередь на отправку",

// Page. Login
"text_login_authorization"				=> "Авторизация", // page title
"text_login_password_placeholder"		=> "Пароль", // page title

// Page. Index
"text_index_title"						=> 'Административная панель сайта "Расходы города"', // page title
"index_index_my_organizations"			=> "Мои муниципалитеты",

// Page. Error
"text_errors_title"						=> "Ошибка",  // page title
"text_page_unauthorized"				=> "Доступ запрещен",
"text_page_not_found"					=> "Страница не найдена",
"text_page_system_error"				=> "Системная ошибка",
"text_unauthorized"						=> "У Вас нет доступа к данному функционалу. Обратитесь в службу поддержки",
"text_not_found"						=> "Вы ообратились к несуществующей (удаленной) странице. Обратитесь в службу поддержки",
"text_system_error"						=> "Возникла непредвиденная ошибка. Если она повторяется, пожалуйста, свяжитесь с нами",
"text_home"								=> "Перейти к главной странице",

// Page. Profile
"text_profile_title"					=> "Профиль пользователя",

// Page. Tasks
"text_tasks_title"						=> "Задачи", // page title
"text_tasks_index_schedule"				=> "Расписание",
"text_tasks_index_enabled"				=> "Включена",
"name_tasks_index_clear"				=> "Удаление непривязанных расходов",
"name_tasks_index_backup"				=> "Резервное копирование БД",
"name_tasks_index_backup_files"			=> "Резервное копирование файлов",
"name_tasks_index_response_sent"		=> "Отправка гражданам ответов на вопросы организациям",

// Scroller. Organization list
"text_organizationlist_title"			=> "Муниципалитеты", // scroller|page title
"text_organizationlist_region"			=> "Регион",

// Entity. Organization
"text_organization_title"				=> "Муниципалитет", // form|page title
"text_organization_new_entity_title"	=> "Новый муниципалитет", // new entity form|page title
"text_organization_region"				=> "Регион",

// Scroller. User list
"text_userlist_title"		=> "Пользователи", // scroller|page title
"text_userlist_email"		=> "E-mail",
"text_userlist_phone"		=> "Телефон",
"text_userlist_password"	=> "Пароль",
"text_userlist_name"		=> "ФИО",
"text_userlist_role"		=> "Роль",

// Entity. User
"text_user_title"				=> "Пользователь", // form|page title
"text_user_new_entity_title"	=> "Новый пользователь", // new entity form|page title

// Scroller. Organization request list
"text_organizationrequestlist_title"			=> "Вопросы", // scroller|page title
"text_organizationrequestlist_expense"			=> "Расход",
"text_organizationrequestlist_request"			=> "Вопрос",
"text_organizationrequestlist_organization"		=> "Муниципалитет",
"text_organizationrequestlist_request"			=> "Вопрос",
"text_organizationrequestlist_response"			=> "Ответ",
"text_organizationrequestlist_response_email"	=> "E-mail для ответа",

"text_organizationrequest_topic" => "Тема",
"text_organizationrequest_request" => "Вопрос",
"text_organizationrequest_response" => "Ответ",
"text_organizationrequest_response_email" => "Email для ответа",

// Entity. Organization request
"text_organizationrequest_title"			=> "Задайте вопрос", //entity|page title
"text_organizationrequest_expense"			=> "Расход",
"text_organizationrequest_topic"			=> "Тема",
"text_organizationrequest_request"			=> "Вопрос",
"text_organizationrequest_response"			=> "Ответ",
"text_organizationrequest_response_email"	=> "Email для ответа",

"code_status_declined"		=> "Отказан",
"code_status_done"			=> "Готов",
"code_status_in_progress"	=> "В обработке",
"code_status_new"			=> "Новый",
"code_status_processed"		=> "Обработан",

// Scroller. User role list
"text_userrolelist_title"			=> "Роли пользователей", // scroller|page title
"text_userrole_new_entity_title"	=> "Новая роль пользователя",

// Entity. User role
"text_userrole_title"				=> "Роль пользователя", // form|page title
"text_userrole_new_entity_title"	=> "Новая роль пользователя", // new entity form|page title

// Scroller. Resource list
"text_resourcelist_title"		=> "Ресурсы системы", // scroller|page title
"text_resourcelist_group"		=> "Группа контроля",
"text_resourcelist_controller"	=> "Контроллер",
"text_resourcelist_action"		=> "Действие",
"text_resourcelist_module"		=> "Модуль",

// Entity. Resource
"text_resource_title"				=> "Ресурс системы", // form|page title
"text_resource_new_entity_title"	=> "Новый ресурс системы", // new entity form|page title
"text_resource_group"				=> "Группа контроля",
"text_resource_controller"			=> "Контроллер",
"text_resource_action"				=> "Действие",
"text_resource_module"				=> "Модуль",

// Scroller. Expense list
"text_expenselist_title"		=> "Расходы", // scroller|page title
"text_expenselist_expense_type"	=> "Тип расхода",
"text_expenselist_target_date"	=> "Срок выполнения",
"text_expenselist_settlement"	=> "Наименование нас. пункта",

// Entity. Expense
"text_expense_title"			=> "Расход", // form|page title
"text_expense_new_entity_title"	=> "Новый расход", // new entity form|page title
"text_expense_expense_type"		=> "Тип расхода",
"text_expense_target_date"		=> "Срок выполнения",
"text_expense_settlement"		=> "Наименование нас. пункта",

// Scroller. Expense type list
"text_expensetypelist_title"	=> "Типы расходов", // scroller|page title

// Entity. Expense type
"text_expensetype_title"			=> "Тип расхода", // form|page title
"text_expensetype_new_entity_title"	=> "Новый тип расхода", // new entity form|page title

// Scroller. Expense status list
"text_expensestatuslist_title"	=> "Статусы расходов", // scroller|page title

// TODO.
// Entity. Expense status

// Scroller. Region list
"text_regionlist_title"	=> "Регионы", // scroller|page title

// TODO.
// Entity. Region

// Scroller. Street type list
"text_streettypelist_title"	=> "Типы улиц", // scroller|page title

// Entity. Street type
"text_streettype_title"				=> "Тип улицы",
"text_streettype_new_entity_title"	=> "Новый тип улицы",

// Scroller. News list
"text_newslist_title"				=> "Новости", // scroller|page title
"text_newslist_publication_date"	=> "Дата публикации",

// Entity. News
"text_news_title"				=> "Новость",
"text_news_new_entity_title"	=> "Новая новость",
"text_news_publication_date"	=> "Дата публикации",

// Menu. 
"main_menu_navigation"			=> "Навигация",
"main_menu_content"				=> "Содержимое",
"main_menu_organizationlist"	=> "Организации",
"main_menu_newslist"			=> "Новости",
"main_menu_settings"			=> "Настройки сайта",
"main_menu_userlist"			=> "Пользователи",
"main_menu_userrolelist"		=> "Роли пользователей",
"main_menu_resourcelist"		=> "Ресурсы сайта",
"main_menu_streettypelist"		=> "Типы улиц",
"main_menu_expensetypelist"		=> "Типы расходов",
"main_menu_regionlist"			=> "Регионы",
"main_menu_expensestatuslist"	=> "Статусы расходов",
"main_menu_tasks"				=> "Задачи",
"main_menu_profile"				=> "Профиль пользователя",
"main_menu_exit_link"			=> "выход",

// Common tabs
"text_tab_main_information" => "Основная информация",
//"text_tab_properties" => "Свойства",


// Common entities properties labels
"text_entity_property_actions"			=> "Действия",
"text_entity_property_active"			=> "Активность",
"text_entity_property_amount"			=> "Сумма, тыс. р.",
"text_entity_property_code"				=> "Код",
"text_entity_property_contacts"			=> "Контакты",
"text_entity_property_coordinates_x"	=> "X = ",
"text_entity_property_coordinates_y"	=> "Y = ",
"text_entity_property_created_at"		=> "Создано",
"text_entity_property_created_by"		=> "Автор",
"text_entity_property_date"				=> "Дата",
"text_entity_property_description"		=> "Описание",
"text_entity_property_email"			=> "Email",
"text_entity_property_executor"			=> "Подрядчик",
"text_entity_property_files"			=> "Файлы",
"text_entity_property_fio"				=> "ФИО",
"text_entity_property_header"			=> "Заголовок",
"text_entity_property_id"				=> "ID",
"text_entity_property_image"			=> "Изображение",
"text_entity_property_images"			=> "Изображения",
"text_entity_property_house_building"	=> "Дом, стр.",
"text_entity_property_login"			=> "Логин",
"text_entity_property_n"				=> "№",
"text_entity_property_name"				=> "Наименование",
"text_entity_property_password"			=> "Пароль",
"text_entity_property_period_from"		=> "с",
"text_entity_property_period_to"		=> "по",
"text_entity_property_phone"			=> "Телефон",
"text_entity_property_recaptcha"		=> "Проверка на человечность",
"text_entity_property_recipient"		=> "Получатель",
"text_entity_property_region"			=> "Регион",
"text_entity_property_related_data"		=> "Связанные записи",
"text_entity_property_role"				=> "Роль",
"text_entity_property_status"			=> "Статус",
"text_entity_property_street"			=> "Улица",
"text_entity_property_street_type"		=> "Тип улицы",
"text_entity_property_value"			=> "Значение",

// Statuses
"status_new"			=> "Новый",
"status_prcessed"		=> "Обработано",
"status_declined"		=> "Отказано",
"status_in_progress"	=> "В обработке",

// Buttons
"button_add"				=> "Добавить",
"button_add_address"		=> "Добавить адрес",
"button_apply"				=> "Применить",
"button_back"				=> "Назад",
"button_clear"				=> "Очистить",
"button_change_address"		=> "Изменить адрес",
"button_check"				=> "Проверить",
"button_confirm"			=> "Подтвердить",
"button_continue"			=> "Продолжить",
"button_copy"				=> "Копировать",
"button_coupon"				=> "Применить купон",
"button_delete"				=> "Удалить",
"button_download"			=> "Скачать",
"button_edit"				=> "Редактировать",
"button_enter"				=> "Войти",
"button_filter"				=> "Фильтровать",
"button_filter_clear"		=> "Сброс",
"button_login"				=> "Войти",
"button_new_address"		=> "Новый адрес",
"button_password_change"	=> "Сменить пароль",
"button_password_print"		=> "Напечатать пароль",
"button_password_recover"	=> "Напомнить пароль",
"button_question"			=> "Задать вопрос",
"button_remove"				=> "Удалить",
"button_save"				=> "Сохранить",
"button_save_direct"		=> "Сохранить на сервер",
"button_save_local"			=> "Сохранить локально",
"button_search"				=> "Поиск",
"button_select"				=> "Указать",
"button_send"				=> "Отправить",
"button_update"				=> "Применить",
"button_upload"				=> "Загрузить",
"button_view"				=> "Просмотр",
"button_wishlist"			=> "в закладки",

// Information
"text_no_data"	=> "Нет данных для отображения",
"text_no_edit"	=> "Редактирование невозможно",

// Client messages
"msg_success_title"				=> "Операция успешна",
"msg_success_entity_saved"		=> "Запись с успешно сохранена",
"msg_success_file_deleted"		=> 'Файл "{file_name}" удален',


"msg_check_field_invalid_value"		=> 'Поле "%field_name%". Передано некорректное значение',
"msg_check_field_mandatory"			=> 'Поле "%field_name%" обязательно для указания',
"msg_check_field_min_value"			=> 'Поле "%field_name%" содержит значение меньше допустимого',
"msg_check_field_max_value"			=> 'Поле "%field_name%" содержит значение больше допустимого',
"msg_check_field_email_format"		=> 'Поле "%field_name%" содержит значение, не соответствущее адресу электронной почты',
"msg_check_field_text_min"			=> 'Поле "%field_name%" содержит слишком короткое значение (должно быть не менее %field_min% символов)',
"msg_check_field_text_max"			=> 'Поле "%field_name%" содержит слишком длинное значение (должно быть не более %field_max% символов)',
"msg_check_field_period_1"			=> 'Поле "%field_name%". Дата "%field_name1%" обязательна для заполнения',
"msg_check_field_period_2"			=> 'Поле "%field_name%". Дата "%field_name2%" обязательна для заполнения',
"msg_check_field_period_any"		=> 'Поле "%field_name%". Должна быть заполнена хотя бы одна дата',
"msg_check_field_period_full"		=> 'Поле "%field_name%". Период должен быть заполнен полностью',
"msg_check_field_period_2lt1"		=> 'В поле "%field_name%" дата окончания периода не может быть меньше даты начала периода',
"msg_check_field_not_found_by_id"	=> 'Поле "%field_name%". По переданому идентификатору не найдена запись в БД',
"msg_check_field_not_in_list"		=> 'Поле "%field_name%". Передано значение не из списка',
"msg_check_field_recaptcha"			=> 'Есть подозрение, что вы робот. Перезагрузите страницу и повторите ввод',



"msg_error_title"						=> 'Ошибка',
"msg_error_not_one_rows"				=> '001. Выборка данных из БД либо пуста, либо содержит более 1 записи',
"msg_error_post_is_expected"			=> '002. Ожидается использование метода POST',
"msg_error_no_id"						=> '003. Не получен идентификатор сущности',
"msg_error_file_not_deleted"			=> '004. Файл "{file_name}" не удален',
"msg_error_file_not_deleted_from_hdd"	=> '005. Файл "{file_name}" не удален из файлового хранилища',
"msg_error_files_not_deleted"			=> '006. Удаление файлов неуспешно',
"msg_error_delete_fail"					=> '007. Удаление неуспешно',




"error_upload_1"        => "Загружаемый на сервер файл превышает параметр upload_max_filesize в php.ini!",
"error_upload_2"        => "Загружаемый на сервер файл превышает параметр MAX_FILE_SIZE который определен в HTML форме!",
"error_upload_3"        => "Загружаемый на сервер файл был загружен не полностью!",
"error_upload_4"        => "Файл не был загружен!",
"error_upload_6"        => "Отсутсвует временная директория!",
"error_upload_7"        => "Не удалось записать файл на диск!",
"error_upload_8"        => "Загружаемый на сервер файл не подходит по расширению!",
"error_upload_999"      => "Неизвестная ошибка!"
);
