-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Янв 20 2017 г., 07:22
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
  `expense_type_id` int(10) NOT NULL COMMENT 'Идентификатор типа расхода',
  `expense_status_id` int(11) NOT NULL,
  `organization_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Идентификатор организации, к которой относится расход',
  `settlement` varchar(255) DEFAULT NULL,
  `street_type_id` int(10) UNSIGNED DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `house` varchar(20) DEFAULT NULL,
  `executor` varchar(255) DEFAULT NULL,
  `target_date_from` date DEFAULT NULL,
  `target_date_to` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `expense`
--

INSERT INTO `expense` (`id`, `name`, `amount`, `expense_type_id`, `expense_status_id`, `organization_id`, `settlement`, `street_type_id`, `street`, `house`, `executor`, `target_date_from`, `target_date_to`) VALUES
(72, 'Капитальный ремонт', 500, 3, 2, 38, NULL, 2, 'Ануфриева', '10', 'ООО «Стройдом»', NULL, '2016-12-12'),
(73, 'Капитальный ремонт', 550, 3, 2, 38, NULL, 2, 'Вокзальная', '3', 'ООО «Стройдом»', NULL, '2016-11-29'),
(74, 'Капальный ремонт', 600, 3, 2, 38, NULL, 2, 'Геологов', '5', 'ООО «Стройдом»', NULL, '2016-12-28'),
(75, 'Капитальный ремонт', 320, 3, 2, 38, NULL, 2, 'Институтская', '4', 'ООО «Стройдом»', NULL, '2016-12-20'),
(76, 'Капитальный ремонт', 450, 3, 1, 38, NULL, 2, 'Институтская', '26', 'ООО «Стройдом»', NULL, '2016-12-22'),
(77, 'Капитальный ремонт', 600, 3, 1, 38, NULL, 2, 'Коссович', '12', 'ООО «Стройдом»', NULL, '2017-01-17'),
(78, 'Ремонт канализации', 250, 3, 1, 38, NULL, 2, 'Лермонтова', '2', 'ООО &#34;АВАНС&#34;', NULL, '2017-01-24'),
(79, 'Ремонт канализации', 300, 3, 1, 38, NULL, 2, 'Лермонтова', '55', 'ООО &#34;АВАНАС&#34;', NULL, '2017-01-17'),
(80, 'Ремонт крыши', 150, 3, 1, 38, NULL, 2, 'Лермонтова', '64', 'ООО&#34;ТРЕСТ&#34;', NULL, '2016-11-30'),
(81, 'Ремонт крыши', 200, 3, 1, 38, NULL, 2, 'Октябрьская', '23', 'ООО &#34;ТРЕСТ&#34;', NULL, '2016-11-30'),
(82, 'Строительство детской площадки', 750, 3, 2, 38, NULL, 2, 'Свердлова', '4', 'ЗАО &#34;ИНЖТЕХ&#34;', NULL, '2016-11-29'),
(83, 'Ремонт школы №17', 6000, 3, 2, 38, NULL, 2, 'Терешковой', '5', 'ООО &#34;РЕМОНТ-СЕРВИС&#34;', NULL, '2016-11-29'),
(84, 'Ремонт фасада здания', 700, 3, 1, 38, NULL, 2, 'Энтузиастов', '56', 'ЗАО &#34;СТРОЙИНВЕСТ&#34;', NULL, '2016-11-29'),
(85, 'Кладка тротуарной плитки', 15000, 4, 3, 38, NULL, 2, 'Ануфриева', '8-54', 'ООО &#34;ГАРАНТ&#34;', NULL, '2016-12-13'),
(86, 'Организация фестиваля красок', 300, 6, 3, 38, NULL, 2, '-', '-', 'ООО &#34;ПРАЗДНИК&#34;', NULL, '2016-11-30'),
(87, 'Ремонт дороги', 600, 4, 2, 38, NULL, 2, 'Волкова', '10-34', 'МУП &#34;РЕМОНТ&#34;', NULL, '2016-11-30'),
(88, 'Закупка канцтоваров', 400, 7, 3, 38, NULL, 2, '-', '-', '-', NULL, '2016-11-30'),
(89, 'Ремонт городской больницы №3', 7000, 5, 3, 38, NULL, 2, 'Маркова', '16', 'ООО &#34;ГАРАНТ&#34;', NULL, '2016-11-30'),
(90, 'Закупка учебной литературы для школы №2', 2000, 1, 3, 38, NULL, 2, 'Волкова', '4', '-', NULL, '2016-11-30'),
(91, 'Содержание столовой для малоимущих', 1500, 8, 3, 38, NULL, 2, 'Космонавтов', '5', 'ООО &#34;ОБЕД&#34;', NULL, '2016-12-06'),
(92, 'Закупка оргтехники', 4000, 7, 3, 38, NULL, 2, 'Ленина', '69', 'ООО &#34;КОМПСЕРВИС&#34;', NULL, '2016-11-30'),
(93, 'Организация лыжной дорожки', 1200, 6, 2, 38, NULL, 2, 'Лесная', '-', 'ООО &#34;Снег&#34;', NULL, '2016-12-14'),
(94, 'Организация футбольного турнира &#34;Кубок&#34;', 3000, 6, 3, 38, NULL, 2, 'Владимирская', '41', 'ООО &#34;ФУТЗАЛ&#34;', NULL, '2016-11-30'),
(95, 'Выплата ветеранам ВОВ', 2500, 8, 3, 38, NULL, 2, '-', '-', '-', NULL, '2016-11-30'),
(96, 'Закупка камер видеонаблюдения для города', 3000, 10, 3, 38, NULL, NULL, '-', '-', 'ИП &#34;ГАРАНТЗАЩИТА&#34;', NULL, '2016-12-01'),
(99, 'Поддержка предпринимателей в 4 кв.', 4000, 9, 3, 38, NULL, NULL, NULL, NULL, NULL, NULL, '2016-12-01'),
(100, 'asd', 11001, 10, 3, 46, 'qwe', NULL, NULL, NULL, NULL, '2016-12-07', '2016-12-08'),
(101, 'asd', 167, 10, 3, 38, 'asd', NULL, NULL, NULL, NULL, NULL, NULL),
(102, 'asd', 12, 10, 3, 38, NULL, NULL, NULL, NULL, NULL, '2017-01-12', '2017-01-13');

-- --------------------------------------------------------

--
-- Структура таблицы `expense_status`
--

DROP TABLE IF EXISTS `expense_status`;
CREATE TABLE IF NOT EXISTS `expense_status` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `expense_status`
--

INSERT INTO `expense_status` (`id`, `name`) VALUES
(1, 'Не начато'),
(2, 'Начато'),
(3, 'Выполнено');

-- --------------------------------------------------------

--
-- Структура таблицы `expense_type`
--

DROP TABLE IF EXISTS `expense_type`;
CREATE TABLE IF NOT EXISTS `expense_type` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `expense_type`
--

INSERT INTO `expense_type` (`id`, `name`) VALUES
(1, 'Образование'),
(2, 'Прочие расходы'),
(3, 'Жилищно-коммунальный фонд'),
(4, 'Дорожно-транспортная система'),
(5, 'Здравоохранение'),
(6, 'Культура, спорт, отдых'),
(7, 'Собственные расходы'),
(8, 'Социальная поддержка'),
(9, 'Экономика'),
(10, 'Безопасность');

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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `file`
--

INSERT INTO `file` (`id`, `name`, `directory`) VALUES
(8, 'organization_46_30623.png', 'upload/files/organization/46/'),
(9, 'organization_45_7990.png', 'upload/files/organization/45/'),
(10, 'organization_44_28215.png', 'upload/files/organization/44/'),
(11, 'organization_42_3591.png', 'upload/files/organization/42/'),
(12, 'organization_43_19252.png', 'upload/files/organization/43/'),
(13, 'organization_41_3421.png', 'upload/files/organization/41/'),
(14, 'organization_40_14034.png', 'upload/files/organization/40/'),
(15, 'organization_36_931.png', 'upload/files/organization/36/'),
(16, 'organization_35_26272.png', 'upload/files/organization/35/'),
(17, 'organization_34_8793.png', 'upload/files/organization/34/'),
(18, 'organization_33_28190.png', 'upload/files/organization/33/'),
(19, 'organization_30_14079.png', 'upload/files/organization/30/'),
(20, 'organization_28_19660.png', 'upload/files/organization/28/'),
(21, 'organization_27_2837.png', 'upload/files/organization/27/'),
(22, 'organization_26_20010.png', 'upload/files/organization/26/'),
(23, 'organization_1_17435.png', 'upload/files/organization/1/'),
(24, 'organization_25_27064.png', 'upload/files/organization/25/'),
(25, 'organization_24_17297.png', 'upload/files/organization/24/'),
(26, 'organization_23_8950.png', 'upload/files/organization/23/'),
(27, 'organization_22_1783.png', 'upload/files/organization/22/'),
(28, 'organization_21_26980.png', 'upload/files/organization/21/'),
(29, 'organization_2_1997.png', 'upload/files/organization/2/'),
(31, 'organization_38_30914.png', 'upload/files/organization/38/'),
(32, 'organization_47_13491.jpg', 'upload/files/organization/47/'),
(33, 'organization_47_12369.jpg', 'upload/files/organization/47/'),
(36, 'organization_49_8500.jpg', 'upload/files/organization/49/'),
(37, 'organization_55_11101.jpg', 'upload/files/organization/55/');

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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `file_collection`
--

INSERT INTO `file_collection` (`id`, `collection_id`, `file_id`) VALUES
(2, 2, 2),
(3, 3, 3),
(4, 3, 4),
(5, 3, 5),
(6, 4, 6),
(8, 6, 8),
(9, 7, 9),
(10, 8, 10),
(11, 9, 11),
(12, 10, 12),
(13, 11, 13),
(14, 12, 14),
(15, 13, 15),
(16, 3, 16),
(17, 2, 17),
(18, 14, 18),
(19, 15, 19),
(20, 16, 20),
(21, 17, 21),
(22, 4, 22),
(23, 18, 23),
(24, 19, 24),
(25, 20, 25),
(26, 21, 26),
(27, 1, 27),
(28, 22, 28),
(29, 23, 29),
(31, 5, 31),
(32, 24, 32),
(33, 25, 33),
(34, 26, 34),
(35, 27, 35),
(36, 27, 36),
(37, 28, 37);

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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `name`, `description`, `publication_date`, `active`, `created_by`, `created_at`) VALUES
(21, 'Интернет портал общедоступной информации о расходах муниципальных образований «Расходы города».', 'Проект реализуется Автономной некоммерческой организации\n«Центр мониторинга и организации взаимодействия населения и органов государственной власти в сфере предоставления общедоступной информации о финансовых ресурсах муниципальных образований».', '2016-11-27', 1, 20, '2015-02-19 10:45:02'),
(24, 'О проекте', 'В настоящее время остро стоит проблема о недостаточной информированности населения о деятельности органов местного самоуправления в плане  распределения финансовых ресурсов, которая  вносит недоверие во взаимоотношения между государством и обществом. Наш проект  предполагает  разработку, внедрение, продвижение и техническое сопровождение удобной интернет платформы, где представители органов местного самоуправления будут выкладывать информацию о расходах своего города по различным направлениям.', '2016-11-27', 1, 20, '2016-11-27 14:40:02'),
(25, 'Цели проекта', 'Цели проекта:\n- создание условий для всестороннего и доверительного взаимодействия между гражданами и органами государственной власти в лице местного самоуправления муниципальных образований Российской Федерации; \n  - повышение социальной активности населения РФ.', '2016-11-27', 1, 20, '2016-11-27 14:42:14');

-- --------------------------------------------------------

--
-- Структура таблицы `organization`
--

DROP TABLE IF EXISTS `organization`;
CREATE TABLE IF NOT EXISTS `organization` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf16 NOT NULL COMMENT 'Название организации',
  `contacts` varchar(500) CHARACTER SET utf16 DEFAULT NULL,
  `region_id` int(10) NOT NULL,
  `email` varchar(255) CHARACTER SET utf16 DEFAULT NULL,
  `img` varchar(255) CHARACTER SET utf16 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `organization`
--

INSERT INTO `organization` (`id`, `name`, `contacts`, `region_id`, `email`, `img`) VALUES
(1, 'Петушки', '601143, Владимирская область, город Петушки, улица Новая, дом 8, телефон: (49243) 2-11-98', 20, 'office.petush@mail.ru', '18'),
(2, 'Муром', '602256 , Владимирская обл.,г.Муром,пл.1100-летия, д.1, телефон:  7(49234) 3-11-02', 20, 'post@murom.info', '23'),
(21, 'Меленки', '602102, Владимирская область, Меленковский район, г. Меленки, ул. Красноармейская, д. 102, телефон: 8 (49247) 2-25-39', 20, 'city@melenky.ru', '22'),
(22, 'Лакинск', '601240, Владимирская область, Собинский район, город Лакинск, улица Горького, дом 20, телефон: 4-13-48', 20, 'lakinsk@lakinskmo.ru', '1'),
(23, 'Курлово', '601570, Владимирская область, Гусь-Хрустальный район, г. Курлово, ул. Советская, д. 8, телефон:  7 (492 41) 5-53-43', 20, 'glavakurlovo@yandex.ru', '21'),
(24, 'Костерёво', '601111, Владимирская область, Петушинский район, г. Костерево, ул. Горького, д. 2, телефон: 8 (49243) 4-24-49', 20, 'kosterevo_adm@mail.ru', '20'),
(25, 'Кольчугино', '601785, Владимирская область, Кольчугино, ул. Ульяновская, 38, телефон: (49245) 4-07-77', 20, 'kolch@avo.ru', '19'),
(26, 'Ковров', '601900, Владимирская область, г.Ковров, ул.Краснознаменная, д.6, телефон: (49232) 3-11-35', 20, 'kovrov@kovrov.ru', '4'),
(27, 'Киржач', '601021, Владимирская обл., г. Киржач, микрорайон Красный Октябрь, ул. Пушкина, 8 Б, телефон:  7 (49237) 6-12-26', 20, 'adm@gorodkirzhach.ru', '17'),
(28, 'Карабаново', '601642, Владимирская область, Алекандровский район, город Карабаново, пл. Лермонтова, д.1а, телефон:	 (49244) 5-12-73', 20, 'adminkar@mail.ru', '16'),
(30, 'Камешково', '601300, Владимирская обл., г. Камешково,  ул. Свердлова, д. 10, телефон: (49248) 2–12–07', 20, 'kamesr@avo.ru', '15'),
(33, 'Гусь-Хрустальный', '601501, Владимирская область, г. Гусь-Хрустальный, ул. Калинина, 28. Телефон: (49241)2-68-11', 20, 'gus@avo.ru', '14'),
(34, 'Гороховец', '601481, Владимирская  область, г. Гороховец, Ленина ул.,  д. 93, телефон : (49238) 2-15-65', 20, 'goroxr@avo.ru', '2'),
(35, 'Вязники', '601440, Владимирская область, ул. Комсомольская, дом 3, телефон: 8 (492) 333-05-66', 20, 'vyazn@avo.ru', '3'),
(36, 'Владимир', '600000, г. Владимир ул. Горького, 36  Тел. (4922) 53-28-17', 20, 'info@vladimir-city.ru', '13'),
(38, 'Александров', '601657, Владимирская область, г. Александров ул. Ленина, дом 69,телефон: 8 (49244) 2-20-05', 20, 'aleksandrov@trytek.ru', '5'),
(40, 'Покров', '601120, Владимирская область, Петушинский район, город Покров, ул. Cоветская,  42 тел.:  7 (49243) 6-21-11', 20, 'info@pokrovcity.ru', '12'),
(41, 'Радужный', '600910, Владимирская область, г. Радужный, квартал, дом 55  тел.:  7 (49254) 3-29-20', 20, 'radugn@avo.ru', '11'),
(42, 'Собинка', '601204, Владимирская область, Собинский район, г. Собинка, ул. Димитрова, д. 1', 20, 'sobin@avo.ru', '9'),
(43, 'Струнино', '601671, Владимирская область, Александровский район, г. Струнино, ул. Воронина, д.1.', 20, 'adm331601@mail.ru', '10'),
(44, 'Судогда', '601352, Владимирская область, г. Судогда ул.Ленина д.65  (49235) 2-26-62, 2-19-15', 20, 'admsudogda@mail.ru', '8'),
(45, 'Суздаль', '601293, Владимирская область, Суздаль, Красная площадь, д.1 тел. (49231) 2-07-15', 20, 'info@suzdalregion.ru', '7'),
(46, 'Юрьев-Польский', '601800, Владимирская обл., г. Юрьев-Польский, ул. Шибанкова, д. 33., телефон:  7 (49246) 2-24-35', 20, 'yurier@avo.ru', '6');

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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `region`
--

INSERT INTO `region` (`id`, `name`) VALUES
(20, 'Владимирская область'),
(21, 'Астраханская область');

-- --------------------------------------------------------

--
-- Структура таблицы `request_status`
--

DROP TABLE IF EXISTS `request_status`;
CREATE TABLE IF NOT EXISTS `request_status` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_code` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `request_status`
--

INSERT INTO `request_status` (`id`, `name_code`) VALUES
(1, 'status_new'),
(2, 'status_processed'),
(3, 'status_in_progress'),
(4, 'status_declined');

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
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf16;

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
(48, 'base', 'errors', 'show500', 'frontend', 'Страница 500', '2015-02-12 17:06:43'),
(49, 'base', 'errors', 'show404', 'frontend', 'Страница 404', '2015-02-12 17:06:43'),
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
(122, 'base', 'organizationrequest', 'save', 'frontend', '', '2016-11-20 17:56:30'),
(123, 'acl', 'regionlist', 'index', 'backend', '', '2016-11-23 23:24:11'),
(124, 'acl', 'region', 'edit', 'backend', '', '2016-11-23 23:27:59'),
(125, 'acl', 'region', 'save', 'backend', '', '2016-11-23 23:28:12'),
(126, 'acl', 'region', 'getdata', 'backend', '', '2016-11-23 23:36:44'),
(127, 'acl', 'region', 'add', 'backend', '', '2016-11-23 23:41:47'),
(128, 'acl', 'region', 'delete', 'backend', '', '2016-11-23 23:42:42'),
(129, 'acl', 'news', 'getdata', 'backend', '', '2016-11-24 00:25:39'),
(130, 'acl', 'streettypelist', 'index', 'backend', '', '2016-11-25 19:45:35'),
(131, 'acl', 'streettype', 'edit', 'backend', '', '2016-11-25 19:45:59'),
(132, 'acl', 'streettype', 'save', 'backend', '', '2016-11-25 19:46:27'),
(133, 'acl', 'streettype', 'delete', 'backend', '', '2016-11-25 19:46:45'),
(134, 'acl', 'streettype', 'add', 'backend', '', '2016-11-25 19:46:55'),
(135, 'acl', 'streettypelist', 'filter', 'backend', '', '2016-11-25 19:48:27'),
(136, 'base', 'organizationrequest', 'edit', 'frontend', '', '2016-11-27 01:11:44'),
(137, 'acl', 'expensestatuslist', 'index', 'backend', '', '2016-11-27 21:41:23'),
(138, 'acl', 'expensestatuslist', 'filter', 'backend', '', '2016-11-27 21:41:34'),
(139, 'base', 'user', 'sendpassword', 'backend', '', '2016-12-03 01:22:14'),
(140, 'acl', 'profile', 'edit', 'backend', '', '2016-12-03 01:41:23'),
(141, 'acl', 'organization', 'show', 'backend', '', '2016-12-10 20:30:17'),
(143, 'acl', 'organization', 'index', 'backend', '', '2016-12-10 20:49:58'),
(144, 'acl', 'organization_expenselist', 'show', 'backend', 'Скроллер расходов в организации', '2016-12-20 23:32:49'),
(145, 'acl', 'expenselist', 'index', 'backend', '', '2016-12-21 00:06:34'),
(146, 'acl', 'userlist', 'show', 'backend', '', '2016-12-21 00:07:55'),
(147, 'acl', 'user', 'show', 'backend', '', '2016-12-21 00:11:24'),
(148, 'acl', 'expenselist', 'show', 'backend', '', '2016-12-21 00:12:40'),
(149, 'acl', 'expense', 'show', 'backend', '', '2016-12-21 00:12:58'),
(150, 'acl', 'userrole', 'index', 'backend', '', '2016-12-21 00:16:42'),
(151, 'acl', 'userrole', 'show', 'backend', '', '2016-12-21 00:16:53'),
(152, 'acl', 'resource', 'index', 'backend', '', '2016-12-21 00:18:39'),
(153, 'acl', 'resource', 'show', 'backend', '', '2016-12-21 00:18:53'),
(154, 'acl', 'streettype', 'show', 'backend', '', '2016-12-21 00:20:23'),
(155, 'acl', 'streettype', 'index', 'backend', '', '2016-12-21 00:20:40'),
(156, 'acl', 'streettypelist', 'show', 'backend', '', '2016-12-21 00:20:55'),
(157, 'acl', 'expensetypelist', 'show', 'backend', '', '2016-12-21 00:22:16'),
(158, 'acl', 'expensetype', 'show', 'backend', '', '2016-12-21 00:22:43'),
(159, 'acl', 'expensetype', 'index', 'backend', '', '2016-12-21 00:22:57'),
(160, 'base', 'regionlist', 'filter', 'backend', '', '2016-12-21 00:23:30'),
(161, 'acl', 'regionlist', 'show', 'backend', '', '2016-12-21 00:23:41'),
(162, 'acl', 'region', 'show', 'backend', '', '2016-12-21 00:23:59'),
(163, 'acl', 'region', 'index', 'backend', '', '2016-12-21 00:24:08'),
(164, 'acl', 'expensestatuslist', 'show', 'backend', '', '2016-12-21 00:24:54'),
(165, 'acl', 'newslist', 'show', 'backend', '', '2016-12-21 00:25:24'),
(166, 'acl', 'newslist', 'filter', 'backend', '', '2016-12-21 00:25:37'),
(167, 'acl', 'news', 'show', 'backend', '', '2016-12-21 00:25:54'),
(168, 'acl', 'news', 'index', 'backend', '', '2016-12-21 00:26:05'),
(169, 'acl', 'organization', 'filter', 'backend', '', '2016-12-21 00:46:48'),
(170, 'acl', 'profile', 'save', 'backend', '', '2016-12-22 00:19:09'),
(171, 'acl', 'organization_expenselist', 'edit', 'backend', '', '2016-12-22 01:38:47'),
(172, 'acl', 'expenselist', 'edit', 'backend', '', '2016-12-24 14:14:12');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- --------------------------------------------------------

--
-- Структура таблицы `street_type`
--

DROP TABLE IF EXISTS `street_type`;
CREATE TABLE IF NOT EXISTS `street_type` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `street_type`
--

INSERT INTO `street_type` (`id`, `name`) VALUES
(1, 'Пр-кт'),
(2, 'Ул.'),
(3, 'Пер-д'),
(4, 'Пр-д'),
(5, 'Пер-к'),
(6, 'Ш.'),
(7, 'Наб.'),
(8, 'Бул.'),
(9, 'Ал.'),
(10, 'Пл.'),
(11, 'Туп.'),
(12, 'Дор.'),
(13, 'Маг.');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `password` char(50) CHARACTER SET utf8 NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` char(50) CHARACTER SET utf8 DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'ФИО',
  `points` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_role_id` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf16;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `password`, `phone`, `email`, `name`, `points`, `user_role_id`, `active`, `created_at`) VALUES
(20, '40bd001563085fc35165329ea1ff5c5ecbdbbeef', NULL, 'a@a', 'Суперпользователь', 0, 1, 1, '2015-02-06 18:40:36'),
(22, 'f4542db9ba30f7958ae42c113dd87ad21fb2eddb', NULL, 'b', 'Гость', 0, 2, 1, '2015-02-10 14:34:30'),
(25, '40bd001563085fc35165329ea1ff5c5ecbdbbeef', NULL, 'c', 'asdasd', 0, 1, 1, '2015-02-10 23:07:09'),
(32, 'da39a3ee5e6b4b0d3255bfef95601890afd80709', NULL, 'd', 'sdfsdad', 0, 5, 1, '2015-02-12 14:36:29'),
(34, '40bd001563085fc35165329ea1ff5c5ecbdbbeef', NULL, 'e', '123123123', 0, 4, 1, '2016-10-25 20:17:10'),
(40, '40bd001563085fc35165329ea1ff5c5ecbdbbeef', '', 'refaliu@yandex.ru', 'Тестовый пользователь-операционист', 0, 5, 1, '2016-12-10 20:08:31');

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
(32, 22),
(40, 38);

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
(5, 60),
(5, 64),
(5, 67),
(5, 68),
(5, 69),
(5, 81),
(5, 104),
(5, 140),
(5, 141),
(5, 143),
(5, 144),
(5, 148),
(5, 169),
(5, 170),
(5, 171),
(5, 172);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
