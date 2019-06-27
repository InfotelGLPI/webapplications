ALTER TABLE `glpi_plugin_webapplications_webapplications`
   ADD `states_id` TINYINT(1) NOT NULL DEFAULT '0'
   AFTER `locations_id`
   COMMENT 'RELATION to glpi_locations (id)',
   ADD INDEX (`states_id`);
