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
   if (!$DB->tableExists("glpi_application")
       && !$DB->tableExists("glpi_plugin_appweb")
       && !$DB->tableExists("glpi_plugin_webapplications_webapplications")) {

      $DB->runFile(GLPI_ROOT . "/plugins/webapplications/sql/empty-2.6.0.sql");

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

   if ($update) {
      $query_  = "SELECT *
                FROM `glpi_plugin_webapplications_profiles` ";
      $result_ = $DB->query($query_);
      if ($DB->numrows($result_) > 0) {

         while ($data = $DB->fetch_array($result_)) {
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

   PluginWebapplicationsProfile::initProfile();
   PluginWebapplicationsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
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

   $tables = ["glpi_plugin_webapplications_webapplications",
                   "glpi_plugin_webapplications_webapplicationtypes",
                   "glpi_plugin_webapplications_webapplicationservertypes",
                   "glpi_plugin_webapplications_webapplicationtechnics",
                   "glpi_plugin_webapplications_webapplications_items"];

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
                   "glpi_plugin_webapplications_profiles"];

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

   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(['itemtype' => 'PluginWebapplicationsWebapplication']);
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginWebapplicationsProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
   PluginWebapplicationsMenu::removeRightsFromSession();
   PluginWebapplicationsProfile::removeRightsFromSession();

   return true;
}


// Define dropdown relations
/**
 * @return array
 */
function plugin_webapplications_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("webapplications")) {
      return ["glpi_plugin_webapplications_webapplicationtypes"
                   => ["glpi_plugin_webapplications_webapplications"
                            => "plugin_webapplications_webapplicationtypes_id"],
                   "glpi_plugin_webapplications_webapplicationservertypes"
                   => ["glpi_plugin_webapplications_webapplications"
                            => "plugin_webapplications_webapplicationservertypes_id"],
                   "glpi_plugin_webapplications_webapplicationtechnics"
                   => ["glpi_plugin_webapplications_webapplications"
                            => "plugin_webapplications_webapplicationtechnics_id"],
                   "glpi_users"
                   => ["glpi_plugin_webapplications_webapplications" => "users_id_tech"],
                   "glpi_groups"
                   => ["glpi_plugin_webapplications_webapplications" => "groups_id_tech"],
                   "glpi_suppliers"
                   => ["glpi_plugin_webapplications_webapplications" => "suppliers_id"],
                   "glpi_manufacturers"
                   => ["glpi_plugin_webapplications_webapplications" => "manufacturers_id"],
                   "glpi_locations"
                   => ["glpi_plugin_webapplications_webapplications" => "locations_id"],
                   "glpi_plugin_webapplications_webapplications"
                   => ["glpi_plugin_webapplications_webapplications_items"
                            => "plugin_webapplications_webapplications_id"],
                   "glpi_entities"
                   => ["glpi_plugin_webapplications_webapplications"     => "entities_id",
                            "glpi_plugin_webapplications_webapplicationtypes" => "entities_id"]];
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
      return ['PluginWebapplicationsWebapplicationType'
                   => PluginWebapplicationsWebapplicationType::getTypeName(2),
                   'PluginWebapplicationsWebapplicationServerType'
                   => PluginWebapplicationsWebapplicationServerType::getTypeName(2),
                   'PluginWebapplicationsWebapplicationTechnic'
                   => PluginWebapplicationsWebapplicationTechnic::getTypeName(2)];
   }
   return [];
}


/**
 * @param $types
 *
 * @return mixed
 */
function plugin_webapplications_AssignToTicket($types) {

   if (Session::haveRight("plugin_webapplications_open_ticket", "1")) {
      $types['PluginWebapplicationsWebapplication'] = PluginWebapplicationsWebapplication::getTypeName(2);
   }
   return $types;
}


////// SEARCH FUNCTIONS ///////() {

/**
 * @param $itemtype
 *
 * @return array
 */
function plugin_webapplications_getAddSearchOptions($itemtype) {

   $sopt = [];

   if (in_array($itemtype, PluginWebapplicationsWebapplication::getTypes(true))) {

      if (Session::haveRight("plugin_webapplications", READ)) {
         $sopt[1310]['table']         = 'glpi_plugin_webapplications_webapplications';
         $sopt[1310]['field']         = 'name';
         $sopt[1310]['name']          = PluginWebapplicationsWebapplication::getTypeName(2) . " - " .
                                        __('Name');
         $sopt[1310]['forcegroupby']  = true;
         $sopt[1310]['datatype']      = 'itemlink';
         $sopt[1310]['massiveaction'] = false;
         $sopt[1310]['itemlink_type'] = 'PluginWebapplicationsWebapplication';
         $sopt[1310]['joinparams']    = ['beforejoin'
                                              => ['table'      => 'glpi_plugin_webapplications_webapplications_items',
                                                       'joinparams' => ['jointype' => 'itemtype_item']]];

         $sopt[1311]['table']         = 'glpi_plugin_webapplications_webapplicationtypes';
         $sopt[1311]['field']         = 'name';
         $sopt[1311]['name']          = PluginWebapplicationsWebapplication::getTypeName(2) . " - " .
                                        PluginWebapplicationsWebapplicationType::getTypeName(1);
         $sopt[1311]['forcegroupby']  = true;
         $sopt[1311]['datatype']      = 'dropdown';
         $sopt[1311]['massiveaction'] = false;
         $sopt[1311]['joinparams']    = ['beforejoin' => [
            ['table'      => 'glpi_plugin_webapplications_webapplications',
                  'joinparams' => $sopt[1310]['joinparams']]]];
      }
   }

   return $sopt;
}

//display custom fields in the search
/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
 *
 * @return string
 */
function plugin_webapplications_giveItem($type, $ID, $data, $num) {
   global $DB;

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];
   $dbu       = new DbUtils();

   switch ($table . '.' . $field) {
      //display associated items with webapplications
      case "glpi_plugin_webapplications_webapplications_items.items_id" :
         $query_device    = "SELECT DISTINCT `itemtype`
                              FROM `glpi_plugin_webapplications_webapplications_items`
                              WHERE `plugin_webapplications_webapplications_id` = '" . $data['id'] . "'
                              ORDER BY `itemtype`";
         $result_device   = $DB->query($query_device);
         $number_device   = $DB->numrows($result_device);
         $out             = '';
         $webapplications = $data['id'];
         if ($number_device > 0) {
            for ($i = 0; $i < $number_device; $i++) {
               $column   = "name";
               $itemtype = $DB->result($result_device, $i, "itemtype");
               if (!class_exists($itemtype)) {
                  continue;
               }
               $item = new $itemtype();
               if ($item->canView()) {
                  $table_item = $dbu->getTableForItemType($itemtype);

                  if ($itemtype != 'Entity') {
                     $query = "SELECT `" . $table_item . "`.*,
                                      `glpi_plugin_webapplications_webapplications_items`.`id` AS table_items_id,
                                      `glpi_entities`.`id` AS entity
                               FROM `glpi_plugin_webapplications_webapplications_items`,
                                    `" . $table_item . "`
                               LEFT JOIN `glpi_entities`
                                 ON (`glpi_entities`.`id` = `" . $table_item . "`.`entities_id`)
                               WHERE `" . $table_item . "`.`id` = `glpi_plugin_webapplications_webapplications_items`.`items_id`
                                     AND `glpi_plugin_webapplications_webapplications_items`.`itemtype` = '$itemtype'
                                     AND `glpi_plugin_webapplications_webapplications_items`.`plugin_webapplications_webapplications_id` = '" . $webapplications . "' "
                              . $dbu->getEntitiesRestrictRequest(" AND ", $table_item, '', '',
                                                           $item->maybeRecursive());

                     if ($item->maybeTemplate()) {
                        $query .= " AND " . $table_item . ".is_template = '0'";
                     }
                     $query .= " ORDER BY `glpi_entities`.`completename`,
                                          `" . $table_item . "`.`$column` ";

                  } else {
                     $query = "SELECT `" . $table_item . "`.*,
                                      `glpi_plugin_webapplications_webapplications_items`.`id` AS table_items_id,
                                      `glpi_entities`.`id` AS entity
                               FROM `glpi_plugin_webapplications_webapplications_items`, `" . $table_item . "`
                               WHERE `" . $table_item . "`.`id` = `glpi_plugin_webapplications_webapplications_items`.`items_id`
                                     AND `glpi_plugin_webapplications_webapplications_items`.`itemtype` = '$itemtype'
                                     AND `glpi_plugin_webapplications_webapplications_items`.`plugin_webapplications_webapplications_id` = '" . $webapplications . "' "
                              . $dbu->getEntitiesRestrictRequest(" AND ", $table_item, '', '',
                                                           $item->maybeRecursive());

                     if ($item->maybeTemplate()) {
                        $query .= " AND " . $table_item . ".is_template = '0'";
                     }
                     $query .= " ORDER BY `glpi_entities`.`completename`,
                                          `" . $table_item . "`.`$column` ";
                  }

                  if ($result_linked = $DB->query($query)) {
                     if ($DB->numrows($result_linked)) {
                        $item = new $itemtype();
                        while ($datal = $DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($datal['id'])) {
                              $out .= $item->getTypeName() . " - " . $item->getLink() . "<br>";
                           }
                        }
                     } else {
                        $out .= ' ';
                     }
                  } else {
                     $out .= ' ';
                  }
               } else {
                  $out .= ' ';
               }
            }
         }
         return $out;
   }
   return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

/**
 * @param $type
 *
 * @return array
 */
function plugin_webapplications_MassiveActions($type) {
   
   $plugin = new Plugin();
   if ($plugin->isActivated('webapplications')) {
      if (in_array($type, PluginWebapplicationsWebapplication::getTypes(true))) {
         return ['PluginWebapplicationsWebapplication' . MassiveAction::CLASS_ACTION_SEPARATOR . 'plugin_webapplications_add_item' =>
                         __('Associate a web application', 'webapplications')];
      }
   }
   return [];
}

function plugin_webapplications_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['webapplications'] = [];

   foreach (PluginWebapplicationsWebapplication::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['webapplications'][$type]
         = ['PluginWebapplicationsWebapplication_Item', 'cleanForItem'];

      CommonGLPI::registerStandardTab($type, 'PluginWebapplicationsWebapplication_Item');
   }
}

function plugin_datainjection_populate_webapplications() {
   global $INJECTABLE_TYPES;

   $INJECTABLE_TYPES['PluginWebapplicationsWebapplicationInjection'] = 'webapplications';
}
