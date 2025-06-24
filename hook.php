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

/**
 * @return bool
 */
function plugin_webapplications_install()
{
    global $DB;

    include_once(PLUGIN_WEBAPPLICATIONS_DIR . "/inc/profile.class.php");

    $update = false;
    //from 3.0 version (glpi 9.5)
    if (!$DB->tableExists("glpi_plugin_webapplications_webapplicationtypes")) {
        $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/empty-5.0.0.sql");
    } else {
        if ($DB->tableExists("glpi_application") && !$DB->tableExists("glpi_plugin_appweb")) {
            $update = true;
            $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-1.1.sql");
        }

        //from 1.1 version
        if ($DB->tableExists("glpi_plugin_appweb") && !$DB->fieldExists("glpi_plugin_appweb", "location")) {
            $update = true;
            $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-1.3.sql");
        }

        //from 1.3 version
        if ($DB->tableExists("glpi_plugin_appweb") && !$DB->fieldExists("glpi_plugin_appweb", "recursive")) {
            $update = true;
            $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-1.4.sql");
        }

        if ($DB->tableExists("glpi_plugin_appweb_profiles")
            && $DB->fieldExists("glpi_plugin_appweb_profiles", "interface")) {
            $update = true;
            $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-1.5.0.sql");
        }

        if ($DB->tableExists("glpi_plugin_appweb")
            && !$DB->fieldExists("glpi_plugin_appweb", "helpdesk_visible")) {
            $update = true;
            $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-1.5.1.sql");
        }

        if ($DB->tableExists("glpi_plugin_appweb")
            && !$DB->tableExists("glpi_plugin_webapplications_webapplications")) {
            $update = true;
            $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-1.6.0.sql");

            //not same index name depending on installation version for (`FK_appweb`, `FK_device`, `device_type`)
            $query = "ALTER TABLE `glpi_plugin_webapplications_webapplications_items` DROP INDEX `FK_compte`;";
            $DB->doQuery($query);

            //index with install version 1.5.0 & 1.5.1
            $query = "ALTER TABLE `glpi_plugin_webapplications_webapplications_items` DROP INDEX `FK_appweb`;";
            $DB->doQuery($query);
        }

        //from 1.6 version
        if ($DB->tableExists("glpi_plugin_webapplications_webapplications")
            && !$DB->fieldExists("glpi_plugin_webapplications_webapplications", "users_id_tech")) {
            $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-1.8.0.sql");
        }
    }

    if ($DB->tableExists("glpi_plugin_webapplications_profiles")) {
        $notepad_tables = ['glpi_plugin_webapplications_webapplications'];
        $dbu            = new DbUtils();

        foreach ($notepad_tables as $t) {
            // Migrate data
            if ($DB->fieldExists($t, 'notepad')) {
                $iterator = $DB->request([
                    'SELECT' => [
                        'notepad',
                        'id'
                    ],
                    'FROM' => $t,
                    'WHERE' => [
                        'NOT' => ['notepad' => null],
                        'notepad' => ['<>', '']
                    ],
                ]);
                if (count($iterator) > 0) {
                    foreach ($iterator as $data) {
                        $iq = "INSERT INTO `glpi_notepads`
                             (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                      VALUES ('" . $dbu->getItemTypeForTable($t) . "', '" . $data['id'] . "',
                              '" . addslashes($data['notepad']) . "', NOW(), NOW())";
                        $DB->doQuery($iq, "0.85 migrate notepad data");
                    }
                }
                $query = "ALTER TABLE `glpi_plugin_webapplications_webapplications` DROP COLUMN `notepad`;";
                $DB->doQuery($query);
            }
        }
    }

    if (!$DB->fieldExists("glpi_plugin_webapplications_webapplicationtypes", "is_recursive")) {
        $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-1.9.0.sql");
    }

    //from 3.0 version (glpi 9.5)
    if ($DB->tableExists("glpi_plugin_webapplications_webapplications")
        && !$DB->tableExists("glpi_plugin_webapplications_appliances")) {
        $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR. "/sql/update-3.0.0.sql");
    }

    //from 4.0 version (glpi 10.0)
    if (!$DB->tableExists("glpi_plugin_webapplications_processes")) {
        $DB->runFile(PLUGIN_WEBAPPLICATIONS_DIR . "/sql/update-5.0.0.sql");
    }


    if ($update) {
        $query_  = "SELECT *
                FROM `glpi_plugin_webapplications_profiles` ";
        $result_ = $DB->doQuery($query_);
        if ($DB->numrows($result_) > 0) {
            while ($data = $DB->fetchArray($result_)) {
                $query = "UPDATE `glpi_plugin_webapplications_profiles`
                      SET `profiles_id` = '" . $data["id"] . "'
                      WHERE `id` = '" . $data["id"] . "';";
                $DB->doQuery($query);
            }
        }

        $query = "ALTER TABLE `glpi_plugin_webapplications_profiles`
               DROP `name` ;";
        $DB->doQuery($query);

        Plugin::migrateItemType(
            [1300 => 'PluginWebapplicationsWebapplication'],
            ["glpi_savedsearches", "glpi_savedsearches_users",
             "glpi_displaypreferences", "glpi_documents_items",
             "glpi_infocoms", "glpi_logs", "glpi_items_tickets"],
            ["glpi_plugin_webapplications_webapplications_items"]
        );

        Plugin::migrateItemType(
            [1200 => "PluginAppliancesAppliance"],
            ["glpi_plugin_webapplications_webapplications_items"]
        );

        Plugin::migrateItemType(
            [1400 => "PluginDatabasesDatabase"],
            ["glpi_plugin_webapplications_webapplications_items"]
        );
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
function plugin_webapplications_uninstall()
{
    global $DB;

    include_once(PLUGIN_WEBAPPLICATIONS_DIR . "/inc/profile.class.php");

    $tables = ["glpi_plugin_webapplications_appliances",
               "glpi_plugin_webapplications_databaseinstances",
               "glpi_plugin_webapplications_streams",
               "glpi_plugin_webapplications_processes",
               "glpi_plugin_webapplications_processes_entities",
               "glpi_plugin_webapplications_entities",
               "glpi_plugin_webapplications_dashboards",
               "glpi_plugin_webapplications_webapplicationtypes",
               "glpi_plugin_webapplications_webapplicationservertypes",
               "glpi_plugin_webapplications_webapplicationtechnics",
               "glpi_plugin_webapplications_webapplicationexternalexpositions"];

    foreach ($tables as $table) {
        $DB->doQuery("DROP TABLE IF EXISTS `$table`;");
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
        $DB->doQuery("DROP TABLE IF EXISTS `$table`;");
    }

    $tables_glpi = ["glpi_displaypreferences",
                    "glpi_documents_items",
                    "glpi_savedsearches",
                    "glpi_logs",
                    "glpi_items_tickets",
                    "glpi_notepads",
                    "glpi_dropdowntranslations"];

    foreach ($tables_glpi as $table_glpi) {
        $DB->delete($table_glpi, ['itemtype' => ['LIKE' => 'PluginWebapplications%']]);
    }

    //Delete rights associated with the plugin
    $profileRight = new ProfileRight();
    foreach (PluginWebapplicationsProfile::getAllRights() as $right) {
        $profileRight->deleteByCriteria(['name' => $right['field']]);
    }

    PluginWebapplicationsProfile::removeRightsFromSession();

    return true;
}


// Define dropdown relations
/**
 * @return array
 */
//function plugin_webapplications_getDatabaseRelations()
//{
//    if (Plugin::isPluginActive("webapplications")) {
//        return ["glpi_appliances" => ["glpi_plugin_webapplications_appliances" => "appliances_id"],
//                "glpi_databaseinstances" => ["glpi_plugin_webapplications_databaseinstances" => "databaseinstances_id"],
//                "glpi_streams" => ["glpi_plugin_webapplications_streams" => "entities_id"],
//                "glpi_processes" => ["glpi_plugin_webapplications_processes" => "entities_id"],
//                "glpi_entities" => ["glpi_plugin_webapplications_entities" => "entities_id"]];
//    }
//
//    return [];
//}


// Define Dropdown tables to be manage in GLPI :
/**
 * @return array
 */
function plugin_webapplications_getDropdown()
{
    if (Plugin::isPluginActive("webapplications")) {
        return ['PluginWebapplicationsWebapplicationServerType' => PluginWebapplicationsWebapplicationServerType::getTypeName(2),
//              'PluginWebapplicationsWebapplicationType'       => PluginWebapplicationsWebapplicationType::getTypeName(2),
                'PluginWebapplicationsWebapplicationTechnic'    => PluginWebapplicationsWebapplicationTechnic::getTypeName(2),
                'PluginWebapplicationsWebapplicationExternalExposition' => PluginWebapplicationsWebapplicationExternalExposition::getTypeName(2)];
    }
    return [];
}

// Define search option for types of the plugins
function plugin_webapplications_getAddSearchOptions($itemtype)
{
    $sopt = [];

    if ($itemtype == "Appliance") {
        if (Session::haveRight("plugin_webapplications_appliances", READ)) {
            $sopt[8102]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8102]['field']         = 'address';
            $sopt[8102]['name']          = __('URL');
            $sopt[8102]['massiveaction'] = false;
            $sopt[8102]['datatype']      = 'weblink';
            $sopt[8102]['linkfield']     = 'appliances_id';
            $sopt[8102]['joinparams']    = array('jointype' => 'child');
            $sopt[8102]['forcegroupby']  = false;

            $sopt[8103]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8103]['field']         = 'backoffice';
            $sopt[8103]['name']          = __('Backoffice URL', 'webapplications');
            $sopt[8103]['massiveaction'] = false;
            $sopt[8103]['datatype']      = 'weblink';
            $sopt[8103]['linkfield']     = 'appliances_id';
            $sopt[8103]['joinparams']    = array('jointype' => 'child');
            $sopt[8103]['forcegroupby']  = false;

            $sopt[8104]['table']         = 'glpi_plugin_webapplications_webapplicationtypes';
            $sopt[8104]['field']         = 'name';
            $sopt[8104]['datatype']  = 'dropdown';
            $sopt[8104]['name']          = PluginWebapplicationsWebapplicationType::getTypeName(1);
            $sopt[8104]['forcegroupby']  = true;
            $sopt[8104]['massiveaction'] = false;
            $sopt[8104]['linkfield']     = 'webapplicationtypes_id';
            $sopt[8104]['joinparams']    = [
               'beforejoin' => [
                  'table'      => 'glpi_plugin_webapplications_appliances',
                  'joinparams' => [
                     'jointype'  => 'child',
                     'condition' => ''
                  ]
               ]
            ];

            $sopt[8105]['table']         = 'glpi_plugin_webapplications_webapplicationservertypes';
            $sopt[8105]['field']         = 'name';
            $sopt[8105]['datatype']      = 'dropdown';
            $sopt[8105]['name']          = PluginWebapplicationsWebapplicationServerType::getTypeName(1);
            $sopt[8105]['forcegroupby']  = true;
            $sopt[8105]['massiveaction'] = false;
            $sopt[8105]['linkfield']     = 'webapplicationservertypes_id';
            $sopt[8105]['joinparams']    = [
               'beforejoin' => [
                  'table'      => 'glpi_plugin_webapplications_appliances',
                  'joinparams' => [
                     'jointype'  => 'child',
                     'condition' => ''
                  ]
               ]
            ];

            $sopt[8106]['table']         = 'glpi_plugin_webapplications_webapplicationtechnics';
            $sopt[8106]['field']         = 'name';
            $sopt[8106]['datatype']      = 'dropdown';
            $sopt[8106]['name']          = PluginWebapplicationsWebapplicationTechnic::getTypeName(1);
            $sopt[8106]['forcegroupby']  = true;
            $sopt[8106]['massiveaction'] = false;
            $sopt[8106]['linkfield']     = 'webapplicationtechnics_id';
            $sopt[8106]['joinparams']    = [
               'beforejoin' => [
                  'table'      => 'glpi_plugin_webapplications_appliances',
                  'joinparams' => [
                     'jointype'  => 'child',
                     'condition' => ''
                  ]
               ]
            ];

            $sopt[8107]['table']         = 'glpi_plugin_webapplications_webapplicationexternalexpositions';
            $sopt[8107]['field']         = 'name';
            $sopt[8107]['datatype']      = 'dropdown';
            $sopt[8107]['name']          = PluginWebapplicationsWebapplicationExternalExposition::getTypeName(1);
            $sopt[8107]['forcegroupby']  = true;
            $sopt[8107]['massiveaction'] = false;
            $sopt[8107]['linkfield']     = 'webapplicationexternalexpositions_id';
            $sopt[8107]['joinparams']    = [
                'beforejoin' => [
                    'table'      => 'glpi_plugin_webapplications_appliances',
                    'joinparams' => [
                        'jointype'  => 'child',
                        'condition' => ''
                    ]
                ]
            ];

            $sopt[8108]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8108]['field']         = 'version';
            $sopt[8108]['name']          = __('Installed version', 'webapplications');
            $sopt[8108]['massiveaction'] = false;
            $sopt[8108]['datatype']      = 'text';
            $sopt[8108]['linkfield']     = 'appliances_id';
            $sopt[8108]['joinparams']    = array('jointype' => 'child');
            $sopt[8108]['forcegroupby']  = false;

            $sopt[8109]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8109]['field']         = 'webapplicationreferringdepartmentvalidation';
            $sopt[8109]['name']          = __('Validation of the request by the referring Department', 'webapplications');
            $sopt[8109]['massiveaction'] = false;
            $sopt[8109]['datatype']      = 'bool';
            $sopt[8109]['linkfield']     = 'appliances_id';
            $sopt[8109]['joinparams']    = array('jointype' => 'child');
            $sopt[8109]['forcegroupby']  = false;

            $sopt[8110]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8110]['field']         = 'webapplicationciovalidation';
            $sopt[8110]['name']          = __('Validation by CIO', 'webapplications');
            $sopt[8110]['massiveaction'] = false;
            $sopt[8110]['datatype']      = 'bool';
            $sopt[8110]['linkfield']     = 'appliances_id';
            $sopt[8110]['joinparams']    = array('jointype' => 'child');
            $sopt[8110]['forcegroupby']  = false;

            $sopt[8111]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8111]['field']         = 'webapplicationavailabilities';
            $sopt[8111]['name']          = __('Availability', 'webapplications');
            $sopt[8111]['massiveaction'] = false;
            $sopt[8111]['datatype']      = 'dropdown';
            $sopt[8111]['linkfield']     = 'appliances_id';
            $sopt[8111]['joinparams']    = array('jointype' => 'child');
            $sopt[8111]['forcegroupby']  = false;

            $sopt[8112]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8112]['field']         = 'webapplicationintegrities';
            $sopt[8112]['name']          = __('Integrity', 'webapplications');
            $sopt[8112]['massiveaction'] = false;
            $sopt[8112]['datatype']      = 'dropdown';
            $sopt[8112]['linkfield']     = 'appliances_id';
            $sopt[8112]['joinparams']    = array('jointype' => 'child');
            $sopt[8112]['forcegroupby']  = false;

            $sopt[8113]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8113]['field']         = 'webapplicationconfidentialities';
            $sopt[8113]['name']          = __('Confidentiality', 'webapplications');
            $sopt[8113]['massiveaction'] = false;
            $sopt[8113]['datatype']      = 'dropdown';
            $sopt[8113]['linkfield']     = 'appliances_id';
            $sopt[8113]['joinparams']    = array('jointype' => 'child');
            $sopt[8113]['forcegroupby']  = false;

            $sopt[8114]['table']         = 'glpi_plugin_webapplications_appliances';
            $sopt[8114]['field']         = 'webapplicationtraceabilities';
            $sopt[8114]['name']          = __('Traceability', 'webapplications');
            $sopt[8114]['massiveaction'] = false;
            $sopt[8114]['datatype']      = 'dropdown';
            $sopt[8114]['linkfield']     = 'appliances_id';
            $sopt[8114]['joinparams']    = array('jointype' => 'child');
            $sopt[8114]['forcegroupby']  = false;

            $sopt[8115]['table']         = 'glpi_suppliers';
            $sopt[8115]['field']         = 'name';
            $sopt[8115]['datatype']      = 'dropdown';
            $sopt[8115]['name']          = __('Referent editor', 'webapplications');
            $sopt[8115]['forcegroupby']  = true;
            $sopt[8115]['massiveaction'] = false;
            $sopt[8115]['linkfield']     = 'editor';
            $sopt[8115]['joinparams']    = [
                'beforejoin' => [
                    'table'      => 'glpi_plugin_webapplications_appliances',
                    'joinparams' => [
                        'jointype'  => 'child',
                        'condition' => ''
                    ]
                ]
            ];
        }
    }
    if ($itemtype == "DatabaseInstance") {
        if (Session::haveRight("plugin_webapplications_appliances", READ)) {
            $sopt[8116]['table']         = 'glpi_plugin_webapplications_webapplicationexternalexpositions';
            $sopt[8116]['field']         = 'name';
            $sopt[8116]['datatype']      = 'dropdown';
            $sopt[8116]['name']          = PluginWebapplicationsWebapplicationExternalExposition::getTypeName(1);
            $sopt[8116]['forcegroupby']  = true;
            $sopt[8116]['massiveaction'] = false;
            $sopt[8116]['linkfield']     = 'webapplicationexternalexpositions_id';
            $sopt[8116]['joinparams']    = [
                'beforejoin' => [
                    'table'      => 'glpi_plugin_webapplications_databaseinstances',
                    'joinparams' => [
                        'jointype'  => 'child',
                        'condition' => ''
                    ]
                ]
            ];

            $sopt[8117]['table']         = 'glpi_plugin_webapplications_databaseinstances';
            $sopt[8117]['field']         = 'webapplicationavailabilities';
            $sopt[8117]['name']          = __('Availability', 'webapplications');
            $sopt[8117]['massiveaction'] = false;
            $sopt[8117]['datatype']      = 'dropdown';
            $sopt[8117]['joinparams']    = array('jointype' => 'child');
            $sopt[8117]['forcegroupby']  = false;

            $sopt[8118]['table']         = 'glpi_plugin_webapplications_databaseinstances';
            $sopt[8118]['field']         = 'webapplicationintegrities';
            $sopt[8118]['name']          = __('Integrity', 'webapplications');
            $sopt[8118]['massiveaction'] = false;
            $sopt[8118]['datatype']      = 'dropdown';
            $sopt[8118]['joinparams']    = array('jointype' => 'child');
            $sopt[8118]['forcegroupby']  = false;

            $sopt[8119]['table']         = 'glpi_plugin_webapplications_databaseinstances';
            $sopt[8119]['field']         = 'webapplicationconfidentialities';
            $sopt[8119]['name']          = __('Confidentiality', 'webapplications');
            $sopt[8119]['massiveaction'] = false;
            $sopt[8119]['datatype']      = 'dropdown';
            $sopt[8119]['joinparams']    = array('jointype' => 'child');
            $sopt[8119]['forcegroupby']  = false;

            $sopt[8120]['table']         = 'glpi_plugin_webapplications_databaseinstances';
            $sopt[8120]['field']         = 'webapplicationtraceabilities';
            $sopt[8120]['name']          = __('Traceability', 'webapplications');
            $sopt[8120]['massiveaction'] = false;
            $sopt[8120]['datatype']      = 'dropdown';
            $sopt[8120]['joinparams']    = array('jointype' => 'child');
            $sopt[8120]['forcegroupby']  = false;

        }
    }
    return $sopt;
}
