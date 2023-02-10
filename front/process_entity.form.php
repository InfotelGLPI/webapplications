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

$process = new PluginWebapplicationsProcess();
$processEntity = new PluginWebapplicationsProcess_Entity();

if (isset($_POST["add"])) {
    $process->check(-1, CREATE, $_POST);
    $newID = $processEntity->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($processEntity->getFormURL() . "?id=" . $newID);
    }
    Html::back();
} elseif (isset($_POST["update"])) {
    $process->check($_POST['id'], UPDATE);
    $processEntity->update($_POST);
    Html::back();
}
