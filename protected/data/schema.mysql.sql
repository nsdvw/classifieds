
CREATE TABLE `country` (
  `country_id` int(11) unsigned NOT NULL auto_increment,
  `city_id` int(11) NOT NULL default 0,
  `name` varchar(128) NOT NULL default '',
  PRIMARY KEY (`country_id`),
  KEY `city_id` (`city_id`)
);

CREATE TABLE `region` (
  `region_id` int(10) unsigned NOT NULL auto_increment,
  `country_id` int(10) unsigned NOT NULL default 0,
  `city_id` int(10) unsigned NOT NULL default 0,
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY (`region_id`),
  FOREIGN KEY (`country_id`) REFERENCES `country` (`country_id`),
  KEY `city_id` (`city_id`)
);

CREATE TABLE `city` (
  `city_id` int(11) unsigned NOT NULL auto_increment,
  `country_id` int(11) unsigned NOT NULL default 0,
  `region_id` int(10) unsigned NOT NULL default 0,
  `name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`city_id`),
  FOREIGN KEY (`country_id`) REFERENCES `country` (`country_id`),
  FOREIGN KEY (`region_id`) REFERENCES `region` (`region_id`)
);

CREATE TABLE `eav_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `name` varchar(255) NOT NULL COMMENT 'Set name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `type` tinyint(1) unsigned NOT NULL COMMENT '0 if the attribute can have only one value or 1 if the attribute can have multiple values',
  `data_type` varchar(255) NOT NULL COMMENT 'The attribute data type',
  `name` varchar(255) NOT NULL COMMENT 'The attribute name',
  `label` varchar(255) DEFAULT NULL COMMENT 'The attribute label',
  `data` text COMMENT 'The serialized data',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_eav_attribute_name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_set` (
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Composite primary key',
  `eav_set_id` int(10) unsigned NOT NULL COMMENT 'Composite primary key',
  `weight` int(10) NOT NULL COMMENT 'The weight of the attribute',
  PRIMARY KEY (`eav_attribute_id`,`eav_set_id`),
  KEY `no_eav_attribute_set_attribute_id` (`eav_attribute_id`) USING BTREE,
  KEY `no_eav_attribute_set_set_id` (`eav_set_id`) USING BTREE,
  KEY `no_eav_attribute_set_weight` (`weight`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_set` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_eav_set_id_eav_attribute_set` FOREIGN KEY (`eav_set_id`) REFERENCES `eav_set` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_date` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` datetime NOT NULL COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_date_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_date_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  KEY `no_eav_attribute_date_value` (`value`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_date` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_varchar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` varchar(255) NOT NULL COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_varchar_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_varchar_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_varchar` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_int` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` int(11) NOT NULL COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_int_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_int_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  KEY `no_eav_attribute_int_value` (`value`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_int` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_text` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` text COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_text_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_text_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_text` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_numeric` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` double NOT NULL COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_numeric_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_numeric_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_numeric` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_money` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` DECIMAL(13,2) NOT NULL COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_money_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_money_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_money` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE user (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255),
  phone VARCHAR(255),
  vk VARCHAR(255),
  skype VARCHAR(255),
  PRIMARY KEY (id),
  UNIQUE (email)
);

CREATE TABLE `category` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `root` INT(10) UNSIGNED DEFAULT NULL,
  `lft` INT(10) UNSIGNED NOT NULL,
  `rgt` INT(10) UNSIGNED NOT NULL,
  `level` SMALLINT(5) UNSIGNED NOT NULL,
  `set_id` INT(10) UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `root` (`root`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`),
  KEY `level` (`level`),
  FOREIGN KEY (`set_id`) REFERENCES `eav_set` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE ad (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  author_id INT UNSIGNED NOT NULL,
  city_id INT UNSIGNED NOT NULL,
  category_id INT(10) UNSIGNED NOT NULL,
  visit_counter INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('unpublished', 'published', 'trash') NOT NULL DEFAULT 'unpublished',
  importance ENUM('usual', 'top', 'highlighted') NOT NULL DEFAULT 'usual',
  eav_set_id INT UNSIGNED,
  FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (city_id) REFERENCES city (city_id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (eav_set_id) REFERENCES eav_set (id) ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (id)
);

CREATE TABLE photo (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  upload_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ad_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (ad_id) REFERENCES ad (id) ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (id)
);

CREATE TABLE attr_variant (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  attr_id INT(10) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  FOREIGN KEY (attr_id) REFERENCES eav_attribute (id) ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (id)
);
