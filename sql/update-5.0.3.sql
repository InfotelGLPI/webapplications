DROP TABLE IF EXISTS `glpi_plugin_webapplications_configs`;
CREATE TABLE `glpi_plugin_webapplications_configs` (
                                                       `id` int unsigned NOT NULL auto_increment,
                                                       `use_fields_description` tinyint(1) NOT NULL DEFAULT '0',
                                                       `fields_description_table` varchar(250),
                                                       `fields_description_name` varchar(250),
                                                       PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_webapplications_configs` (id, use_fields_description) VALUES (1, 0);