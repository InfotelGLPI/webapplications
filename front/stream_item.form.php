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

use GlpiPlugin\Webapplications\Stream_Item;

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}

$streamItem = new Stream_Item();

if (isset($_POST["add"])) {
    // Check the right on the record actually being written (the relation),
    // not on an unrelated Stream instance.
    $streamItem->check(-1, CREATE, $_POST);
    $newID = $streamItem->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($streamItem->getFormURL() . "?id=" . $newID);
    }
    Html::back();
} elseif (isset($_POST["update"])) {
    // $_POST['id'] is a Stream_Item id: reload and check that record so the
    // authorization is evaluated on the object that update() will modify.
    $streamItem->check($_POST['id'], UPDATE);
    $streamItem->update($_POST);
    Html::back();
}
