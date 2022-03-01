DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationservertypes`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationservertypes` (
  `id`      int unsigned NOT NULL        AUTO_INCREMENT,
  `name`    VARCHAR(255)
            COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` TEXT COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('1', 'Apache', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('2', 'IIS', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('3', 'Nginx', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('4', 'PRTG', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('5', 'Tomcat', '');


DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationtypes`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationtypes` (
  `id`           int unsigned NOT NULL        AUTO_INCREMENT,
  `entities_id`  int unsigned NOT NULL        DEFAULT '0',
  `name`         VARCHAR(255)
                 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment`      TEXT COLLATE utf8mb4_unicode_ci,
  `is_recursive` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationtechnics`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationtechnics` (
  `id`      int unsigned NOT NULL        AUTO_INCREMENT,
  `name`    VARCHAR(255)
            COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` TEXT COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('1', 'Asp', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('2', 'Cgi', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('3', 'Java', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('4', 'Perl', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('5', 'Php', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('6', '.Net', '');

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationtechnictypes`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationtechnictypes` (
    `id`      int unsigned NOT NULL        AUTO_INCREMENT,
    `name`    VARCHAR(255)
              COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `comment` TEXT COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id`),
    KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationexternalexpositions`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationexternalexpositions` (
  `id`      int unsigned NOT NULL        AUTO_INCREMENT,
  `name`    VARCHAR(255)
            COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` TEXT COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationrefdepvalidation`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationrefdepvalidation` (
   `id`      int unsigned NOT NULL        AUTO_INCREMENT,
   `validation`    tinyint(1) NOT NULL default '0',
   `comment` TEXT COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationciovalidation`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationciovalidation` (
  `id`      int unsigned NOT NULL        AUTO_INCREMENT,
  `validation`    tinyint(1) NOT NULL default '0',
  `comment` TEXT COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;



DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationavailabilities`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationavailabilities` (
   `id`      int unsigned NOT NULL        AUTO_INCREMENT,
   `name`    VARCHAR(255)
       COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 1,
   `color`   VARCHAR(6) DEFAULT NULL,
   `comment` TEXT COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationintegrities`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationintegrities` (
   `id`      int unsigned NOT NULL        AUTO_INCREMENT,
   `name`    VARCHAR(255)
       COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 1,
   `color`   VARCHAR(6) DEFAULT NULL,
   `comment` TEXT COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationconfidentialities`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationconfidentialities` (
   `id`      int unsigned NOT NULL        AUTO_INCREMENT,
   `name`    VARCHAR(255)
       COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 1,
   `color`   VARCHAR(6) DEFAULT NULL,
   `comment` TEXT COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationtraceabilities`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationtraceabilities` (
   `id`      int unsigned NOT NULL        AUTO_INCREMENT,
   `name`    VARCHAR(255)
       COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 1,
   `color`   VARCHAR(6) DEFAULT NULL,
   `comment` TEXT COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


DROP TABLE IF EXISTS `glpi_plugin_webapplications_appliances`;
CREATE TABLE `glpi_plugin_webapplications_appliances` (
   `id` int unsigned NOT NULL auto_increment,
   `appliances_id` int unsigned NOT NULL,
   `webapplicationtypes_id`       int unsigned    NOT NULL     DEFAULT '0'
        COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtypes (id)',
   `webapplicationservertypes_id` int unsigned    NOT NULL     DEFAULT '0'
        COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationservertypes (id)',
   `webapplicationtechnics_id`    int unsigned    NOT NULL     DEFAULT '0'
        COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtechnics (id)',
   `webapplicationtechnictypes_id`    int unsigned  NOT NULL     DEFAULT '0'
       COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtechnictypes (id)',
   `webapplicationexternalexpositions_id` int unsigned  NOT NULL     DEFAULT '0'
       COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationexternalexpositions (id)',
   `webapplicationreferringdepartmentvalidation_id` int unsigned   NOT NULL     DEFAULT '0'
       COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationrefdepvalidation (id)',
   `webapplicationciovalidation_id` int unsigned   NOT NULL     DEFAULT '0'
       COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationciovalidation (id)',
   `webapplicationavailabilities_id` int unsigned   NOT NULL     DEFAULT '0'
       COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationavailabilities (id)',
   `webapplicationintegrities_id` int unsigned   NOT NULL     DEFAULT '0'
       COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationintegrities (id)',
   `webapplicationconfidentialities_id` int unsigned   NOT NULL     DEFAULT '0'
       COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationconfidentialities (id)',
   `webapplicationtraceabilities_id` int unsigned   NOT NULL     DEFAULT '0'
       COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtraceabilities (id)',
   `address` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `backoffice`  VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   PRIMARY KEY  (`id`),
   KEY `appliances_id` (`appliances_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

