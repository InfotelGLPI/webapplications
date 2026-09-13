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

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Webapplications\Stream;
use GlpiPlugin\Webapplications\Stream_Item;

/**
 * Re-validate both ends of the relation.
 *
 * glpi_plugin_webapplications_streams_items carries no entities_id, so
 * check(-1, CREATE, $_POST) / check($id, UPDATE) degrade to the global right bit
 * and never look at the stream the row hangs on nor at the item it points to.
 * Editing a stream's item list is governed by UPDATE on that stream (that is the
 * bit showForStream() uses to decide whether the form is editable at all), and
 * the linked item must be one the caller may read.
 */
function plugin_webapplications_check_stream_item_endpoints(int $streams_id, array $input): void
{
    $stream = new Stream();
    if ($streams_id <= 0 || !$stream->can($streams_id, UPDATE)) {
        throw new AccessDeniedHttpException();
    }

    if (!isset($input['items_id']) && !isset($input['itemtype'])) {
        return;
    }

    $itemtype = (string) ($input['itemtype'] ?? '');
    if (!Stream_Item::isValidStreamItemtype($itemtype)
        || !is_a($itemtype, CommonDBTM::class, true)) {
        throw new AccessDeniedHttpException();
    }

    $target = new $itemtype();
    if (!$target->can((int) ($input['items_id'] ?? 0), READ)) {
        throw new AccessDeniedHttpException();
    }
}

// Page-level guard mirroring the sibling controllers (process.php, stream.php):
// establishes the "whole page is authorized" invariant. The per-branch
// check(-1, CREATE, $_POST) / check($_POST['id'], UPDATE) below remain the real
// authorization boundary on the mutated record.
Session::checkRight("plugin_webapplications_streams", READ);

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
    plugin_webapplications_check_stream_item_endpoints(
        (int) ($_POST['plugin_webapplications_streams_id'] ?? 0),
        $_POST,
    );
    $newID = $streamItem->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($streamItem->getFormURL() . "?id=" . $newID);
    }
    Html::back();
} elseif (isset($_POST["update"])) {
    // $_POST['id'] is a Stream_Item id: reload and check that record so the
    // authorization is evaluated on the object that update() will modify.
    $streamItem->check($_POST['id'], UPDATE);
    // The stream the row currently hangs on must be updatable...
    plugin_webapplications_check_stream_item_endpoints(
        (int) $streamItem->fields['plugin_webapplications_streams_id'],
        [],
    );
    // ...and so must the one it would be moved to, along with the posted item.
    plugin_webapplications_check_stream_item_endpoints(
        (int) ($_POST['plugin_webapplications_streams_id']
            ?? $streamItem->fields['plugin_webapplications_streams_id']),
        $_POST,
    );
    $streamItem->update($_POST);
    Html::back();
}
