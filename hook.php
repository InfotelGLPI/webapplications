<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 webapplications plugin for GLPI
 Copyright (C) 2009-2016 by the webapplications Development Team.

 https://github.com/InfotelGLPI/webapplications
 -------------------------------------------------------------------------

 LICENSE

 This file is part of webapplications.

 webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * @return bool
 */
function plugin_webapplications_install() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/webapplications/inc/profile.class.php");

   $update = false;
   //from 3.0 version (glpi 9.5)
   if (!$DB->tableExists("glpi_plugin_webapplications_appliances")) {

      $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/empty-3.0.0.sql");

   } else {

      if ($DB->tableExists("glpi_application") && !$DB->tableExists("glpi_plugin_appweb")) {
         $update = true;
         $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-1.1.sql");
      }

      //from 1.1 version
      if ($DB->tableExists("glpi_plugin_appweb") && !$DB->fieldExists("glpi_plugin_appweb", "location")) {
         $update = true;
         $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-1.3.sql");
      }

      //from 1.3 version
      if ($DB->tableExists("glpi_plugin_appweb") && !$DB->fieldExists("glpi_plugin_appweb", "recursive")) {
         $update = true;
         $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-1.4.sql");
      }

      if ($DB->tableExists("glpi_plugin_appweb_profiles")
         && $DB->fieldExists("glpi_plugin_appweb_profiles", "interface")) {
         $update = true;
         $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-1.5.0.sql");
      }

      if ($DB->tableExists("glpi_plugin_appweb")
         && !$DB->fieldExists("glpi_plugin_appweb", "helpdesk_visible")) {
         $update = true;
         $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-1.5.1.sql");
      }

      if (!$DB->tableExists("glpi_plugin_webapplications_webapplications")) {
         $update = true;
         $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-1.6.0.sql");

         //not same index name depending on installation version for (`FK_appweb`, `FK_device`, `device_type`)
         $query = "ALTER TABLE `glpi_plugin_webapplications_webapplications_items` DROP INDEX `FK_compte`;";
         $DB->query($query);

         //index with install version 1.5.0 & 1.5.1
         $query = "ALTER TABLE `glpi_plugin_webapplications_webapplications_items` DROP INDEX `FK_appweb`;";
         $DB->query($query);
      }

      //from 1.6 version
      if ($DB->tableExists("glpi_plugin_webapplications_webapplications")
         && !$DB->fieldExists("glpi_plugin_webapplications_webapplications", "users_id_tech")) {
         $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-1.8.0.sql");
      }
   }

   if ($DB->tableExists("glpi_plugin_webapplications_profiles")) {

      $notepad_tables = ['glpi_plugin_webapplications_webapplications'];
      $dbu = new DbUtils();

      foreach ($notepad_tables as $t) {
         // Migrate data
         if ($DB->fieldExists($t, 'notepad')) {
            $query = "SELECT id, notepad
                      FROM `$t`
                      WHERE notepad IS NOT NULL
                            AND notepad <>'';";
            foreach ($DB->request($query) as $data) {
               $iq = "INSERT INTO `glpi_notepads`
                             (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                      VALUES ('" . $dbu->getItemTypeForTable($t) . "', '" . $data['id'] . "',
                              '" . addslashes($data['notepad']) . "', NOW(), NOW())";
               $DB->queryOrDie($iq, "0.85 migrate notepad data");
            }
            $query = "ALTER TABLE `glpi_plugin_webapplications_webapplications` DROP COLUMN `notepad`;";
            $DB->query($query);
         }
      }
   }

   if (!$DB->fieldExists("glpi_plugin_webapplications_webapplicationtypes", "is_recursive")) {
      $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-1.9.0.sql");
   }

   //from 3.0 version (glpi 9.5)
   if ($DB->tableExists("glpi_plugin_webapplications_webapplications")
      && !$DB->tableExists("glpi_plugin_webapplications_appliances")) {
      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/update-3.0.0.sql");
   }


if ($update) {
   $query_  = "SELECT *
                FROM `glpi_plugin_webapplications_profiles` ";
   $result_ = $DB->query($query_);
   if ($DB->numrows($result_) > 0) {

      while ($data = $DB->fetchArray($result_)) {
         $query = "UPDATE `glpi_plugin_webapplications_profiles`
                      SET `profiles_id` = '" . $data["id"] . "'
                      WHERE `id` = '" . $data["id"] . "';";
         $DB->query($query);
      }
   }

   $query = "ALTER TABLE `glpi_plugin_webapplications_profiles`
               DROP `name` ;";
   $DB->query($query);

   Plugin::migrateItemType([1300 => 'PluginWebapplicationsWebapplication'],
      ["glpi_savedsearches", "glpi_savedsearches_users",
         "glpi_displaypreferences", "glpi_documents_items",
         "glpi_infocoms", "glpi_logs", "glpi_items_tickets"],
      ["glpi_plugin_webapplications_webapplications_items"]);

   Plugin::migrateItemType([1200 => "PluginAppliancesAppliance"],
      ["glpi_plugin_webapplications_webapplications_items"]);
}

$migration = new Migration("2.2.0");
$migration->dropTable('glpi_plugin_webapplications_profiles');

return true;

}


/**
 * @return bool
 */
function plugin_webapplications_uninstall() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/webapplications/inc/profile.class.php");
   include_once(GLPI_ROOT . "/plugins/webapplications/inc/menu.class.php");

   $tables =  ["glpi_plugin_webapplications_appliances",
               "glpi_plugin_webapplications_webapplicationtypes",
               "glpi_plugin_webapplications_webapplicationservertypes",
               "glpi_plugin_webapplications_webapplicationtechnics"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

//old versions
   $tables = ["glpi_plugin_appweb",
               "glpi_dropdown_plugin_appweb_type",
               "glpi_dropdown_plugin_appweb_server_type",
               "glpi_dropdown_plugin_appweb_technic",
               "glpi_plugin_appweb_device",
               "glpi_plugin_appweb_profiles",
               "glpi_plugin_webapplications_profiles",
               "glpi_plugin_webapplications_webapplications_items"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $tables_glpi = ["glpi_displaypreferences",
                     "glpi_documents_items",
                     "glpi_savedsearches",
                     "glpi_logs",
                     "glpi_items_tickets",
                     "glpi_notepads",
                     "glpi_dropdowntranslations"];

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE
                  FROM `$table_glpi`
                  WHERE `itemtype` LIKE 'PluginWebapplications%'");
   }

   PluginWebapplicationsMenu::removeRightsFromSession();

   return true;
}


// Define dropdown relations
/**
 * @return array
 */
function plugin_webapplications_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("webapplications")) {
      return ["glpi_appliances" => ["glpi_plugin_webapplications_appliances" => "appliances_id"]];
   }

   return [];
}


// Define Dropdown tables to be manage in GLPI :
/**
 * @return array
 */
function plugin_webapplications_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("webapplications")) {
      return [     'PluginWebapplicationsWebapplicationServerType' => PluginWebapplicationsWebapplicationServerType::getTypeName(2),
                   'PluginWebapplicationsWebapplicationType' => PluginWebapplicationsWebapplicationType::getTypeName(2),
                   'PluginWebapplicationsWebapplicationTechnic' => PluginWebapplicationsWebapplicationTechnic::getTypeName(2)];
   }
   return [];
}
