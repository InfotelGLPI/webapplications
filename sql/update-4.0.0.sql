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