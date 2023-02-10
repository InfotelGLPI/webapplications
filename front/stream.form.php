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

use Glpi\Event;

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}
global $CFG_GLPI;


$stream = new PluginWebapplicationsStream();

if (isset($_POST["add"])) {

    $stream->check(-1, CREATE, $_POST);
    $newID = $stream->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($stream->getFormURL() . "?id=" . $newID);
    }
    Html::back();

} else if (isset($_POST["delete"])) {

    $stream->check($_POST['id'], DELETE);
    $stream->delete($_POST);
    $stream->redirectToList();

} else if (isset($_POST["restore"])) {

    $stream->check($_POST['id'], PURGE);
    $stream->restore($_POST);
    $stream->redirectToList();

} else if (isset($_POST["purge"])) {
    $stream->check($_POST['id'], PURGE);
    $stream->delete($_POST, 1);
    $stream->redirectToList();

} else if (isset($_POST["update"])) {

    $stream->check($_POST['id'], UPDATE);
    $stream->pre_update();
    $stream->update($_POST);
    Html::back();

}
else if (isset($_GET['_in_modal'])) {
    Html::popHeader(PluginWebapplicationsStream::getTypeName(2), $_SERVER['PHP_SELF']);
    $_SESSION['reload']=true;
    $options = ['withtemplate' => $_GET["withtemplate"], 'formoptions'  => "data-track-changes=true", 'stream_types' => $CFG_GLPI['stream_types']];
    if(isset($_GET['appliance_id'])) {
        $options['appliances_id'] = $_GET['appliance_id'];
    }
    $menus = ["appliancedashboard", "stream"];
    PluginWebapplicationsStream::displayFullPageForItem($_GET['id'], $menus, $options );

    Html::popFooter();

}
else {

    if (Session::getCurrentInterface() == "central") {

        Html::header(PluginWebapplicationsStream::getTypeName(2), $_SERVER['PHP_SELF'], "appliancedashboard", "pluginwebapplicationsstream", "config");
        $stream->display(['id' => $_GET["id"], 'stream_types' => $CFG_GLPI['stream_types']]);
    }
    Html::footer();
}

