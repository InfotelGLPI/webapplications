<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 webapplications plugin for GLPI
 Copyright (C) 2009-2023 by the webapplications Development Team.

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


use GlpiPlugin\Webapplications\Webapplication;
use GlpiPlugin\Webapplications\Dashboard;

Session::checkRight("plugin_webapplications_appliances", READ);

Html::header(Webapplication::getTypeName(2), "", Dashboard::class, "");

if (!isset($_POST['do_migration'])) {
    $_POST['do_migration'] = "0";
}

global $DB;

echo "<div class='center'><h1>" . __('Core migration', 'webapplications') . "</h1><br/>";

echo "<table class='center'><tr><td>";
Html::showSimpleForm(
    $_SERVER['PHP_SELF'],
    'migration',
    __('Core migration', 'webapplications'),
    ['do_migration' => '1'],
    '',
    '',
    [__('Are you sure you want to do core migration ?', 'webapplications')]
);

echo "</td></tr></table>";

if ($DB->TableExists("glpi_plugin_webapplications_webapplications") && $_POST['do_migration'] == 1) {
    $dbu      = new DbUtils();
    $idUnknow = 0;

    echo "<br>";
    echo "<br>";

    echo __('Data migration', 'webapplications');

    $webappstypes = $dbu->getAllDataFromTable('glpi_plugin_webapplications_webapplicationtypes');
    $add_temporary_column_query = "ALTER TABLE `glpi_appliancetypes` ADD `old_id` int(11) NOT NULL DEFAULT 0;";
    $DB->doQuery($add_temporary_column_query);

    foreach ($webappstypes as $webapptype) {
        $DB->insert('glpi_appliancetypes', [
            'entities_id'  => (int) $webapptype['entities_id'],
            'is_recursive' => (int) $webapptype['is_recursive'],
            'name'         => $webapptype['name'],
            'comment'      => $webapptype['comment'],
            'old_id'       => (int) $webapptype['id'],
        ]);
    }


    $webapps                    = $dbu->getAllDataFromTable('glpi_plugin_webapplications_webapplications');
    $add_temporary_column_query = "ALTER TABLE `glpi_appliances` ADD `old_id` int(11) NOT NULL DEFAULT 0;";
    $DB->doQuery($add_temporary_column_query);
    foreach ($webapps as $webapp) {
        $DB->insert('glpi_appliances', [
            'entities_id'       => (int) $webapp['entities_id'],
            'is_recursive'      => (int) $webapp['is_recursive'],
            'name'              => $webapp['name'],
            'is_deleted'        => (int) $webapp['is_deleted'],
            'appliancetypes_id' => (int) $webapp['plugin_webapplications_webapplicationtypes_id'],
            'comment'           => $webapp['comment'],
            'locations_id'      => (int) $webapp['locations_id'],
            'manufacturers_id'  => (int) $webapp['manufacturers_id'],
            'users_id_tech'     => (int) $webapp['users_id_tech'],
            'groups_id_tech'    => (int) $webapp['groups_id_tech'],
            'old_id'            => (int) $webapp['id'],
        ]);

        $DB->insert('glpi_plugin_webapplications_appliances', [
            'appliances_id'                               => (int) $webapp['id'],
            'webapplicationservertypes_id'                => (int) $webapp['plugin_webapplications_webapplicationservertypes_id'],
            'webapplicationtechnics_id'                   => (int) $webapp['plugin_webapplications_webapplicationtechnics_id'],
            'address'                                     => $webapp['address'],
            'backoffice'                                  => $webapp['backoffice'],
            'webapplicationexternalexpositions_id'        => (int) $webapp['webapplicationexternalexpositions_id'],
            'webapplicationreferringdepartmentvalidation' => (int) $webapp['webapplicationreferringdepartmentvalidation'],
            'webapplicationciovalidation'                 => (int) $webapp['webapplicationciovalidation'],
            'webapplicationavailabilities'                => (int) $webapp['webapplicationavailabilities'],
            'webapplicationintegrities'                   => (int) $webapp['webapplicationintegrities'],
            'webapplicationconfidentialities'             => (int) $webapp['webapplicationconfidentialities'],
            'webapplicationtraceabilities'                => (int) $webapp['webapplicationtraceabilities'],
        ]);

        $DB->insert('glpi_plugin_webapplications_databaseinstances', [
            'databaseinstances_id'                 => (int) $webapp['id'],
            'webapplicationexternalexpositions_id' => (int) $webapp['webapplicationexternalexpositions_id'],
            'webapplicationavailabilities'         => (int) $webapp['webapplicationavailabilities'],
            'webapplicationintegrities'            => (int) $webapp['webapplicationintegrities'],
            'webapplicationconfidentialities'      => (int) $webapp['webapplicationconfidentialities'],
            'webapplicationtraceabilities'         => (int) $webapp['webapplicationtraceabilities'],
        ]);
    }

    $new_appliances = $dbu->getAllDataFromTable('glpi_appliances', ['old_id' => ['>', 0]]);

    foreach ($new_appliances as $new_appliance) {
        $DB->update(
            'glpi_plugin_webapplications_appliances',
            ['appliances_id' => (int) $new_appliance['id']],
            ['appliances_id' => (int) $new_appliance['old_id']]
        );

        if (Plugin::isPluginActive('accounts')) {
            $DB->update(
                'glpi_plugin_accounts_accounts_items',
                ['items_id' => (int) $new_appliance['id'], 'itemtype' => 'Appliance'],
                ['items_id' => (int) $new_appliance['old_id'], 'itemtype' => 'PluginWebapplicationsWebapplication']
            );
        }
        if (Plugin::isPluginActive('databases')) {
            $DB->update(
                'glpi_plugin_databases_databases_items',
                ['items_id' => (int) $new_appliance['id'], 'itemtype' => 'Appliance'],
                ['items_id' => (int) $new_appliance['old_id'], 'itemtype' => 'PluginWebapplicationsWebapplication']
            );
        }
    }

    $remove_temporary_column_query = "ALTER TABLE `glpi_appliances` DROP `old_id`;";
    $DB->doQuery($remove_temporary_column_query);

    $appliance_types = $dbu->getAllDataFromTable('glpi_appliancetypes', ['old_id' => ['>', 0]]);

    foreach ($appliance_types as $appliance_type) {
        $DB->update(
            'glpi_appliances',
            ['appliancetypes_id' => (int) $appliance_type['id']],
            ['appliancetypes_id' => (int) $appliance_type['old_id']]
        );
    }

    $remove_temporary_column_query = "ALTER TABLE `glpi_appliancetypes` DROP `old_id`;";
    $DB->doQuery($remove_temporary_column_query);

    echo "<br>";
    echo __('Tables purge', 'webapplications');

    $tables = ["glpi_plugin_webapplications_webapplications",
               "glpi_plugin_webapplications_webapplications_items"];

    foreach ($tables as $table) {
        $DB->doQuery("DROP TABLE IF EXISTS `$table`;");
    }

    $oldtables = ["glpi_plugin_appweb",
                  "glpi_dropdown_plugin_appweb_type",
                  "glpi_dropdown_plugin_appweb_server_type",
                  "glpi_dropdown_plugin_appweb_technic",
                  "glpi_plugin_appweb_device",
                  "glpi_plugin_appweb_profiles",
                  "glpi_plugin_webapplications_profiles"];

    foreach ($oldtables as $oldtable) {
        $DB->doQuery("DROP TABLE IF EXISTS `$oldtable`;");
    }

    echo "<br>";

    echo "<br>";
    echo __('Link with core purge', 'webapplications');
    echo "<br>";

    $in = "IN (" . implode(',', array(
          "'GlpiPlugin\Webapplications'"
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
        $DB->doQuery($query);
    }

    echo __('Migration was successful', 'webapplications');
}

echo "</div>";
Html::footer();
