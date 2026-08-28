<?php

/**
 * -------------------------------------------------------------------------
 * webapplications plugin for GLPI
 * Copyright (C) 2015-2026 by the webapplications Development Team.
 *
 * https://github.com/InfotelGLPI/webapplications
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of webapplications.
 *
 * webapplications is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * webapplications is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with webapplications. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

use GlpiPlugin\Webapplications\Process_Entity;

// Page-level guard mirroring the sibling controllers (process.php, stream.php):
// establishes the "whole page is authorized" invariant. The per-branch
// check(-1, CREATE, $_POST) / check($_POST['id'], UPDATE) below remain the real
// authorization boundary on the mutated record.
Session::checkRight("plugin_webapplications_processes", READ);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}

$processEntity = new Process_Entity();

if (isset($_POST["add"])) {
    // Check the right on the record actually being written (the relation),
    // not on an unrelated Process instance.
    $processEntity->check(-1, CREATE, $_POST);
    $newID = $processEntity->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($processEntity->getFormURL() . "?id=" . $newID);
    }
    Html::back();
} elseif (isset($_POST["update"])) {
    // $_POST['id'] is a Process_Entity id: reload and check that record so the
    // authorization is evaluated on the object that update() will modify.
    $processEntity->check($_POST['id'], UPDATE);
    $processEntity->update($_POST);
    Html::back();
}
