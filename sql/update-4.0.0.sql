CREATE TABLE `glpi_plugin_webapplications_webapplicationexternalexpositions` (
  `id`      int unsigned NOT NULL        AUTO_INCREMENT,
  `name`    VARCHAR(255)
            COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` TEXT COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

ALTER TABLE `glpi_plugin_webapplications_appliances`
  ADD `webapplicationexternalexpositions_id` int unsigned  NOT NULL     DEFAULT '0',
  ADD `webapplicationreferringdepartmentvalidation` tinyint NOT NULL default '0',
  ADD `webapplicationciovalidation` tinyint NOT NULL default '0',
  ADD `webapplicationavailabilities` int unsigned   NOT NULL     DEFAULT '1',
  ADD `webapplicationintegrities` int unsigned   NOT NULL     DEFAULT '1',
  ADD `webapplicationconfidentialities` int unsigned   NOT NULL     DEFAULT '0',
  ADD `webapplicationtraceabilities` int unsigned   NOT NULL     DEFAULT '1',
  ADD `version` VARCHAR (255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD `webapplicationmailsupport` VARCHAR (255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD `webapplicationphonesupport` VARCHAR (255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

CREATE TABLE `glpi_plugin_webapplications_databaseinstances` (
   `id` int unsigned NOT NULL auto_increment,
   `databases_id`  int unsigned NOT NULL        DEFAULT '0',
   `webapplicationexternalexpositions_id` int unsigned  NOT NULL     DEFAULT '0' COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationexternalexpositions (id)',
   `webapplicationavailabilities` int unsigned   NOT NULL     DEFAULT '1',
   `webapplicationintegrities` int unsigned   NOT NULL     DEFAULT '1',
   `webapplicationconfidentialities` int unsigned   NOT NULL     DEFAULT '0',
   `webapplicationtraceabilities` int unsigned   NOT NULL     DEFAULT '1',
   PRIMARY KEY  (`id`),
   KEY `databases_id` (`databases_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_webapplications_streams` (
   `id` int unsigned NOT NULL auto_increment,
   `entities_id`  int unsigned NOT NULL        DEFAULT '0',
   `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `transmitter` int unsigned NOT NULL default '0',
   `transmitter_type` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `receiver` int unsigned NOT NULL default '0',
   `receiver_type` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `encryption` tinyint NOT NULL default '0',
   `encryption_type` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `ports` int(5) unsigned NOT NULL,
   `protocole` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_webapplications_entities` (
   `id` int unsigned NOT NULL auto_increment,
   `entities_id` int unsigned NOT NULL,
   `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `owner` int unsigned NOT NULL default '0',
   `securitycontact` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `relationnature` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_webapplications_processes` (
   `id` int unsigned NOT NULL auto_increment,
   `entities_id`  int unsigned NOT NULL        DEFAULT '0',
   `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `owner` int unsigned NOT NULL default '0',
   `webapplicationavailabilities` int unsigned   NOT NULL     DEFAULT '1',
   `webapplicationintegrities` int unsigned   NOT NULL     DEFAULT '1',
   `webapplicationconfidentialities` int unsigned   NOT NULL     DEFAULT '0',
   `webapplicationtraceabilities` int unsigned   NOT NULL     DEFAULT '1',
   `is_recursive` tinyint NOT NULL DEFAULT '0',
   `comment` varchar(255) DEFAULT NULL,
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE `glpi_plugin_webapplications_processes_entities` (
    `id` int unsigned NOT NULL auto_increment,
    `plugin_webapplications_entities_id` int unsigned NOT NULL default '0',
    `plugin_webapplications_processes_id` int unsigned NOT NULL default '0',
    PRIMARY KEY  (`id`),
    UNIQUE KEY `unicity` (`plugin_webapplications_entities_id`,`plugin_webapplications_processes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE `glpi_plugin_webapplications_streams_items` (
    `id` int unsigned NOT NULL auto_increment,
    `plugin_webapplications_streams_id` int unsigned NOT NULL default '0',
    `items_id` int unsigned NOT NULL default '0',
    `itemtype` varchar(100) NOT NULL DEFAULT '',
    PRIMARY KEY  (`id`),
    UNIQUE KEY `unicity` (`plugin_webapplications_streams_id`,`items_id`,`itemtype`),
    KEY `item` (`itemtype`, `items_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE `glpi_plugin_webapplications_dashboards` (
    `id` int unsigned NOT NULL auto_increment,
    `users_id` int unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_webapplications_dashboards` VALUES ('1', 0);
