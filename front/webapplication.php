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

include('../../../inc/includes.php');

use Certificate_Item;
use Change_Item;
use Computer_Item;
use Contract_Item;
use Document_Item;
use Domain_Item;
use Infocom;
use Item_Problem;
use Item_Project;
use Item_Rack;
use Item_Ticket;
use KnowbaseItem_Item;
use Log;

Html::header(PluginWebapplicationsWebapplication::getTypeName(2), "", "plugins", "");

Session::checkRight("plugin_webapplications", READ);

if (!isset($_POST['do_migration'])) {
   $_POST['do_migration'] = "0";
}

global $DB;

echo "<div align='center'><h1>" . __('Core migration', 'webapplications') . "</h1><br/>";

echo "<table align='center'><tr><td>";
Html::showSimpleForm($_SERVER['PHP_SELF'], 'migration', __('Core migration', 'webapplications'),
                     ['do_migration' => '1'], '', '',
                     [__('Are you sure you want to do core migration ?', 'webapplications')]);

echo "</td></tr></table>";

if ($DB->TableExists("glpi_plugin_webapplications_webapplications") && $_POST['do_migration'] == 1) {
   $dbu      = new DbUtils();
   $idUnknow = 0;

   // All tables containing items that may have a relation with a plugin's webapplication
   $itemtypes_tables = [
      Certificate_Item::getTable(),
      Change_Item::getTable(),
      Computer_Item::getTable(),
      Contract_Item::getTable(),
      Document_Item::getTable(),
      Domain_Item::getTable(),
      Infocom::getTable(),
      Item_Problem::getTable(),
      Item_Project::getTable(),
      Item_Rack::getTable(),
      Item_Ticket::getTable(),
      KnowbaseItem_Item::getTable(),
      Log::getTable(),
   ];

   echo "<br>";
   echo "<br>";

   echo __('Data migration', 'webapplications');

   $webappstypes = $dbu->getAllDataFromTable('glpi_plugin_webapplications_webapplicationtypes');
   $add_temporary_column_query = "ALTER TABLE `glpi_appliancetypes` ADD `old_id` int(11) NOT NULL DEFAULT 0;";
   $DB->queryOrDie($add_temporary_column_query);

   foreach ($webappstypes as $webapptype) {
      $migrate_webapptypes = 'INSERT INTO `glpi_appliancetypes` (`entities_id`, `is_recursive`, `name`, `comment`, `old_id`)
              VALUES("' . $webapptype['entities_id'] . '","' . $webapptype['is_recursive'] . '","' . $webapptype['name']
                        . '", "' . addslashes($webapptype['comment']) . '" ,"' . $webapptype['id']. '" );';

      $DB->query($migrate_webapptypes);
   }


   $webapps                    = $dbu->getAllDataFromTable('glpi_plugin_webapplications_webapplications');
   $add_temporary_column_query = "ALTER TABLE `glpi_appliances` ADD `old_id` int(11) NOT NULL DEFAULT 0;";
   $DB->queryOrDie($add_temporary_column_query);
   foreach ($webapps as $webapp) {

      $migrate_webapps = 'INSERT INTO `glpi_appliances` (`entities_id`, `is_recursive`, `name`, `is_deleted`, `appliancetypes_id`,
                                               `comment`, `locations_id`, `manufacturers_id`, `users_id_tech`,`groups_id_tech`, `old_id`
                                               )
              VALUES("' . $webapp['entities_id'] . '","' . $webapp['is_recursive'] . '","' . $webapp['name'] . '",
                           "' . $webapp['is_deleted'] . '", "' . $webapp['plugin_webapplications_webapplicationtypes_id'] . '", "' . addslashes($webapp['comment']) . '",
                                 "' . $webapp['locations_id'] . '", "' . $webapp['manufacturers_id'] . '",
                                    "' . $webapp['users_id_tech'] . '","' . $webapp['groups_id_tech'] . '","' . $webapp['id'] . '")';

      $DB->query($migrate_webapps);

      $migrate_webapps_additional_fields = 'INSERT INTO `glpi_plugin_webapplications_appliances` (`appliances_id`, 
                                               `webapplicationservertypes_id`, `webapplicationtechnics_id`, `address`, `backoffice`) VALUES
                                              ("' . $webapp['id'] . '", "' . $webapp['plugin_webapplications_webapplicationservertypes_id'] . '",
                                                      "' . $webapp['plugin_webapplications_webapplicationtechnics_id'] . '",
                                                         "' . $webapp['address'] . '", "' . $webapp['backoffice'] . '")';


      $DB->query($migrate_webapps_additional_fields);
   }

   $new_appliances = $dbu->getAllDataFromTable('glpi_appliances', ['old_id' => ['>', 0]]);

   foreach ($new_appliances as $new_appliance) {

      $query = "UPDATE `glpi_plugin_webapplications_appliances`
                            SET `appliances_id`='" . $new_appliance['id'] . "'
                            WHERE `appliances_id`= " . $new_appliance['old_id'] . ";";
      $DB->query($query);

      // Update relations with items
      foreach ($itemtypes_tables as $itemtype_table) {
         // Both type and ID are changed, there should be no collision (setting
         // the row to a new Appliance ID already used by a old plugin's
         // webapplication ID) value because the WHERE clause targets both the
         // old type AND the old ID.
         $DB->query(sprintf(
            '
            UPDATE
               `%s`
            SET
               `itemtype` = \'%s\',
               `items_id` =  %d
            WHERE
               `itemtype` = \'%s\'
               AND `items_id` = %d
            ',
            $itemtype_table,
            'Appliance',
            (int) $new_appliance['id'],
            'PluginWebapplicationsWebapplication',
            (int) $new_appliance['old_id']
         ));
      }

      if ($plugin->isActivated('accounts')) {
         $queryUpdateAccountsAssociatedItems = "UPDATE `glpi_plugin_accounts_accounts_items` 
                                                   SET `glpi_plugin_accounts_accounts_items`.`items_id` =  '" . $new_appliance['id'] . "', 
                                                            `glpi_plugin_accounts_accounts_items`.`itemtype` = 'Appliance'
                                                            WHERE `glpi_plugin_accounts_accounts_items`.`items_id`= '" . $new_appliance['old_id'] . "'
                                                               AND  `glpi_plugin_accounts_accounts_items`.`itemtype` = 'PluginWebapplicationsWebapplication';";
         $DB->query($queryUpdateAccountsAssociatedItems);
      }
      if ($plugin->isActivated('databases')) {
         $querUpdateDatabasesAssociatedItems ="UPDATE `glpi_plugin_databases_databases_items` 
                                                   SET `glpi_plugin_databases_databases_items`.`items_id` =  '" . $new_appliance['id'] . "', 
                                                            `glpi_plugin_databases_databases_items`.`itemtype` = 'Appliance'
                                                            WHERE `glpi_plugin_databases_databases_items`.`items_id`= '" . $new_appliance['old_id'] . "'
                                                               AND  `glpi_plugin_databases_databases_items`.`itemtype` = 'PluginWebapplicationsWebapplication';";

         $DB->query($querUpdateDatabasesAssociatedItems);
      }
   }

   $remove_temporary_column_query = "ALTER TABLE `glpi_appliances` DROP `old_id`;";
   $DB->queryOrDie($remove_temporary_column_query);

   $appliance_types = $dbu->getAllDataFromTable('glpi_appliancetypes', ['old_id' => ['>', 0]]);

   foreach ($appliance_types as $appliance_type) {
      $update_appliancetypes = "UPDATE `glpi_appliances`
                                     SET `appliancetypes_id`='" . $appliance_type['id'] . "'
                                     WHERE `appliancetypes_id`= " . $appliance_type['old_id'] . ";";
      $DB->query($update_appliancetypes);
   }

   $remove_temporary_column_query = "ALTER TABLE `glpi_appliancetypes` DROP `old_id`;";
   $DB->queryOrDie($remove_temporary_column_query);

   $plugin = new Plugin();

   echo "<br>";
   echo __('Tables purge', 'webapplications');

   $tables = ["glpi_plugin_webapplications_webapplications",
              "glpi_plugin_webapplications_webapplications_items"];

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   $oldtables = ["glpi_plugin_appweb",
                 "glpi_dropdown_plugin_appweb_type",
                 "glpi_dropdown_plugin_appweb_server_type",
                 "glpi_dropdown_plugin_appweb_technic",
                 "glpi_plugin_appweb_device",
                 "glpi_plugin_appweb_profiles",
                 "glpi_plugin_webapplications_profiles"];

   foreach ($oldtables as $oldtable)
      $DB->query("DROP TABLE IF EXISTS `$oldtable`;");

   echo "<br>";

   echo "<br>";
   echo __('Link with core purge', 'webapplications');
   echo "<br>";

   $in = "IN (" . implode(',', array(
         "'PluginWebapplicationsWebapplication'"
      )) . ")";

   $tables = array(
      "glpi_displaypreferences",
      "glpi_documents_items",
      "glpi_contracts_items",
      "glpi_savedsearches",
      "glpi_logs",
      "glpi_notepads",
   );

   foreach ($tables as $table) {
      $query = "DELETE FROM `$table` WHERE (`itemtype` " . $in . " ) ";
      $DB->query($query);
   }

   echo __('Migration was successful', 'webapplications');
}

echo "</div>";
Html::footer();

?>