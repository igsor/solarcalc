DROP TABLE IF EXISTS `battery`;
DROP TABLE IF EXISTS `panel`;
DROP TABLE IF EXISTS `inverter`;
DROP TABLE IF EXISTS `load`;
DROP TABLE IF EXISTS `controller`;
DROP TABLE IF EXISTS `project_load`;
DROP TABLE IF EXISTS `project_panel`;
DROP TABLE IF EXISTS `project_battery`;
DROP TABLE IF EXISTS `project_inverter`;
DROP TABLE IF EXISTS `project_controller`;
DROP TABLE IF EXISTS `project`;

CREATE TABLE `load` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `power` DOUBLE,
 `type` ENUM('AC', 'DC') default 'DC',
 `voltage` DOUBLE default 12,
 `price` DOUBLE default 0,
 `stock` INT default 0,
 PRIMARY KEY (`id`)
);

CREATE TABLE `panel` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `power` DOUBLE,
 `voltage` DOUBLE default 12,
 `peak_power` DOUBLE,
 `price` DOUBLE default 0,
 `stock` INT default 0,
 PRIMARY KEY (`id`)
);

CREATE TABLE `battery` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `dod` DOUBLE,
 `voltage` DOUBLE default 3.2,
 `loss` DOUBLE default 0,
 `discharge` DOUBLE default 0.03,
 `lifespan` DOUBLE default 2000,
 `capacity` DOUBLE,
 `price` DOUBLE default 0,
 `stock` INT default 0,
 `max_const_current` DOUBLE,
 `max_peak_current` DOUBLE,
 `avg_const_current` DOUBLE,
 `max_humidity` DOUBLE,
 `max_temperature` DOUBLE,
PRIMARY KEY (`id`)
);

CREATE TABLE `inverter` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `loss` DOUBLE,
 `voltage` DOUBLE default 12,
 `price` DOUBLE default 0,
 `stock` INT default 0,
 `max_current` DOUBLE,
PRIMARY KEY (`id`)
);


CREATE TABLE `controller` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `loss` DOUBLE,
 `price` DOUBLE default 0,
 `stock` INT default 0,
 `voltage` DOUBLE,
 `max_current` DOUBLE,
PRIMARY KEY (`id`)
);


CREATE TABLE `project` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `client_name` VARCHAR(100),
 `client_phone` VARCHAR(100),
 `responsible_name` VARCHAR(100),
 `responsible_phone` VARCHAR(100),
 `location` VARCHAR(100),
 `comments` TEXT,
 `delivery_date` INT,
 `sunhours` INT NOT NULL default 5,
PRIMARY KEY (`id`)
);


CREATE TABLE `project_load` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `project` INT unsigned NOT NULL,
 `load` INT unsigned default NULL,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `amount` INT default 0,
 `power` DOUBLE,
 `type` ENUM('AC', 'DC') default 'DC',
 `voltage` DOUBLE default 12,
 `price` DOUBLE default 0,
 `daytime` DOUBLE default 0,
 `nighttime` DOUBLE default 0,
 `sold` BOOL default FALSE,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`project`) REFERENCES `project`(`id`) ON DELETE CASCADE
 -- FOREIGN KEY (`load`) REFERENCES `load`(`id`) ON DELETE SET NULL
);


CREATE TABLE `project_panel` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `project` INT unsigned NOT NULL,
 `panel` INT unsigned NOT NULL,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `amount` INT default 0,
 `power` DOUBLE,
 `peak_power` DOUBLE,
 `voltage` DOUBLE default 12,
 `price` DOUBLE default 0,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`project`) REFERENCES `project`(`id`) ON DELETE CASCADE
 -- FOREIGN KEY (`panel`) REFERENCES `panel`(`id`) ON DELETE SET NULL
);


CREATE TABLE `project_battery` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `project` INT unsigned NOT NULL,
 `battery` INT unsigned NOT NULL,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `amount` INT default 0,
 `dod` DOUBLE,
 `voltage` DOUBLE default 3.2,
 `loss` DOUBLE default 0,
 `discharge` DOUBLE default 0.03,
 `lifespan` DOUBLE default 2000,
 `capacity` DOUBLE,
 `price` DOUBLE default 0,
 `max_const_current` DOUBLE,
 `max_peak_current` DOUBLE,
 `avg_const_current` DOUBLE,
 `max_humidity` DOUBLE,
 `max_temperature` DOUBLE,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`project`) REFERENCES `project`(`id`) ON DELETE CASCADE
 -- FOREIGN KEY (`panel`) REFERENCES `panel`(`id`) ON DELETE SET NULL
);


CREATE TABLE `project_inverter` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `project` INT unsigned NOT NULL,
 `inverter` INT unsigned NOT NULL,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `amount` INT default 0,
 `loss` DOUBLE,
 `voltage` DOUBLE default 12,
 `price` DOUBLE default 0,
 `max_current` DOUBLE,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`project`) REFERENCES `project`(`id`) ON DELETE CASCADE
 -- FOREIGN KEY (`inverter`) REFERENCES `inverter`(`id`) ON DELETE SET NULL
);


CREATE TABLE `project_controller` (
 `id` INT unsigned NOT NULL AUTO_INCREMENT,
 `project` INT unsigned NOT NULL,
 `controller` INT unsigned NOT NULL,
 `name` VARCHAR(100) NOT NULL default '',
 `description` TEXT,
 `amount` INT default 0,
 `loss` DOUBLE,
 `price` DOUBLE default 0,
 `voltage` DOUBLE,
 `max_current` DOUBLE,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`project`) REFERENCES `project`(`id`) ON DELETE CASCADE
 -- FOREIGN KEY (`controller`) REFERENCES `controller`(`id`) ON DELETE SET NULL
);


