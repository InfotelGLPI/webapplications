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

Session::checkLoginUser();

use Glpi\Event;
use GlpiPlugin\Webapplications\Process;

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}


$process = new Process();

if (isset($_POST["add"])) {
    $process->check(-1, CREATE, $_POST);
    $newID = $process->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($process->getFormURL() . "?id=" . $newID);
    }
    Html::back();
} elseif (isset($_POST["delete"])) {
    $process->check($_POST['id'], DELETE);
    $process->delete($_POST);
    $process->redirectToList();
} elseif (isset($_POST["restore"])) {
    $process->check($_POST['id'], PURGE);
    $process->restore($_POST);
    $process->redirectToList();
} elseif (isset($_POST["purge"])) {
    $process->check($_POST['id'], PURGE);
    $process->delete($_POST, 1);
    $process->redirectToList();
} elseif (isset($_POST["update"])) {
    $process->check($_POST['id'], UPDATE);
    $process->update($_POST);
    Html::back();
} elseif (isset($_GET['_in_modal'])) {
    $_SESSION['reload']=true;
    Html::popHeader(Process::getTypeName(2), $_SERVER['PHP_SELF']);
    $options = ['withtemplate' => $_GET["withtemplate"], 'formoptions'  => "data-track-changes=true"];
    if (isset($_GET['appliance_id'])) {
        $options['appliances_id'] = $_GET['appliance_id'];
    }
    $menus = ["appliancedashboard", "process"];
    Process::displayFullPageForItem($_GET['id'], $menus, $options);
    Html::popFooter();
} else {
    if (Session::getCurrentInterface() == "central") {
        Html::header(Process::getTypeName(2), $_SERVER['PHP_SELF'], "appliancedashboard", Process::class, "config");
        $process->display(['id' => $_GET["id"]]);
    }
    Html::footer();
}
