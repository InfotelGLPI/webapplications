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
 * Class PluginWebapplicationsPhysicalInfrastructure
 */
class PluginWebapplicationsPhysicalInfrastructure extends CommonDBTM
{
    public static $rightname = "plugin_webapplications";

    public static function getTypeName($nb = 0)
    {
        return __('Physical Infrastructure', 'webapplications');
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nbItem = count(self::getItems());
            return self::createTabEntry(self::getTypeName(), $nbItem);
        }
        return self::getTypeName();
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showLists();
        return true;
    }


    public static function getItems()
    {
        global $CFG_GLPI;
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $itemsAppDBTM = new Appliance_Item();

        $itemApp = $itemsAppDBTM->find([
            'appliances_id' => $ApplianceId,
            'itemtype' => $CFG_GLPI['inventory_types']
        ], 'itemtype');


        $listItem = [];
        foreach ($itemApp as $st) {
            $item = ['id' => $st['items_id'], 'itemtype' => $st['itemtype']];

            $itemDBTM = new $st['itemtype'];
            if ($itemDBTM->getFromDB($st['items_id'])) {
                array_push($listItem, $item);
            }
        }

        return $listItem;
    }

    public static function showLists()
    {
        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $appliance = new Appliance();
        $appliance->getFromDB($ApplianceId);

        echo "<div class='card-header main-header d-flex flex-wrap mx-n2 mt-n2 align-items-stretch'>";
        echo "<h3 class='card-title d-flex align-items-center ps-4'>";
        echo "<div class='ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1'>";
        echo "<i class='ti ti-versions fa-2x'></i>";
        echo "</div>";
        echo "<h3 style='margin: auto'>";
        $linkApp = Appliance::getFormURLWithID($ApplianceId);
        $name = $appliance->getName();
        echo "<a href='$linkApp'>$name</a>";
        echo "</h3>";
        echo "</h3>";
        echo "</div>";

        echo "<h2 class='card-header card-web-header d-flex justify-content-between align-items-center'>";
        echo __('Physical Infrastructure', 'webapplications');
        echo "</h2>";

        self::showListItem();

        echo "<script>accordion();</script>";
    }

    public static function showListItem()
    {
        global $CFG_GLPI;

        $ApplianceId = $_SESSION['plugin_webapplications_loaded_appliances_id'];

        $listItem = self::getItems();

        echo "<form name='form' method='post' action='" .
            Toolbox::getItemTypeFormURL('Appliance_Item') . "'>";
        echo "<div align='center'><table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='6'>" . __('Add an item') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='center'>";
        Dropdown::showSelectItemFromItemtypes(
            [
                'items_id_name' => 'items_id',
                'itemtypes' => $CFG_GLPI['inventory_types'],
                'checkright' => true,
            ]
        );
        echo "</td>";
        echo "<td class='tab_bg_2 center' colspan='6'>";
        echo Html::hidden('appliances_id', ['value' => $ApplianceId]);
        echo Html::submit(_sx('button', 'Add'), ['name' => 'add', 'class' => 'btn btn-primary']);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";

        Html::closeForm();


        echo "<h2 class='card-header d-flex justify-content-between align-items-center'>";
        echo _n("Item list", "Items list", count($listItem), 'webapplications');
        echo "</h2>";


        if (empty($listItem)) {

            echo "<table class='tab_cadre_fixe'>";
            echo "<tbody>";
            echo "<tr class='center'>";
            echo "<td colspan='4'>";
            echo __('No associated items', 'webapplications');
            echo "</td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";

        } else {

            echo "<div class='accordion' name=listItem>";

            foreach ($listItem as $item) {
                $itemtype = $item['itemtype'];

                $itemDBTM = new $itemtype();

                $itemDBTM->getFromDB($item['id']);


                $name = $itemDBTM->fields['name'];

                echo "<h3 class='accordionhead'>$name</h3>";

                echo "<div class='panel' id='tabsbody'>";


                echo "<table class='tab_cadre_fixe'>";


                echo "<tbody>";

                $linkItem = $itemDBTM::getFormURLWithID($item['id']);
                $linkItem .= "&forcetab=main";
                $linkItem .= "&type=" . $itemtype;

                echo "<tr>";
                echo "<th>";
                echo __("Name");
                echo "</th>";
                echo "<td>";
                echo "<a href='$linkItem'>$name</a>";
                echo "</td>";


                echo "<td style='width: 10%'>";
                echo Html::submit(_sx('button', 'Edit'), [
                    'name' => 'edit',
                    'class' => 'btn btn-secondary',
                    'icon' => 'fas fa-edit',
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#edit' . $itemtype . $item['id']
                ]);

                echo Ajax::createIframeModalWindow(
                    'edit' . $itemtype . $item['id'],
                    $linkItem,
                    [
                        'display' => false,
                        'reloadonclose' => true
                    ]
                );
                echo "</td>";

                echo "</tr>";

                echo "<tr>";
                echo "<th>";
                echo __("Item type");
                echo "</th>";
                echo "<td>";
                echo $itemDBTM->getTypeName();
                echo "</td>";
                echo "</tr>";

                $typeField = $itemtype . 'Type';
                $typeId = $itemDBTM->getField(strtolower($typeField) . 's_id');
                $ct = new $typeField;
                $ct->getFromDB($typeId);
                $type = $ct->getName();

                $modelField = $itemtype . 'Model';
                $modelId = $itemDBTM->getField(strtolower($modelField) . 's_id');
                $cm = new $modelField;
                $cm->getFromDB($modelId);
                $model = $cm->getName();

                $itemId = $item['id'];
                $itemOSDBTM = new Item_OperatingSystem();

                $OSName = NOT_AVAILABLE;
                $OSVersionName = null;

                if ($itemOSDBTM->getFromDBByCrit(['items_id' => $itemId, 'itemtype' => $itemtype])) {
                    $OSId = $itemOSDBTM->fields['operatingsystems_id'];
                    $OS = new OperatingSystem();
                    $OS->getFromDB($OSId);
                    $OSName = $OS->getName();

                    $OSVersionId = $itemOSDBTM->fields['operatingsystemversions_id'];
                    $OSVersion = new OperatingSystemVersion();
                    $OSVersion->getFromDB($OSVersionId);
                    $OSVersionName = $OSVersion->getName();
                    if ($OSVersionName == NOT_AVAILABLE || $OSName == NOT_AVAILABLE) {
                        $OSVersionName = null;
                    }
                }


                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo __('Technical characteristics', 'webapplications');
                echo "</th>";
                echo "<td class='inTable'>";
                echo "<table style='width:60%'>";
                echo "<tr><td><b>" . __('Type') . "</b></td>";
                echo "<td>" . $type . "</td></tr>";
                echo "<tr><td><b>" . __('Model') . "</b> </td>";
                echo "<td>" . $model . "</td></tr>";
                echo "<tr><td><b>" . __('Operating System') . "</b></td>";
                echo "<td>" . $OSName . " " . $OSVersionName . "</td></tr>";
                echo "</table>";
                echo "</td>";
                echo "</tr>";


                $comment = $itemDBTM->getField('comment');

                echo "<tr>";
                echo "<th style='padding-bottom: 20px'>";
                echo __('Comment');
                echo "</th>";
                echo "<td>";
                if (!empty($comment)) {
                    echo "<table style='border:1px solid; width:60%'>";
                    echo "<td>" . $comment . "</td>";
                    echo "</table>";
                }
                echo "</td>";
                echo "</tr>";


                $location = new Location();
                $locationId = $itemDBTM->getField('locations_id');
                $location->getFromDB($locationId);
                $locationName = $location->getName();
                $link = Location::getFormURLWithID($locationId);

                echo "<tr>";
                echo "<th>";
                echo __("Location");
                echo "</th>";
                echo "<td>";
                if ($locationId > 0) {
                    echo "<a href=$link>$locationName</a>";
                } else {
                    echo $locationName;
                }
                echo "</td>";
                echo "</tr>";

                echo "</tbody>";
                echo "</table></div>";
            }
        }
        echo "</div>";
    }
}
