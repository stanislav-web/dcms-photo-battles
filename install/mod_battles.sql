-- -- --
-- Table structure  `md_battles`
-- Database:        [ANY DCMS]
-- Project:         DCMS
-- Date:            Август 18 2013 г., 15:12
-- Author:          Stanislav WEB <stanisov@gmail.com>
-- Description:     Структура таблицы категорий
-- -- --

DROP TABLE IF EXISTS `mod_battles`;

CREATE TABLE `mod_battles` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID батла',
    `gender` ENUM('1','0') NOT NULL DEFAULT '1' COMMENT 'Тип батла (1 - Мужской, 2 - Женский)',
    `active` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Статус',
    `players` INT(2) NOT NULL DEFAULT '0' COMMENT 'Кол-во игроков',
    `start` INT(15) NOT NULL DEFAULT '0' COMMENT 'Время старта',
    `finish` INT(15) NOT NULL DEFAULT '0' COMMENT 'Время окончания',
    `key` INT(11) NOT NULL DEFAULT '0' COMMENT 'Ключ (последний зашедший)',
    PRIMARY KEY (`id`),
    KEY `gender` (`gender`),
    KEY `active` (`active`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `mod_battles` AUTO_INCREMENT = 0;

-- Table structure  `mod_battles_view`
-- Database:        [ANY DCMS]
-- Project:         DCMS
-- Date:            Август 18 2013 г., 15:12
-- Author:          Stanislav WEB <stanisov@gmail.com>
-- Description:     Структура таблицы содержимого категорий
-- -- --

DROP TABLE IF EXISTS `mod_battles_view`;

CREATE TABLE `mod_battles_view` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
    `votes` INT(11) NOT NULL DEFAULT '0' COMMENT 'Количество голосов',
    `photo_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'ID фото',
    `ext` CHAR(4) NOT NULL DEFAULT '' COMMENT 'Расширение фото',
    `user_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'ID пользователя',
    `battle_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'ID батла',
    `status` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Статус, если 0 - без участия, 1 - участник',
    PRIMARY KEY (`id`),
    KEY `votes` (`votes`),
    KEY `battle_id` (`battle_id`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `mod_battles_view` AUTO_INCREMENT = 0;

-- Table structure  `mod_battles_votes`
-- Database:        [ANY DCMS]
-- Project:         DCMS
-- Date:            Август 18 2013 г., 15:12
-- Author:          Stanislav WEB <stanisov@gmail.com>
-- Description:     Структура таблицы голосований
-- -- --

DROP TABLE IF EXISTS `mod_battles_votes`;

CREATE TABLE `mod_battles_votes` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID записи',
    `user_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'ID за кого голос',
    `from_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'ID голосовавшего',
    `gender` ENUM('1','0') NOT NULL DEFAULT '1' COMMENT 'Пол голосовавшего (1 - Мужской, 2 - Женский)',
    `battle_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'ID батла',
    PRIMARY KEY (`id`),
    KEY `gender` (`gender`),
    KEY `battle_id` (`battle_id`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `mod_battles_votes` AUTO_INCREMENT = 0;