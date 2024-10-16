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

include('../../../inc/includes.php');

Session::checkLoginUser();

if (!isset($_GET["id"])) {
    $_GET["id"] = "1";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}

$dashboard = new PluginWebapplicationsDashboard;

Html::header(
    PluginWebapplicationsDashboard::getTypeName(2),
    $_SERVER['PHP_SELF'],
    "appliancedashboard",
    "pluginwebapplicationsdashboard"
);

$id = 0;
if (isset($_SESSION['plugin_webapplications_loaded_appliances_id'])) {
    $id = $_SESSION['plugin_webapplications_loaded_appliances_id'];
}

PluginWebapplicationsDashboard::selectAppliance($id);

Html::footer();
