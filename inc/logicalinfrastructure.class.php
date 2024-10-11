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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}


/**
 * Class PluginWebapplicationsLogicalInfrastructure
 */
class PluginWebapplicationsLogicalInfrastructure extends CommonDBTM
{
    public static $rightname = "plugin_webapplications";

    public static function getTypeName($nb = 0)
    {
        return _n('Logical infrastructure', 'Logical infrastructure', $nb,'webapplications');
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        global $DB;

        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $item = new Appliance();
        $item->getFromDB($ApplianceId);
        $class = get_class($item);

        // Only enabled for CommonDBTM
        if (!is_a($item, "CommonDBTM", true)) {
            throw new \InvalidArgumentException(
                "Argument \$item ($class) must be a CommonDBTM."
            );
        }

        $is_enabled_asset = Impact::isEnabled($class);
        $is_itil_object = is_a($item, "CommonITILObject", true);

        // Check if itemtype is valid
        if (!$is_enabled_asset && !$is_itil_object) {
            throw new \InvalidArgumentException(
                "Argument \$item ($class) is not a valid target for impact analysis."
            );
        }

        if (
            !$_SESSION['glpishow_count_on_tabs']
            || !isset($item->fields['id'])
            || $is_itil_object
        ) {
            // Count is disabled in config OR no item loaded OR ITIL object -> no count
            $total = 0;
        } else if ($is_enabled_asset) {
            // If on an asset, get the number of its direct dependencies
            $total = count($DB->request([
                'FROM'  => ImpactRelation::getTable(),
                'WHERE' => [
                    'OR' => [
                        [
                            // Source item is our item
                            'itemtype_source' => get_class($item),
                            'items_id_source' => $item->fields['id'],
                        ],
                        [
                            // Impacted item is our item AND source item is enabled
                            'itemtype_impacted' => get_class($item),
                            'items_id_impacted' => $item->fields['id'],
                            'itemtype_source'   => Impact::getEnabledItemtypes()
                        ]
                    ]
                ]
            ]));
        }
        return self::createTabEntry(self::getTypeName(), $total);
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }

    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        $options['candel'] = false;
        $options['colspan'] = 1;


        echo "<div align='center'>
        <table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='6' style='text-align:right'>" . __('Appliance') . "</td>";

        echo "<td >";
        $rand = Appliance::dropdown(['name' => 'applianceDropdown']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
        echo "<div id=lists-LogicalInfra></div>";

        $array['value'] = '__VALUE__';
        $array['type'] = self::getType();
        Ajax::updateItemOnSelectEvent(
            'dropdown_applianceDropdown' . $rand,
            'lists-LogicalInfra',
            $CFG_GLPI['root_doc'] . PLUGIN_WEBAPPLICATIONS_DIR_NOFULL . '/ajax/getLists.php',
            $array
        );
    }

    public static function showLists()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $item = new Appliance();
        $item->getFromDB($ApplianceId);

        PluginWebapplicationsDashboard::showHeaderDashboard($item);

        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>";
        echo _n('Logical infrastructure', 'Logical infrastructure', 1,'webapplications');
        echo "</h2>";

        $class = get_class($item);

        // Only enabled for CommonDBTM
        if (!is_a($item, "CommonDBTM")) {
            throw new \InvalidArgumentException(
                "Argument \$item ($class) must be a CommonDBTM)."
            );
        }

        $ID = $item->fields['id'];

        // Don't show the impact analysis on new object
        if ($item->isNewID($ID)) {
            return false;
        }

        // Check READ rights
        $itemtype = $item->getType();
        if (!$itemtype::canView()) {
            return false;
        }

        // For an ITIL object, load the first linked element by default
        if (is_a($item, "CommonITILObject")) {
            $linked_items = $item->getLinkedItems();

            // Search for a valid linked item of this ITILObject
            $items_data = [];
            foreach ($linked_items as $itemtype => $linked_item_ids) {
                $class = $itemtype;
                if (self::isEnabled($class)) {
                    $item = new $class();
                    foreach ($linked_item_ids as $linked_item_id) {
                        if (!$item->getFromDB($linked_item_id)) {
                            continue;
                        }
                        $items_data[] = [
                            'itemtype' => $itemtype,
                            'items_id' => $linked_item_id,
                            'name'     => $item->getNameID(),
                        ];
                    }
                }
            }

            // No valid linked item were found, tab shouldn't be visible
            if (empty($items_data)) {
                return false;
            }

            Impact::printAssetSelectionForm($items_data);
        }

        // Check is the impact analysis is enabled for $class
        if (!Impact::isEnabled($class)) {
            return false;
        }

        // Build graph and params
        $graph = Impact::buildGraph($item, true);
        $params = Impact::prepareParams($item);
        $readonly = !$item->can($item->fields['id'], UPDATE);

        // Print header
        Impact::printHeader(Impact::makeDataForCytoscape($graph), $params, $readonly);

        // Displays views
        Impact::displayGraphView($item);

        $graphForList = Impact::buildGraph($item);
        Impact::displayListView($item, $graphForList, true);

        // Select view
        echo Html::scriptBlock("
         // Select default view
         $(document).ready(function() {
            if (location.hash == '#list') {
               showListView();
            } else {
               showGraphView();
            }
         });
      ");
    }
}
