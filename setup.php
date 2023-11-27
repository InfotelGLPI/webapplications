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

define('PLUGIN_WEBAPPLICATIONS_VERSION', '5.0.0');

if (!defined("PLUGIN_WEBAPPLICATIONS_DIR")) {
    define("PLUGIN_WEBAPPLICATIONS_DIR", Plugin::getPhpDir("webapplications"));
    define("PLUGIN_WEBAPPLICATIONS_DIR_NOFULL", Plugin::getPhpDir("webapplications", false));
    define("PLUGIN_WEBAPPLICATIONS_WEBDIR", Plugin::getWebDir("webapplications"));
}


// Init the hooks of the plugins -Needed
function plugin_init_webapplications()
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $PLUGIN_HOOKS['csrf_compliant']['webapplications']   = true;

    $PLUGIN_HOOKS['change_profile']['webapplications']   = ['PluginWebapplicationsProfile',
       'initProfile'];

    Plugin::registerClass('PluginWebapplicationsProfile', ['addtabon' => ['Profile']]);
    if (Session::getLoginUserID()) {
        if (Session::haveRight("plugin_webapplications", READ)) {
            $PLUGIN_HOOKS['menu_toadd']['webapplications']['appliancedashboard'] = array('PluginWebapplicationsDashboard','PluginWebapplicationsEntity', 'PluginWebapplicationsProcess', 'PluginWebapplicationsStream');
        }
    }


    //if glpi is loaded
       if (Session::getLoginUserID()) {

      if (Session::haveRight("plugin_webapplications", READ)
          || Session::haveRight("config", UPDATE)) {
         $PLUGIN_HOOKS['config_page']['webapplications']        = 'front/webapplication.php';
      }
       }

    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], "front/appliance.form.php") ==true) {
        $PLUGIN_HOOKS['post_item_form']['webapplications']= ['PluginWebapplicationsAppliance', 'addFields'];
    } elseif (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], "front/databaseinstance.form.php") ==true) {
        $PLUGIN_HOOKS['post_item_form']['webapplications'] = ['PluginWebapplicationsDatabaseInstance', 'addFields'];
    }

    $PLUGIN_HOOKS['item_purge']['webapplications']['Appliance'] = ['PluginWebapplicationsAppliance', 'cleanRelationToAppliance'];
    $PLUGIN_HOOKS['item_purge']['webapplications']['DatabaseInstance'] = ['PluginWebapplicationsDatabaseInstance', 'cleanRelationToDatabase'];

    // Other fields inherited from webapplications
    $PLUGIN_HOOKS['item_add']['webapplications']       = ['Appliance' => ['PluginWebapplicationsAppliance',
                                                                          'applianceAdd'],
                                                          'DatabaseInstance' => ['PluginWebapplicationsDatabaseInstance',
                                                                          'databaseAdd'],
                                                         'Item' => ['PluginWebapplicationsItem',
                                                                          'addApplianceItem']];

    $PLUGIN_HOOKS['pre_item_update']['webapplications'] = ['Appliance' => ['PluginWebapplicationsAppliance',
                                                                           'applianceUpdate'],
                                                           'DatabaseInstance' => ['PluginWebapplicationsDatabaseInstance',
                                                                           'databaseUpdate']];


    array_push($CFG_GLPI['appliance_types'], 'PluginWebapplicationsProcess', 'PluginWebapplicationsEntity', 'PluginWebapplicationsStream', 'Appliance');
    $CFG_GLPI['stream_types'] = ['DatabaseInstance', 'Computer', 'NetworkEquipment'];


    if (isset($_SERVER['REQUEST_URI'])
        && (strpos($_SERVER['REQUEST_URI'], "front/appliance.form.php") ==true
        || strpos($_SERVER['REQUEST_URI'], "front/databaseinstance.form.php") == true
        || strpos($_SERVER['REQUEST_URI'], "front/process.form.php") ==true
        || strpos($_SERVER['REQUEST_URI'], "front/dashboard.php") ==true)) {
        $PLUGIN_HOOKS["add_javascript"]['webapplications'][] = 'scripts/securityneedscolor.js.php';
    }
}


/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_webapplications()
{
    return ['name'           => _n('Web application', 'Web applications', 2, 'webapplications'),
                 'version'        => PLUGIN_WEBAPPLICATIONS_VERSION,
                 'license'        => 'GPLv2+',
                 'oldname'        => 'appweb',
                 'author'         => "<a href='http://blogglpi.infotel.com'>Infotel</a>",
                 'homepage'       => 'https://github.com/InfotelGLPI/webapplications',
                 'requirements'   => [
                   'glpi' => [
                      'min' => '10.0',
                      'dev' => false
                   ]
                ]
             ];
}


/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 *
 * @return bool
 */
function plugin_webapplications_check_prerequisites()
{
    if (version_compare(GLPI_VERSION, '10.0', 'lt')
       || version_compare(GLPI_VERSION, '11.0', 'ge')) {
        if (method_exists('Plugin', 'messageIncompatible')) {
            echo Plugin::messageIncompatible('core', '10.0');
        }
        return false;
    }

    return true;
}


/**
 * Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
 *
 * @return bool
 */
function plugin_webapplications_check_config()
{
    return true;
}
