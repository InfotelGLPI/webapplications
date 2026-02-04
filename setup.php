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

define('PLUGIN_WEBAPPLICATIONS_VERSION', '5.1.5');

global $CFG_GLPI;

use Glpi\Plugin\Hooks;
use GlpiPlugin\Webapplications\Appliance;
use GlpiPlugin\Webapplications\Dashboard;
use GlpiPlugin\Webapplications\DatabaseInstance;
use GlpiPlugin\Webapplications\Entity;
use GlpiPlugin\Webapplications\Process;
use GlpiPlugin\Webapplications\Profile;
use GlpiPlugin\Webapplications\Stream;

if (!defined("PLUGIN_WEBAPPLICATIONS_DIR")) {
    define("PLUGIN_WEBAPPLICATIONS_DIR", Plugin::getPhpDir("webapplications"));
    $root = $CFG_GLPI['root_doc'] . '/plugins/webapplications';
    define("PLUGIN_WEBAPPLICATIONS_WEBDIR", $root);
}


// Init the hooks of the plugins -Needed
function plugin_init_webapplications()
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $PLUGIN_HOOKS['csrf_compliant']['webapplications'] = true;

    $PLUGIN_HOOKS['change_profile']['webapplications'] = [
        Profile::class,
        'initProfile',
    ];

    Plugin::registerClass(Profile::class, ['addtabon' => ['Profile']]);
    if (Session::getLoginUserID()) {
        if (Session::haveRight("plugin_webapplications_configs", UPDATE)) {
            $PLUGIN_HOOKS['config_page']['webapplications'] = 'front/config.form.php';
        }
        if (Session::haveRight("plugin_webapplications_appliances", READ)) {
            $PLUGIN_HOOKS['menu_toadd']['webapplications']['appliancedashboard'] = [
                Dashboard::class,
                Entity::class,
                Process::class,
                Stream::class,
            ];
        }
    }

    $PLUGIN_HOOKS['post_item_form']['webapplications'] = [Appliance::class, 'addFields'];

    $PLUGIN_HOOKS['item_purge']['webapplications']['Appliance'] = [
        Appliance::class,
        'cleanRelationToAppliance',
    ];
    $PLUGIN_HOOKS['item_purge']['webapplications']['DatabaseInstance'] = [
        DatabaseInstance::class,
        'cleanRelationToDatabase',
    ];

    // Other fields inherited from webapplications
    $PLUGIN_HOOKS['item_add']['webapplications'] = [
        'Appliance' => [
            Appliance::class,
            'applianceAdd',
        ],
        'DatabaseInstance' => [
            DatabaseInstance::class,
            'databaseAdd',
        ],
        'Appliance_Item' => [
            DatabaseInstance::class,
            'databaseLink',
        ],
    ];

    $PLUGIN_HOOKS['pre_item_update']['webapplications'] = [
        'Appliance' => [
            Appliance::class,
            'applianceUpdate',
        ],
        'DatabaseInstance' => [
            DatabaseInstance::class,
            'databaseUpdate',
        ],
    ];


    array_push(
        $CFG_GLPI['appliance_types'],
        Process::class,
        Entity::class,
        Stream::class,
        'Appliance'
    );
    $CFG_GLPI['stream_types'] = ['DatabaseInstance', 'Computer', 'NetworkEquipment', 'Appliance'];


    if (isset($_SERVER['REQUEST_URI'])
        && (strpos($_SERVER['REQUEST_URI'], "front/appliance.form.php") == true
            || strpos($_SERVER['REQUEST_URI'], "front/databaseinstance.form.php") == true
            || strpos($_SERVER['REQUEST_URI'], "front/process.form.php") == true
            || strpos($_SERVER['REQUEST_URI'], "front/dashboard.php") == true)) {
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['webapplications'][] = 'scripts/securityneedscolor.js';
    }

    $PLUGIN_HOOKS[Hooks::ADD_CSS]['webapplications'] = ['css/webapplications.css'];
}


/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_webapplications()
{
    return [
        'name' => __('Appliance dashboard', 'webapplications'),
        'version' => PLUGIN_WEBAPPLICATIONS_VERSION,
        'license' => 'GPLv2+',
        'oldname' => 'appweb',
        'author' => "<a href='https//blogglpi.infotel.com'>Infotel</a>, Xavier CAILLAUD",
        'homepage' => 'https://github.com/InfotelGLPI/webapplications',
        'requirements' => [
            'glpi' => [
                'min' => '11.0',
                'max' => '12.0',
                'dev' => false,
            ],
        ],
    ];
}

/**
 * @return bool
 */
function plugin_webapplications_check_prerequisites()
{
    if (!is_readable(__DIR__ . '/vendor/autoload.php')
        || !is_file(__DIR__ . '/vendor/autoload.php')) {
        echo "Run composer install --no-dev in the plugin directory<br>";
        return false;
    }

    return true;
}

