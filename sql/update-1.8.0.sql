ALTER TABLE `glpi_plugin_webapplications_webapplications`
  CHANGE `groups_id` `groups_id_tech` INT(11) NOT NULL DEFAULT '0',
  CHANGE `users_id` `users_id_tech` INT(11) NOT NULL DEFAULT '0';