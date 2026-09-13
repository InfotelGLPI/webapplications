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
use GlpiPlugin\Webapplications\Entity;
use GlpiPlugin\Webapplications\Process;
use GlpiPlugin\Webapplications\Process_Entity;

/**
 * Re-validate both ends of the relation.
 *
 * glpi_plugin_webapplications_processes_entities carries no entities_id, so
 * check(-1, CREATE, $_POST) / check($id, UPDATE) degrade to the global right bit
 * and never look at the process nor at the entity being linked. The relation is
 * edited from either side (showForEntity() / showForProcess() gate their form on
 * UPDATE over the side they render), so require UPDATE on one end and READ on
 * the other - and never less than READ on both.
 */
function plugin_webapplications_check_process_entity_endpoints(int $entities_id, int $processes_id): void
{
    $entity = new Entity();
    $process = new Process();

    if ($entities_id <= 0 || $processes_id <= 0) {
        throw new AccessDeniedHttpException();
    }

    $can_entity = $entity->can($entities_id, UPDATE);
    $can_process = $process->can($processes_id, UPDATE);

    if ($can_entity && $can_process) {
        return;
    }
    if ($can_entity && $process->can($processes_id, READ)) {
        return;
    }
    if ($can_process && $entity->can($entities_id, READ)) {
        return;
    }

    throw new AccessDeniedHttpException();
}

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
    plugin_webapplications_check_process_entity_endpoints(
        (int) ($_POST['plugin_webapplications_entities_id'] ?? 0),
        (int) ($_POST['plugin_webapplications_processes_id'] ?? 0),
    );
    $newID = $processEntity->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($processEntity->getFormURL() . "?id=" . $newID);
    }
    Html::back();
} elseif (isset($_POST["update"])) {
    // $_POST['id'] is a Process_Entity id: reload and check that record so the
    // authorization is evaluated on the object that update() will modify.
    $processEntity->check($_POST['id'], UPDATE);
    // The pair the row currently holds, then the pair it would be moved to.
    plugin_webapplications_check_process_entity_endpoints(
        (int) $processEntity->fields['plugin_webapplications_entities_id'],
        (int) $processEntity->fields['plugin_webapplications_processes_id'],
    );
    plugin_webapplications_check_process_entity_endpoints(
        (int) ($_POST['plugin_webapplications_entities_id']
            ?? $processEntity->fields['plugin_webapplications_entities_id']),
        (int) ($_POST['plugin_webapplications_processes_id']
            ?? $processEntity->fields['plugin_webapplications_processes_id']),
    );
    $processEntity->update($_POST);
    Html::back();
}
