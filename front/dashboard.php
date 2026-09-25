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

use GlpiPlugin\Webapplications\Dashboard;

Session::checkRight(Dashboard::$rightname, READ);

if (!isset($_GET["id"])) {
    $_GET["id"] = "1";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}

$iapp = new \Appliance_Item();

if (isset($_POST['add'])) {
    $iapp->check(-1, CREATE, $_POST);
    $iapp->add($_POST);

    // The impact-analysis relations built below mirror the appliance's impact graph.
    // Being allowed to link an item (checked above) must not, on its own, let a user
    // mutate impact analysis: gate that on UPDATE over the target appliance, and cast
    // every client-supplied identifier to its expected type before reuse.
    $appliances_id = (int) ($_POST['appliances_id'] ?? 0);
    $items_id      = (int) ($_POST['items_id'] ?? 0);
    $itemtype      = (string) ($_POST['itemtype'] ?? '');
    $can_impact    = $appliances_id > 0 && (new \Appliance())->can($appliances_id, UPDATE);

    if ($itemtype == 'Computer') {
        $item = new \Appliance();
        $item->getFromDB($appliances_id);

        $instances = getAllDataFromTable(
            \DatabaseInstance::getTable(),
            [
                'WHERE' => [
                    'items_id' => $items_id,
                    'itemtype' => $itemtype,
                ],
            ],
        );
        foreach ($instances as $row) {
            // getAllDataFromTable() applies neither rights nor entity scope: each instance
            // hosted on the computer may belong to an entity the user cannot reach, so the
            // link (and its impact relation) is checked instance by instance.
            $input = [
                'appliances_id' => $appliances_id,
                'items_id'      => (int) $row['id'],
                'itemtype'      => \DatabaseInstance::class,
            ];
            if (!$iapp->can(-1, CREATE, $input)
                || !(new \DatabaseInstance())->can((int) $row['id'], READ)) {
                continue;
            }
            if ($iapp->add($input) && $can_impact) {

                $i_items = getAllDataFromTable(
                    ImpactItem::getTable(),
                    [
                        'WHERE' => [
                            'itemtype' => 'Appliance',
                            'items_id' => $appliances_id,
                        ],
                    ],
                );

                foreach ($i_items as $i_item) {
                    $impact = new ImpactItem();
                    $impact->add([
                        'impactcontexts_id' => $i_item['impactcontexts_id'],
                        'itemtype' => 'DatabaseInstance',
                        'items_id' => $row['id'],
                    ]);
                    $impactr = new ImpactRelation();
                    $impactr->add([
                        'itemtype_source' => $itemtype,
                        'items_id_source' => $items_id,
                        'itemtype_impacted' => 'DatabaseInstance',
                        'items_id_impacted' => $row['id'],
                    ]);
                }
            }
        }
    }

    // UPDATE on the appliance is not enough to insert the linked item into its impact
    // graph: the user must also be able to read that item.
    $linked_item = getItemForItemtype($itemtype);
    if ($can_impact && $linked_item && $linked_item->can($items_id, READ)) {
        $i_items = getAllDataFromTable(
            ImpactItem::getTable(),
            [
                'WHERE' => [
                    'itemtype' => 'Appliance',
                    'items_id' => $appliances_id,
                ],
            ],
        );

        foreach ($i_items as $i_item) {
            $impact = new ImpactItem();
            $impact->add([
                'impactcontexts_id' => $i_item['impactcontexts_id'],
                'itemtype' => $itemtype,
                'items_id' => $items_id,
            ]);
            $impactr = new ImpactRelation();
            $impactr->add([
                'itemtype_source' => 'Appliance',
                'items_id_source' => $appliances_id,
                'itemtype_impacted' => $itemtype,
                'items_id_impacted' => $items_id,
            ]);
        }
    }

    Html::back();
} elseif (isset($_POST['reset'])) {
    // The dashboard "Delete" button removes an item from the CURRENTLY loaded
    // appliance only (the list it belongs to is scoped to that appliance). Bind
    // both the authorization check and the destructive delete to that appliance
    // so a purge can never cascade to links in other appliances or entities the
    // user has no access to. Raw $_POST identifiers are not trusted for scoping.
    $appliances_id = (int) ($_SESSION['plugin_webapplications_loaded_appliances_id'] ?? 0);
    $items_id      = (int) ($_POST['items_id'] ?? 0);
    $itemtype      = (string) ($_POST['itemtype'] ?? '');

    $itemsAppDBTM = new Appliance_Item();

    if ($appliances_id <= 0 || !$itemsAppDBTM->getFromDBByCrit([
        'appliances_id' => $appliances_id,
        'items_id'      => $items_id,
        'itemtype'      => $itemtype,
    ])) {
        // Nothing to delete for these unchecked identifiers.
        Html::back();
        return;
    }
    // Enforce authorization (global right + entity access) on the exact link that
    // will be deleted before running any destructive delete.
    $itemsAppDBTM->check($itemsAppDBTM->getID(), PURGE);

    $itemsAppDBTM->deleteByCriteria([
        'appliances_id' => $appliances_id,
        'items_id'      => $items_id,
        'itemtype'      => $itemtype,
    ]);

    if ($itemtype == 'Computer') {
        $instances = getAllDataFromTable(
            DatabaseInstance::getTable(),
            [
                'WHERE' => [
                    'items_id' => $items_id,
                    'itemtype' => $itemtype,
                ],
            ],
        );
        // deleteByCriteria() replays no right: purge each instance link on its own, and
        // only when the user may purge it (the instance may sit in an unreachable entity).
        $instances_ids = array_map('intval', array_column($instances, 'id'));
        $links         = $instances_ids === [] ? [] : $itemsAppDBTM->find([
            'appliances_id' => $appliances_id,
            'itemtype'      => DatabaseInstance::class,
            'items_id'      => $instances_ids,
        ]);
        foreach ($links as $link) {
            if ($itemsAppDBTM->can($link['id'], PURGE)) {
                $itemsAppDBTM->delete(['id' => $link['id']], true);
            }
        }
    }

    Html::back();
}

$dashboard = new Dashboard();

Html::header(
    Dashboard::getTypeName(2),
    $_SERVER['PHP_SELF'],
    "appliancedashboard",
    Dashboard::class,
);

$id = 0;
if (isset($_SESSION['plugin_webapplications_loaded_appliances_id'])) {
    $id = $_SESSION['plugin_webapplications_loaded_appliances_id'];
}

Dashboard::selectAppliance($id);

Html::footer();
