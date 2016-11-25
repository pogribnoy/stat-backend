-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Ноя 21 2016 г., 21:17
-- Версия сервера: 5.7.9
-- Версия PHP: 5.6.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `stat`
--

-- --------------------------------------------------------

--
-- Структура таблицы `expense`
--

DROP TABLE IF EXISTS `expense`;
CREATE TABLE IF NOT EXISTS `expense` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `expense_type_id` int(10) NOT NULL COMMENT 'Идентификатор типа расхода',
  `organization_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Идентификатор организации, к которой относится расход',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `expense`
--

INSERT INTO `expense` (`id`, `name`, `amount`, `date`, `expense_type_id`, `organization_id`) VALUES
(32, 'asd', 10000, '2016-11-01', 1, NULL),
(34, 'qqq', 10000, '2016-11-01', 1, NULL),
(35, 'фыв', 10000, '2016-11-01', 1, NULL),
(36, 'фыв', 10000, '2016-11-01', 1, NULL),
(38, 'йцйц', 10000, '2016-11-01', 1, NULL),
(39, 'фыв', 10000, '2016-11-01', 1, NULL),
(40, 'фывв', 10000, '2016-09-12', 1, NULL),
(41, 'фыв', 10000, '2016-11-01', 1, NULL),
(42, 'йцу', 10000, '2016-11-01', 1, NULL),
(43, 'asd', 10000, '2016-11-01', 1, 22),
(44, 'йцу', 10000, '2016-11-01', 2, 22),
(46, 'test2', 10000, '2016-11-01', 1, NULL),
(47, 'test123', 10000, '2016-08-16', 1, NULL),
(48, 'test321', 10000, '2016-11-01', 1, 22),
(49, 'test09', 10000, '2016-09-07', 1, 22),
(50, 'testasd', 10000, '2016-11-01', 1, 22),
(64, 'test23', 2200, '2016-11-01', 1, 22),
(65, 'ываывавы', 3400, '2016-11-13', 2, NULL),
(66, 'ываывавы', 123256, '2016-11-13', 2, 22),
(67, 'asdsadas', 0, '2016-11-13', 1, 22),
(68, 'Ножницы', 1000000, '2016-11-20', 3, 38);

-- --------------------------------------------------------

--
-- Структура таблицы `expense_type`
--

DROP TABLE IF EXISTS `expense_type`;
CREATE TABLE IF NOT EXISTS `expense_type` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `expense_type`
--

INSERT INTO `expense_type` (`id`, `name`) VALUES
(1, 'Ремонт'),
(2, 'Прочие расходы'),
(3, 'Канцелярия');

-- --------------------------------------------------------

--
-- Структура таблицы `file`
--

DROP TABLE IF EXISTS `file`;
CREATE TABLE IF NOT EXISTS `file` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `directory` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `file`
--

INSERT INTO `file` (`id`, `name`, `directory`) VALUES
(1, 'organization_22_27644.JPG', 'upload/files/default/22/'),
(2, 'organization_34_2591.jpg', 'upload/files/default/34/'),
(5, 'organization_35_1968.jpg', 'upload/files/default/35/'),
(6, 'organization_26_5673.jpg', 'upload/files/default/26/');

-- --------------------------------------------------------

--
-- Структура таблицы `file_collection`
--

DROP TABLE IF EXISTS `file_collection`;
CREATE TABLE IF NOT EXISTS `file_collection` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `collection_id` int(10) UNSIGNED NOT NULL,
  `file_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `file_collection`
--

INSERT INTO `file_collection` (`id`, `collection_id`, `file_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 3, 4),
(5, 3, 5),
(6, 4, 6);

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf16 NOT NULL COMMENT 'Название попроса',
  `description` varchar(500) CHARACTER SET utf16 NOT NULL,
  `publication_date` date NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `created_by` int(10) UNSIGNED NOT NULL COMMENT 'user_id',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `name`, `description`, `publication_date`, `active`, `created_by`, `created_at`) VALUES
(12, 'Программа 13', 'Московские улицы начнут преображаться уж', '2016-11-01', 1, 1, '2014-12-22 19:09:43'),
(14, 'фывфывs', 'фывфывsssss', '2016-11-02', 0, 1, '2015-02-12 13:25:11'),
(17, 'фывфывs', '<ol>\n	<li>&quot; OR 1=1 asss</li>\n</ol>\n', '2016-11-03', 1, 1, '2015-02-12 14:08:23'),
(19, 'asdsdsdasd', '<p>q<strong>weqwe </strong></p>\n\n<p>as</p>\n', '2016-11-04', 1, 1, '2015-02-12 14:56:40'),
(20, 'asdasdasd', '<p>qqq<s>qqq</s>1</p>\n', '2016-11-05', 1, 23, '2015-02-17 17:25:43'),
(21, 'qwqweqwe', '<p>qwe</p>\n\n<p>qwe</p>\n\n<ol>\n	<li>qwe</li>\n	<li>qwe</li>\n	<li>qwe</li>\n</ol>\n\n<p>qw</p>\n', '2016-11-04', 1, 23, '2015-02-19 10:45:02'),
(23, 'Новость дня!', '<h2 style="font-style:italic">фываыаыафывафыва</h2>\n\n<h1><big>уыаыаываываыва</big></h1>\n', '2016-11-03', 1, 23, '2015-02-19 19:44:40');

-- --------------------------------------------------------

--
-- Структура таблицы `organization`
--

DROP TABLE IF EXISTS `organization`;
CREATE TABLE IF NOT EXISTS `organization` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf16 NOT NULL COMMENT 'Название организации',
  `contacts` varchar(500) CHARACTER SET utf16 NOT NULL,
  `region_id` int(10) NOT NULL,
  `email` varchar(255) CHARACTER SET utf16 DEFAULT NULL,
  `img` varchar(255) CHARACTER SET utf16 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `organization`
--

INSERT INTO `organization` (`id`, `name`, `contacts`, `region_id`, `email`, `img`) VALUES
(1, 'Объект 1', 'фывфыв', 20, NULL, NULL),
(2, 'Объект 21', 'фывфыв2', 22, NULL, NULL),
(21, 'q', 'q', 20, NULL, NULL),
(22, 'www', 'г. Подльск, ул. Победы, д.3, подъезд 4. Телефон:  7(4967)65-45-54', 20, '', '1'),
(23, 'з', 'з', 20, NULL, NULL),
(24, '1', '1', 20, '1', NULL),
(25, '2', '2', 20, NULL, NULL),
(26, '38', '3', 20, '', '4'),
(27, '1', '1', 20, NULL, NULL),
(28, '1', '1', 20, NULL, NULL),
(30, '2', '2', 20, NULL, NULL),
(33, '5', '5', 20, NULL, NULL),
(34, '6', '6', 21, '', '2'),
(35, '7', '7', 20, '', '3'),
(36, '8', '8', 20, NULL, NULL),
(38, '44234', '44', 20, '', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `organization_request`
--

DROP TABLE IF EXISTS `organization_request`;
CREATE TABLE IF NOT EXISTS `organization_request` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Идентификатор организации, к которой относится расход',
  `user_id` int(10) UNSIGNED NOT NULL,
  `topic_id` int(10) UNSIGNED NOT NULL,
  `status_id` int(10) UNSIGNED NOT NULL,
  `request` varchar(1000) NOT NULL,
  `response` varchar(1000) DEFAULT NULL,
  `response_email` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- --------------------------------------------------------

--
-- Структура таблицы `organization_request_topic`
--

DROP TABLE IF EXISTS `organization_request_topic`;
CREATE TABLE IF NOT EXISTS `organization_request_topic` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `organization_request_topic`
--

INSERT INTO `organization_request_topic` (`id`, `name`) VALUES
(1, 'Прочее');

-- --------------------------------------------------------

--
-- Структура таблицы `region`
--

DROP TABLE IF EXISTS `region`;
CREATE TABLE IF NOT EXISTS `region` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `region`
--

INSERT INTO `region` (`id`, `name`) VALUES
(20, 'Москва'),
(21, 'Питер'),
(22, 'Московска область');

-- --------------------------------------------------------

--
-- Структура таблицы `resource`
--

DROP TABLE IF EXISTS `resource`;
CREATE TABLE IF NOT EXISTS `resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` varchar(30) NOT NULL,
  `controller` varchar(50) NOT NULL DEFAULT 'index',
  `action` varchar(50) NOT NULL DEFAULT 'index',
  `module` varchar(30) NOT NULL DEFAULT 'main',
  `description` varchar(200) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `controller_3` (`controller`,`action`,`module`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `resource`
--

INSERT INTO `resource` (`id`, `group`, `controller`, `action`, `module`, `description`, `created_at`) VALUES
(1, 'acl', 'index', 'index', 'backend', 'Просмотр главной страницы в админке', '2015-02-12 00:10:57'),
(6, 'acl', 'user', 'delete', 'backend', 'Удаление пользователя', '2015-02-11 22:14:25'),
(9, 'base', 'errors', 'show401', 'backend', 'Страница 401', '2015-02-12 17:06:43'),
(10, 'acl', 'newslist', 'index', 'backend', 'Просмотр страницы со списком новостей', '2015-02-12 17:17:09'),
(11, 'acl', 'news', 'save', 'backend', 'Сохранение (создание\\изменение) новости', '2015-02-12 17:17:09'),
(12, 'acl', 'news', 'delete', 'backend', 'Удаление новости', '2015-02-12 17:17:43'),
(13, 'acl', 'userrolelist', 'index', 'backend', 'Просмотр страницы со списком ролей', '2015-02-12 17:25:14'),
(19, 'acl', 'userlist', 'index', 'backend', 'Просмотр страницы со списком пользователей', '2015-02-12 18:06:32'),
(22, 'base', 'errors', 'show404', 'backend', 'Страница 404', '2015-02-12 17:06:43'),
(23, 'base', 'session', 'end', 'backend', 'Завершение сессии', '2015-02-14 23:33:15'),
(24, 'base', 'login', 'index', 'backend', 'Форма авторизации в СЧ', '2015-02-17 14:09:37'),
(25, 'acl', 'news', 'activate', 'backend', 'Изменение свойства активности у новости', '2015-02-18 18:27:01'),
(26, 'acl', 'news', 'edit', 'backend', 'Редактирование новости', '2015-02-18 18:29:08'),
(29, 'acl', 'news', 'add', 'backend', 'Добавление новости', '2015-02-18 18:29:08'),
(32, 'acl', 'result', 'edit', 'backend', 'Редактирование результата', '2015-02-18 18:29:08'),
(33, 'acl', 'result', 'delete', 'backend', 'Удаление результата', '2015-02-12 17:17:43'),
(34, 'acl', 'resultlist', 'index', 'backend', 'Просмотр страницы со списком результатов', '2015-02-12 17:17:09'),
(36, 'acl', 'result', 'add', 'backend', 'Добавление результата', '2015-02-18 18:29:08'),
(37, 'acl', 'result', 'save', 'backend', 'Сохранение (создание\\изменение) данных результата', '2015-02-03 22:25:53'),
(38, 'acl', 'result', 'upload', 'backend', 'Загрузка файла для результата', '2015-02-11 22:14:25'),
(39, 'acl', 'file', 'delete', 'backend', 'Удаление файла', '2015-02-12 17:17:43'),
(40, 'acl', 'file', 'upload', 'backend', 'Загрузка файла для какой-либо сущности', '2015-02-11 22:14:25'),
(41, 'acl', 'settinglist', 'index', 'backend', 'Просмотр страницы со списком настроек', '2015-02-12 17:17:09'),
(42, 'acl', 'setting', 'save', 'backend', 'Сохранение данных настройки', '2015-02-03 22:25:53'),
(43, 'acl', 'objectcategorylist', 'index', 'backend', 'Просмотр страницы со списком категорий объектов', '2015-02-12 17:17:09'),
(48, 'base', 'errors', 'show500', 'frontend', 'Страница 500', '2015-02-12 17:06:43'),
(49, 'base', 'errors', 'show404', 'frontend', 'Страница 404', '2015-02-12 17:06:43'),
(50, 'acl', 'objectlist', 'index', 'backend', 'Просмотр страницы со списком объектов', '2015-02-12 17:17:09'),
(51, 'acl', 'object', 'edit', 'backend', 'Редактирование объекта', '2015-02-18 18:29:08'),
(52, 'acl', 'object', 'add', 'backend', 'Добавление объекта', '2015-02-18 18:29:08'),
(53, 'acl', 'object', 'save', 'backend', 'Сохранение (создание\\изменение) данных объекта', '2015-02-03 22:25:53'),
(54, 'acl', 'object', 'delete', 'backend', 'Удаление объекта', '2015-02-12 17:17:43'),
(55, 'acl', 'objectcategory', 'save', 'backend', 'Сохранение (изменение) категории объекта', '2015-02-12 17:25:14'),
(56, 'acl', 'objectcategory', 'add', 'backend', 'Добавление категории объекта', '2015-02-18 18:29:08'),
(57, 'acl', 'objectcategory', 'delete', 'backend', 'Удаление категории объекта', '2015-02-12 17:17:43'),
(58, 'acl', 'objectcategory', 'edit', 'backend', 'Редактирование категории объекта', '2015-02-18 18:29:08'),
(59, 'acl', 'objectcategory', 'active', 'backend', 'Изменение свойства активности у категории объекта', '2015-02-18 18:27:01'),
(60, 'acl', 'organizationlist', 'index', 'backend', 'Просмотр страницы со списком организаций', '2015-02-12 18:06:32'),
(61, 'acl', 'organization', 'edit', 'backend', 'Редактирование организации', '2015-02-18 18:29:08'),
(62, 'acl', 'organization', 'delete', 'backend', 'Удаление организации', '2015-02-12 17:17:43'),
(63, 'acl', 'organization', 'add', 'backend', 'Добавление организации', '2015-02-18 18:29:08'),
(64, 'acl', 'organizationlist', 'filter', 'backend', 'Фильтрация списка организаций', '2015-02-12 18:06:32'),
(66, 'acl', 'organization', 'getdata', 'backend', 'Получение полных данных организации', '2015-02-18 18:29:08'),
(67, 'acl', 'expense', 'add', 'backend', 'Добавление расхода', '2015-02-18 18:29:08'),
(68, 'acl', 'expense', 'delete', 'backend', 'Удаление расхода', '2015-02-12 17:17:43'),
(69, 'acl', 'expense', 'edit', 'backend', 'Редактирование расхода', '2015-02-18 18:29:08'),
(70, 'acl', 'expenselist', 'filter', 'backend', 'Фильтрация списка расходов', '2015-02-12 18:06:32'),
(71, 'acl', 'expense', 'getdata', 'backend', 'Получение полных данных расхода', '2015-02-18 18:29:08'),
(72, 'acl', 'userlist', 'filter', 'backend', 'Фильтрация списка пользователей', '2015-02-12 18:06:32'),
(73, 'acl', 'user', 'edit', 'backend', 'Редактирование пользователя', '2015-02-11 22:14:25'),
(74, 'acl', 'user', 'getdata', 'backend', 'Получение полных данных пользователя', '2015-02-18 18:29:08'),
(75, 'acl', 'userrolelist', 'filter', 'backend', 'Фильтрация списка ролей пользователей', '2015-02-12 18:06:32'),
(76, 'acl', 'userrole', 'edit', 'backend', 'Редактирование роли пользователя', '2015-02-11 22:14:25'),
(77, 'acl', 'userrole', 'getdata', 'backend', 'Получение полных данных роли пользователя', '2015-02-18 18:29:08'),
(78, 'acl', 'resource', 'getdata', 'backend', 'Получение полных данных ресурса системы', '2015-02-18 18:29:08'),
(79, 'acl', 'resourcelist', 'filter', 'backend', 'Фильтрация списка ресурсов системы', '2015-02-12 18:06:32'),
(80, 'acl', 'resourcelist', 'index', 'backend', 'Просмотр страницы со списком ресурсов системы', '2015-02-12 18:06:32'),
(81, 'acl', 'organization', 'save', 'backend', 'Сохранение (создание\\изменение) организации сразу на сервер', '2015-02-12 17:17:09'),
(82, 'acl', 'expensetypelist', 'index', 'backend', 'Просмотр списка типов расходов', '2015-02-12 17:25:14'),
(83, 'acl', 'expensetypelist', 'filter', 'backend', 'Фильтрация списка типов расходов', '2015-02-12 18:06:32'),
(84, 'acl', 'expensetype', 'edit', 'backend', 'Редактирование типа расхода', '2015-02-18 18:29:08'),
(85, 'acl', 'expensetype', 'getdata', 'backend', 'Получение полных данных типа расхода', '2015-02-18 18:29:08'),
(86, 'acl', 'setting', 'edit', 'backend', 'Редактирование настройки', '2015-02-11 22:14:25'),
(87, 'acl', 'setting', 'getdata', 'backend', 'Получение полных данных настройки', '2015-02-18 18:29:08'),
(88, 'acl', 'settinglist', 'filter', 'backend', 'Фильтрация списка настроек', '2015-02-12 18:06:32'),
(90, 'acl', 'user', 'add', 'backend', 'Добавление пользователя', '2015-02-18 18:29:08'),
(92, 'acl', 'user', 'save', 'backend', 'Сохранение (создание\\изменение) пользователя сразу на сервер', '2015-02-12 17:17:09'),
(93, 'acl', 'resource', 'save', 'backend', 'Сохранение (создание\\изменение) ресурса системы сразу на сервер', '2015-02-12 17:17:09'),
(95, 'acl', 'resource', 'edit', 'backend', 'Редактирование ресурса системы', '2015-02-11 22:14:25'),
(96, 'acl', 'resource', 'delete', 'backend', 'Удаление ресурса системы', '2015-02-11 22:14:25'),
(97, 'acl', 'resource', 'add', 'backend', 'Добавление ресурса системы', '2015-02-18 18:29:08'),
(98, 'acl', 'userrole', 'add', 'backend', 'Добавление роли пользователя', '2015-02-18 18:29:08'),
(99, 'acl', 'userrole', 'delete', 'backend', 'Удаление роли пользователя', '2015-02-12 17:17:43'),
(100, 'acl', 'userrole', 'save', 'backend', 'Сохранение (создание\\изменение) роли пользователя сразу на сервер', '2015-02-12 17:17:09'),
(102, 'acl', 'expensetype', 'add', 'backend', 'Добавление типа расхода', '2015-02-18 18:29:08'),
(103, 'acl', 'organization', 'upload', 'backend', 'Загрузка изображения для организации', '2016-09-16 19:19:27'),
(104, 'acl', 'expense', 'save', 'backend', 'Сохранение (создание\\изменение) расхода', '2016-09-23 19:28:21'),
(105, 'base', 'index', 'index', 'frontend', 'Главная страница публичной части', '2016-11-11 14:09:48'),
(106, 'base', 'login', 'index', 'frontend', 'Страница авторизации публичной части', '2016-11-11 15:30:07'),
(107, 'base', 'session', 'end', 'frontend', '', '2016-11-11 17:17:57'),
(109, 'base', 'session', 'start', 'backend', '', '2016-11-11 17:20:34'),
(110, 'base', 'session', 'start', 'frontend', '', '2016-11-11 17:20:53'),
(111, 'base', 'expensetypelist', 'index', 'frontend', '', '2016-11-11 19:31:41'),
(112, 'base', 'expensetype', 'index', 'frontend', '', '2016-11-11 19:55:49'),
(113, 'acl', 'requestlist', 'index', 'frontend', '', '2016-11-11 20:05:27'),
(114, 'base', 'expenselist', 'index', 'frontend', '', '2016-11-12 19:38:45'),
(115, 'base', 'expenselist', 'filter', 'frontend', '', '2016-11-13 01:55:42'),
(116, 'base', 'about', 'index', 'frontend', '', '2016-11-13 11:14:42'),
(117, 'base', 'newslist', 'index', 'frontend', '', '2016-11-13 12:18:33'),
(118, 'base', 'news', 'index', 'frontend', '', '2016-11-13 14:56:29'),
(119, 'base', 'organization', 'index', 'frontend', '', '2016-11-13 16:30:13'),
(120, 'base', 'organizationrequest', 'getdata', 'frontend', '', '2016-11-19 23:56:00'),
(121, 'acl', 'expensetype', 'save', 'backend', '', '2016-11-20 17:39:33'),
(122, 'base', 'organizationrequest', 'save', 'frontend', '', '2016-11-20 17:56:30');

-- --------------------------------------------------------

--
-- Структура таблицы `result`
--

DROP TABLE IF EXISTS `result`;
CREATE TABLE IF NOT EXISTS `result` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf16 NOT NULL COMMENT 'Название попроса',
  `description` varchar(500) CHARACTER SET utf16 NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `created_by` int(10) UNSIGNED NOT NULL COMMENT 'user_id',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `result`
--

INSERT INTO `result` (`id`, `name`, `description`, `active`, `created_by`, `created_at`, `published_at`) VALUES
(2, 'фывф2', '<p>фывфыkas</p>\n', 1, 22, '2015-05-26 17:10:17', '2015-06-02 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `result_file`
--

DROP TABLE IF EXISTS `result_file`;
CREATE TABLE IF NOT EXISTS `result_file` (
  `result_id` int(11) UNSIGNED NOT NULL,
  `file_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`result_id`,`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `result_file`
--

INSERT INTO `result_file` (`result_id`, `file_id`) VALUES
(2, 20),
(2, 21),
(2, 22);

-- --------------------------------------------------------

--
-- Структура таблицы `setting`
--

DROP TABLE IF EXISTS `setting`;
CREATE TABLE IF NOT EXISTS `setting` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `value` varchar(500) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `setting`
--

INSERT INTO `setting` (`id`, `code`, `value`, `description`) VALUES
(2, 'logo_img', 'img/index-logo.png', NULL),
(3, 'use_points_img', 'img/obmen_polls_in_main.png', NULL),
(4, 'poll_img', 'img/proyti_opros_in_main.png', NULL),
(5, 'register_img', 'img/reg_in_main.png', NULL),
(6, 'admin_table_limit', '100', 'Ограничение количества строк для таблиц в служебной части'),
(11, 'files_upload_directory', 'upload/files/', 'Каталог, в который должны загружаться файлы сущностей: опросов, вопросов, результатов и т.д. В конце обязательно указание символа &#34;/&#34;'),
(12, 'result_upload_directory', 'results/', 'Каталог, в который должны загружаться файлы, привязанные к результатам. В конце обязательно указание символа &#34;/&#34;'),
(13, 'acl_routes_enable', '1', 'Контролировать ли доступ к страницам'),
(14, 'admin_table_page_sizes', '[30,50,100]', 'Ограничение количества строк для таблиц в служебной части');

-- --------------------------------------------------------

--
-- Структура таблицы `status`
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_code` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `status`
--

INSERT INTO `status` (`id`, `name_code`) VALUES
(1, 'status_new'),
(2, 'status_processed'),
(3, 'status_in_progress'),
(4, 'status_declined');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `password` char(50) CHARACTER SET utf8 NOT NULL,
  `phone` char(15) CHARACTER SET utf8 NOT NULL,
  `email` char(50) CHARACTER SET utf8 DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'ФИО',
  `points` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_role_id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `password`, `phone`, `email`, `name`, `points`, `user_role_id`, `active`, `created_at`) VALUES
(20, '40bd001563085fc35165329ea1ff5c5ecbdbbeef', '1111111111', 'admin@admin.ru', 'Его величество', 0, 1, 1, '2015-02-06 18:40:36'),
(22, 'f4542db9ba30f7958ae42c113dd87ad21fb2eddb', '3333333333', 'guest', 'Гость', 0, 2, 1, '2015-02-10 14:34:30'),
(25, 'da39a3ee5e6b4b0d3255bfef95601890afd80709', '1111111122', 'asd@asd1.ru', 'asdasd', 0, 1, 1, '2015-02-10 23:07:09'),
(32, 'f4542db9ba30f7958ae42c113dd87ad21fb2eddb', '12312312345', 'oper@oper.ru', 'sdfsdad', 0, 5, 1, '2015-02-12 14:36:29'),
(34, '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'q123123', 'asd@asd.ru', '123123123', 0, 4, 1, '2016-10-25 20:17:10');

-- --------------------------------------------------------

--
-- Структура таблицы `user_organization`
--

DROP TABLE IF EXISTS `user_organization`;
CREATE TABLE IF NOT EXISTS `user_organization` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `organization_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`,`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `user_organization`
--

INSERT INTO `user_organization` (`user_id`, `organization_id`) VALUES
(20, 22),
(22, 2),
(32, 22);

-- --------------------------------------------------------

--
-- Структура таблицы `user_role`
--

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `user_role`
--

INSERT INTO `user_role` (`id`, `name`, `active`, `created_at`) VALUES
(1, 'Администратор', 1, '2014-12-22 09:55:51'),
(2, 'Гость', 1, '2014-12-22 09:56:53'),
(3, 'Авторизованный пользователь', 1, '2015-02-04 17:55:50'),
(4, 'Новостной редактор', 1, '2015-02-13 10:11:15'),
(5, 'Оператор', 1, '2015-02-14 23:28:04');

-- --------------------------------------------------------

--
-- Структура таблицы `user_role_resource`
--

DROP TABLE IF EXISTS `user_role_resource`;
CREATE TABLE IF NOT EXISTS `user_role_resource` (
  `user_role_id` int(11) UNSIGNED NOT NULL,
  `resource_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_role_id`,`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `user_role_resource`
--

INSERT INTO `user_role_resource` (`user_role_id`, `resource_id`) VALUES
(3, 1),
(3, 9),
(3, 22),
(3, 23),
(3, 24),
(4, 1),
(4, 9),
(4, 10),
(4, 11),
(4, 12),
(4, 22),
(4, 23),
(4, 24),
(4, 25),
(4, 26),
(4, 29),
(5, 1),
(5, 9),
(5, 22),
(5, 23),
(5, 24);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
